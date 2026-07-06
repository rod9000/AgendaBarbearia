<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\Company;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function booking()
    {
        $combos = $this->bookingService->getServiceUserCombos();
        $companyWhatsapp = Company::where('active', true)->value('whatsapp');

        return view('public.booking', compact('combos', 'companyWhatsapp'));
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
        $dayOfWeek = $date->dayOfWeek;

        // Accept arrays or single values (backward compat)
        $userIds = $request->get('user_ids', $request->has('user_id') ? [$request->get('user_id')] : []);
        $serviceIds = $request->get('service_ids', $request->has('service_id') ? [$request->get('service_id')] : []);

        if (empty($userIds) || empty($serviceIds)) {
            return response()->json(['slots' => []]);
        }

        // Max duration across all selected services
        $services = Service::whereIn('id', $serviceIds)->get();
        $maxDuration = $services->max('duration_min') ?? 30;

        // Collect per-user availability
        $userSlotSets = [];

        foreach ($userIds as $uid) {
            $whs = WorkingHour::where('user_id', $uid)
                ->where('day_of_week', $dayOfWeek)
                ->where('active', true)
                ->get();

            if ($whs->isEmpty()) {
                return response()->json(['slots' => []]);
            }

            $existingAppointments = Appointment::where('user_id', $uid)
                ->whereDate('start', $date)
                ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
                ->get();

            $blockedSlots = BlockedSlot::where(function ($q) use ($uid) {
                    $q->where('user_id', $uid)->orWhereNull('user_id');
                })
                ->whereDate('start', '<=', $date)
                ->whereDate('end', '>=', $date)
                ->get();

            $userSlots = [];

            foreach ($whs as $wh) {
                $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $wh->start_time);
                $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $wh->end_time);
                $current = $startTime->copy();

                while ($current->copy()->addMinutes($maxDuration)->lte($endTime)) {
                    $slotEnd = $current->copy()->addMinutes($maxDuration);
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
                        $userSlots[] = $current->format('H:i');
                    }

                    $current->addMinutes($maxDuration);
                }
            }

            $userSlotSets[] = $userSlots;
        }

        // Intersection: slots available for ALL selected users
        $common = $userSlotSets[0] ?? [];
        for ($i = 1; $i < count($userSlotSets); $i++) {
            $common = array_values(array_intersect($common, $userSlotSets[$i]));
        }

        $slots = array_map(fn($t) => ['time' => $t, 'label' => $t], $common);

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
            'customer_id'        => 'required|exists:customers,id',
            'combos'             => 'required|array|min:1',
            'combos.*.service_id' => 'required|exists:services,id',
            'combos.*.user_id'    => 'required|exists:users,id',
            'date'               => 'required|date',
            'time'               => 'required|date_format:H:i',
        ]);

        try {
            $grouped = collect($data['combos'])->groupBy('user_id');

            foreach ($grouped as $userId => $userCombos) {
                $serviceIds = $userCombos->pluck('service_id')->toArray();
                $services = Service::whereIn('id', $serviceIds)->get();
                $maxDuration = $services->max('duration_min');
                $start = Carbon::parse($data['date'] . ' ' . $data['time']);
                $end = $start->copy()->addMinutes($maxDuration);

                $appointment = Appointment::create([
                    'customer_id' => $data['customer_id'],
                    'user_id'     => $userId,
                    'service_id'  => $services->count() === 1 ? $services->first()->id : null,
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
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao criar agendamento: ' . $e->getMessage()]);
        }

        $items = [];
        foreach ($grouped as $userId => $userCombos) {
            $user = User::find($userId);
            $services = Service::whereIn('id', $userCombos->pluck('service_id'))->get();
            foreach ($services as $s) {
                $items[] = ['service' => $s->name, 'professional' => $user->name];
            }
        }

        return redirect()->route('public.sucesso')
            ->with('agendamento', [
                'date'  => $data['date'],
                'time'  => $data['time'],
                'items' => $items,
            ]);
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

        $combos = [];
        foreach ($services as $service) {
            foreach ($users as $user) {
                $combos[] = (object) [
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                    'duration_min' => $service->duration_min,
                    'price'        => $service->price,
                    'color_hex'    => $service->color_hex,
                    'user_id'      => $user->id,
                    'user_name'    => $user->name,
                ];
            }
        }

        return view('public.reagendar', compact('appointment', 'combos', 'services', 'users'));
    }

    public function reagendarStore(Request $request, $token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->first();

        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Link inválido ou expirado.'], 404);
        }

        $data = $request->validate([
            'combos'              => 'required|array|min:1',
            'combos.*.service_id' => 'required|exists:services,id',
            'combos.*.user_id'    => 'required|exists:users,id',
            'date'                => 'required|date',
            'time'                => 'required|date_format:H:i',
        ]);

        try {
            $grouped = collect($data['combos'])->groupBy('user_id');
            $start = Carbon::parse($data['date'] . ' ' . $data['time']);

            $createdAppointments = [];

            $isFirstUser = true;

            foreach ($grouped as $userId => $userCombos) {
                $serviceIds = $userCombos->pluck('service_id')->toArray();
                $services = Service::whereIn('id', $serviceIds)->get();
                $maxDuration = $services->max('duration_min');
                $end = $start->copy()->addMinutes($maxDuration);

                if ($isFirstUser) {
                    $appointment->update([
                        'user_id' => $userId,
                        'start'   => $start,
                        'end'     => $end,
                        'status'  => 'scheduled',
                        'notes'   => $appointment->notes . ' | Reagendado pelo cliente em ' . now()->format('d/m/Y H:i'),
                    ]);
                    $createdAppointments[] = $appointment;
                } else {
                    $newApp = Appointment::create([
                        'customer_id' => $appointment->customer_id,
                        'user_id'     => $userId,
                    'service_id'  => $services->first()->id,
                        'start'       => $start,
                        'end'         => $end,
                        'status'      => 'scheduled',
                        'notes'       => 'Reagendado via link (adicional)',
                        'parent_id'   => $appointment->id,
                    ]);
                    $createdAppointments[] = $newApp;
                }

                $pivotData = [];
                foreach ($services as $service) {
                    $pivotData[$service->id] = [
                        'price'        => $service->price,
                        'duration_min' => $service->duration_min,
                    ];
                }

                if ($isFirstUser) {
                    $appointment->services()->sync($pivotData);
                } else {
                    $newApp->services()->sync($pivotData);
                }

                $isFirstUser = false;
            }

            NotificationLog::create([
                'appointment_id' => $appointment->id,
                'customer_id'    => $appointment->customer_id,
                'type'           => 'reschedule',
                'recipient'      => $appointment->customer->phone ?? null,
                'message'        => 'Cliente reagendou via link',
                'status'         => 'clicked',
                'sent_at'        => now(),
            ]);

            $allServices = Service::whereIn('id', collect($data['combos'])->pluck('service_id')->toArray())->get();
            $serviceList = $allServices->map(fn($s) => $s->name . ' (' . $s->duration_min . 'min)')->implode("\n");
            $totalPrice = $allServices->sum('price');
            $userNames = $grouped->keys()->map(fn($uid) => User::find($uid)?->name)->filter()->implode(', ');
            $confirmLink = url('/confirmar/' . $appointment->confirmation_token);
            $msg = "Olá {$appointment->customer->name}, seu agendamento foi REAGENDADO com sucesso!\n"
                 . "Novos detalhes:\n{$serviceList}\n"
                 . "Data: {$start->format('d/m/Y H:i')}\n"
                 . "Profissional(is): {$userNames}\n"
                 . "Valor: R$ " . number_format($totalPrice, 2, ',', '.')
                 . "\n\nConfirme sua presença:\n{$confirmLink}";

            try {
                $wa = new WhatsAppService();
                $wa->send($appointment->customer->phone, $msg);
            } catch (\Exception $e) {
                \Log::error('WhatsApp reschedule send failed: ' . $e->getMessage());
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Reagendamento failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao reagendar. Tente novamente.'], 500);
        }
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

    public function cancelar($token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->first();

        if (!$appointment) {
            return view('public.cancelamento', ['appointment' => null, 'cancelled' => false, 'error' => 'Link inválido ou expirado.']);
        }

        if (in_array($appointment->status, ['cancelled', 'completed'])) {
            return view('public.cancelamento', ['appointment' => $appointment, 'cancelled' => true]);
        }

        return view('public.cancelamento', ['appointment' => $appointment, 'cancelled' => false]);
    }

    public function cancelarStore($token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->first();

        if (!$appointment) {
            return redirect()->route('public.booking')->with('error', 'Link inválido.');
        }

        if (in_array($appointment->status, ['cancelled', 'completed'])) {
            return view('public.cancelamento', ['appointment' => $appointment, 'cancelled' => true]);
        }

        $appointment->update(['status' => 'cancelled']);

        NotificationLog::create([
            'appointment_id' => $appointment->id,
            'customer_id'    => $appointment->customer_id,
            'type'           => 'cancellation_link',
            'recipient'      => $appointment->customer->phone ?? null,
            'message'        => 'Cliente cancelou agendamento via link',
            'status'         => 'clicked',
            'sent_at'        => now(),
        ]);

        try {
            $wa = app(\App\Services\WhatsAppService::class);
            $wa->send($appointment->customer->phone,
                "Seu agendamento foi cancelado.\n" .
                "Data: {$appointment->start->format('d/m/Y H:i')}\n\n" .
                "Para reagendar: " . url('/reagendar/' . $token)
            );
        } catch (\Exception $e) {
            \Log::error('WhatsApp send failed: ' . $e->getMessage());
        }

        return view('public.cancelamento', ['appointment' => $appointment, 'cancelled' => true]);
    }

    public function sucesso()
    {
        if (!session('agendamento')) {
            return redirect()->route('public.booking');
        }

        return view('public.sucesso');
    }
}
