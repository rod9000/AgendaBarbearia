<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function booking()
    {
        $services = Service::where('active', true)->orderBy('name')->get();
        $users = User::where('active', true)->where('role', 'attendant')->orderBy('name')->get();
        return view('public.booking', compact('services', 'users'));
    }

    public function searchCustomer(Request $request)
    {
        $query = $request->get('q');
        if (strlen($query) < 3) {
            return response()->json(['customers' => []]);
        }

        $customers = Customer::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('cpf', 'like', "%{$query}%")
              ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') LIKE ?", ["%{$query}%"]);
        })->orderBy('name')->get(['id', 'name', 'cpf', 'phone', 'email']);

        return response()->json(['customers' => $customers]);
    }

    public function getSlots(Request $request)
    {
        $date = Carbon::parse($request->get('date'));
        $serviceId = $request->get('service_id');
        $userId = $request->get('user_id');

        $service = Service::findOrFail($serviceId);
        $dayOfWeek = $date->dayOfWeek;

        $wh = WorkingHour::where('user_id', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->where('active', true)
            ->first();

        if (!$wh) {
            return response()->json(['slots' => []]);
        }

        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $wh->start_time);
        $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $wh->end_time);
        $duration = $service->duration_min;

        $existingAppointments = Appointment::where('user_id', $userId)
            ->whereDate('start', $date)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->get();

        $blockedSlots = BlockedSlot::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->whereDate('start', '<=', $date)
            ->whereDate('end', '>=', $date)
            ->get();

        $slots = [];
        $current = $startTime->copy();

        while ($current->copy()->addMinutes($duration)->lte($endTime)) {
            $slotEnd = $current->copy()->addMinutes($duration);
            $available = true;

            foreach ($existingAppointments as $app) {
                if ($current->lt($app->end) && $slotEnd->gt($app->start)) {
                    $available = false;
                    break;
                }
            }

            if ($available) {
                foreach ($blockedSlots as $blocked) {
                    $blockedStart = Carbon::parse($blocked->start);
                    $blockedEnd = Carbon::parse($blocked->end);
                    if ($current->lt($blockedEnd) && $slotEnd->gt($blockedStart)) {
                        $available = false;
                        break;
                    }
                }
            }

            if ($available && $current->gt(Carbon::now())) {
                $slots[] = [
                    'time' => $current->format('H:i'),
                    'label' => $current->format('H:i'),
                ];
            }

            $current->addMinutes($duration);
        }

        return response()->json(['slots' => $slots]);
    }

    public function store(Request $request)
    {
        if ($request->get('action') === 'register_customer') {
            $data = $request->validate([
                'name'  => 'required|string|max:100',
                'cpf'   => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
            ]);

            try {
                $customer = Customer::create([
                    'name'       => $data['name'],
                    'cpf'        => $data['cpf'] ?? null,
                    'phone'      => $data['phone'] ?? '',
                    'email'      => $data['email'] ?? null,
                    'birth_date' => now()->subYears(18),
                    'created_by' => User::where('role', 'admin')->value('id') ?? 1,
                ]);

                return response()->json([
                    'success'  => true,
                    'customer' => [
                        'id'    => $customer->id,
                        'name'  => $customer->name,
                        'cpf'   => $customer->cpf,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao cadastrar: ' . $e->getMessage(),
                ], 422);
            }
        }

        $data = $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'user_id'       => 'required|exists:users,id',
            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'date'          => 'required|date',
            'time'          => 'required|date_format:H:i',
        ]);

        $services = Service::whereIn('id', $data['service_ids'])->get();
        $maxDuration = $services->max('duration_min');
        $start = Carbon::parse($data['date'] . ' ' . $data['time']);
        $end = $start->copy()->addMinutes($maxDuration);

        $appointment = Appointment::create([
            'customer_id' => $data['customer_id'],
            'user_id'     => $data['user_id'],
            'service_id'  => $services->first()->id,
            'start'       => $start,
            'end'         => $end,
            'status'      => 'scheduled',
            'notes'       => 'Agendamento via site público',
        ]);

        $pivotData = [];
        foreach ($services as $service) {
            $pivotData[$service->id] = [
                'price'        => $service->price,
                'duration_min' => $service->duration_min,
            ];
        }
        $appointment->services()->sync($pivotData);

        return redirect()->route('public.booking')
            ->with('success', 'Agendamento realizado com sucesso! Entraremos em contato para confirmar.');
    }

    public function reagendar($token)
    {
        $appointment = Appointment::with(['customer', 'services', 'user'])
            ->where('confirmation_token', $token)
            ->first();

        if (!$appointment) {
            return view('public.confirmacao', ['success' => false, 'message' => 'Link inválido ou expirado.']);
        }

        if ($appointment->status === 'cancelled') {
            return view('public.confirmacao', ['success' => false, 'message' => 'Este agendamento foi cancelado.']);
        }

        $services = Service::where('active', true)->orderBy('name')->get();
        $users = User::where('active', true)->where('role', 'attendant')->orderBy('name')->get();

        return view('public.reagendar', compact('appointment', 'services', 'users'));
    }

    public function reagendarStore(Request $request, $token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->first();

        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Link inválido ou expirado.'], 404);
        }

        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'date'        => 'required|date',
            'time'        => 'required|date_format:H:i',
        ]);

        $services = Service::whereIn('id', $data['service_ids'])->get();
        $maxDuration = $services->max('duration_min');
        $start = Carbon::parse($data['date'] . ' ' . $data['time']);
        $end = $start->copy()->addMinutes($maxDuration);

        $appointment->update([
            'user_id' => $data['user_id'],
            'start'   => $start,
            'end'     => $end,
            'status'  => 'scheduled',
            'notes'   => $appointment->notes . ' | Reagendado pelo cliente em ' . now()->format('d/m/Y H:i'),
        ]);

        $pivotData = [];
        foreach ($services as $service) {
            $pivotData[$service->id] = [
                'price'        => $service->price,
                'duration_min' => $service->duration_min,
            ];
        }
        $appointment->services()->sync($pivotData);

        NotificationLog::create([
            'appointment_id' => $appointment->id,
            'customer_id'    => $appointment->customer_id,
            'type'           => 'reschedule',
            'recipient'      => $appointment->customer->phone ?? null,
            'message'        => 'Cliente reagendou via link',
            'status'         => 'clicked',
            'sent_at'        => now(),
        ]);

        $serviceList = $services->map(fn($s) => $s->name . ' (' . $s->duration_min . 'min)')->implode("\n");
        $totalPrice = $services->sum('price');
        $confirmLink = url('/confirmar/' . $appointment->confirmation_token);
        $msg = "Olá {$appointment->customer->name}, seu agendamento foi REAGENDADO com sucesso!\n"
             . "Novos detalhes:\n{$serviceList}\n"
             . "Data: {$start->format('d/m/Y H:i')}\n"
             . "Profissional: {$appointment->user->name}\n"
             . "Valor: R$ " . number_format($totalPrice, 2, ',', '.')
             . "\n\nConfirme sua presença:\n{$confirmLink}";

        try {
            $wa = new WhatsAppService();
            $wa->send($appointment->customer->phone, $msg);
        } catch (\Exception $e) {
            \Log::error('WhatsApp reschedule send failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    public function confirmar($token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->first();

        if (!$appointment) {
            return view('public.confirmacao', ['success' => false, 'message' => 'Link inválido ou expirado.']);
        }

        if ($appointment->confirmed_at) {
            return view('public.confirmacao', [
                'success' => true,
                'message' => 'Sua presença já foi confirmada anteriormente!',
                'appointment' => $appointment,
            ]);
        }

        if (in_array($appointment->status, ['cancelled', 'no_show'])) {
            return view('public.confirmacao', ['success' => false, 'message' => 'Este agendamento foi cancelado.']);
        }

        $appointment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        NotificationLog::create([
            'appointment_id' => $appointment->id,
            'customer_id'    => $appointment->customer_id,
            'type'           => 'confirmation_link',
            'recipient'      => $appointment->customer->phone ?? null,
            'message'        => 'Cliente confirmou presença via link',
            'status'         => 'clicked',
            'sent_at'        => now(),
        ]);

        return view('public.confirmacao', [
            'success' => true,
            'message' => 'Presença confirmada com sucesso!',
            'appointment' => $appointment,
        ]);
    }
}
