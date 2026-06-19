<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\Service;
use App\Models\WorkingHour;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('name')->get();
        $services = Service::where('active', true)->orderBy('name')->get();
        $products = \App\Models\Product::where('quantity', '>', 0)->orderBy('name')->get();

        if (auth()->user()->isAdmin()) {
            $users = \App\Models\User::where('active', true)->orderBy('name')->get();
        } else {
            $users = \App\Models\User::where('id', auth()->id())->orderBy('name')->get();
        }

        $workingHours = WorkingHour::where('active', true)->get()->groupBy('user_id');

        return view('admin.appointments.index', compact('customers', 'services', 'products', 'users', 'workingHours'));
    }

    public function calendarData(Request $request)
    {
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));

        $query = Appointment::with(['customer', 'services', 'products', 'user', 'payment'])
            ->whereBetween('start', [$start, $end]);

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $appointments = $query->get()->map(function ($app) {
            $statusColors = [
                'scheduled'  => '#3b82f6',
                'confirmed'  => '#22c55e',
                'in_progress' => '#eab308',
                'completed'  => '#22c55e',
                'cancelled'  => '#ef4444',
                'no_show'    => '#f97316',
            ];

            $serviceNames = $app->services->pluck('name')->implode(' + ');
            $servicesTotal = $app->services->sum('pivot.price');
            $productsTotal = $app->products->sum(fn($p) => $p->pivot->quantity * $p->pivot->unit_price);
            $totalPrice = $servicesTotal + $productsTotal;

            $recurringInfo = null;
            if ($app->isRecurring()) {
                $recurringInfo = [
                    'frequency' => $app->recurring_frequency,
                    'until'     => $app->recurring_until?->format('Y-m-d'),
                ];
            } elseif ($app->isChild()) {
                $recurringInfo = [
                    'parent_id' => $app->parent_id,
                ];
            }

            return [
                'id'              => $app->id,
                'title'           => $app->customer->name . ' - ' . $serviceNames,
                'start'           => $app->start->toIso8601String(),
                'end'             => $app->end->toIso8601String(),
                'backgroundColor' => $statusColors[$app->status] ?? '#3b82f6',
                'borderColor'     => $statusColors[$app->status] ?? '#3b82f6',
                'extendedProps'   => [
                    'customer'       => $app->customer->name,
                    'customer_id'    => $app->customer_id,
                    'service'        => $serviceNames,
                    'service_id'     => $app->service_id,
                    'service_ids'    => $app->services->pluck('id')->toArray(),
                    'product_ids'    => $app->products->pluck('id')->toArray(),
                    'product_quantities' => $app->products->pluck('pivot.quantity')->toArray(),
                    'user_id'        => $app->user_id,
                    'status'         => $app->status,
                    'price'          => $totalPrice,
                    'services_price' => $servicesTotal,
                    'products_price' => $productsTotal,
                    'phone'          => $app->customer->phone,
                    'notes'          => $app->notes,
                    'user'           => $app->user->name,
                    'payment'        => $app->payment ? ['method' => $app->payment->method, 'amount' => $app->payment->amount] : null,
                    'recurring'      => $recurringInfo,
                ],
            ];
        });

        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'user_id'            => 'required|exists:users,id',
            'service_ids'        => 'required|array|min:1',
            'service_ids.*'      => 'exists:services,id',
            'product_ids'        => 'nullable|array',
            'product_ids.*'      => 'exists:products,id',
            'product_quantities' => 'nullable|array',
            'product_quantities.*' => 'integer|min:1',
            'start'              => 'required|date',
            'end'                => 'nullable|date|after:start',
            'notes'              => 'nullable|string',
            'recurring_frequency' => 'nullable|string|in:daily,weekly,biweekly,monthly',
            'recurring_until'    => 'nullable|date|after:start',
        ]);

        if (!auth()->user()->isAdmin()) {
            $data['user_id'] = auth()->id();
        }

        $services = Service::whereIn('id', $data['service_ids'])->get();
        $maxDuration = $services->max('duration_min');
        $data['service_id'] = $services->first()->id;

        if (!$data['end']) {
            $data['end'] = Carbon::parse($data['start'])->addMinutes($maxDuration);
        }

        $data['status'] = 'scheduled';

        $appointment = Appointment::create($data);

        $pivotData = [];
        foreach ($services as $service) {
            $pivotData[$service->id] = [
                'price'        => $service->price,
                'duration_min' => $service->duration_min,
            ];
        }
        $appointment->services()->sync($pivotData);

        if (!empty($data['product_ids'])) {
            $productPivotData = [];
            $productIds = $data['product_ids'];
            $quantities = $data['product_quantities'] ?? [];
            $products = \App\Models\Product::whereIn('id', $productIds)->get();
            foreach ($products as $index => $product) {
                $qty = $quantities[$index] ?? 1;
                $productPivotData[$product->id] = [
                    'quantity'   => $qty,
                    'unit_price' => $product->sale_price,
                ];
            }
            $appointment->products()->sync($productPivotData);
        }

        if (!empty($data['recurring_frequency']) && !empty($data['recurring_until'])) {
            $frequency = $data['recurring_frequency'];
            $until = Carbon::parse($data['recurring_until']);
            $start = Carbon::parse($data['start']);
            $end = Carbon::parse($data['end']);
            $durationMinutes = $start->diffInMinutes($end);

            $current = $start->copy();
            $intervalMap = [
                'daily'    => 'addDay',
                'weekly'   => 'addWeek',
                'biweekly' => 'addWeeks',
                'monthly'  => 'addMonth',
            ];
            $intervalFn = $intervalMap[$frequency] ?? 'addWeek';
            $intervalArg = $frequency === 'biweekly' ? 2 : 1;

            while (true) {
                if ($intervalArg !== 1) {
                    $current->{$intervalFn}($intervalArg);
                } else {
                    $current->{$intervalFn}();
                }

                if ($current->gt($until)) break;

                $child = Appointment::create([
                    'customer_id' => $data['customer_id'],
                    'user_id'     => $data['user_id'],
                    'service_id'  => $data['service_id'],
                    'start'       => $current->copy(),
                    'end'         => $current->copy()->addMinutes($durationMinutes),
                    'status'      => 'scheduled',
                    'notes'       => $data['notes'] ?? null,
                    'parent_id'   => $appointment->id,
                ]);

                $child->services()->sync($pivotData);
            }
        }

        $this->sendWhatsAppConfirmation($appointment);

        return response()->json(['success' => true, 'appointment' => $appointment->load(['customer', 'services', 'products', 'user'])]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $rules = [
            'customer_id'   => 'sometimes|required|exists:customers,id',
            'user_id'       => 'sometimes|required|exists:users,id',
            'service_ids'   => 'sometimes|required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'product_ids'   => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'product_quantities' => 'nullable|array',
            'product_quantities.*' => 'integer|min:1',
            'start'         => 'sometimes|required|date',
            'end'           => 'sometimes|required|date|after:start',
            'status'        => 'nullable|string|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'notes'         => 'nullable|string',
            'recurring_frequency' => 'nullable|string|in:daily,weekly,biweekly,monthly',
            'recurring_until'    => 'nullable|date',
            'update_all_series'  => 'nullable|boolean',
        ];

        if (!auth()->user()->isAdmin()) {
            $rules['user_id'] = 'sometimes|required|in:' . auth()->id();
        }

        $data = $request->validate($rules);
        $updateAll = !empty($data['update_all_series']);

        if (!empty($data['service_ids'])) {
            $services = Service::whereIn('id', $data['service_ids'])->get();
            $data['service_id'] = $services->first()->id;

            $pivotData = [];
            foreach ($services as $service) {
                $pivotData[$service->id] = [
                    'price'        => $service->price,
                    'duration_min' => $service->duration_min,
                ];
            }
        }

        $oldStatus = $appointment->status;
        $wasCompleted = $oldStatus !== 'completed' && ($data['status'] ?? '') === 'completed';
        $wasCancelled = $oldStatus !== 'cancelled' && ($data['status'] ?? '') === 'cancelled';

        if ($updateAll && ($wasCompleted || $wasCancelled) && $appointment->isRecurring()) {
            $appointment->children()->update(['status' => $data['status']]);
        }

        $appointment->update($data);

        if (!empty($pivotData)) {
            $appointment->services()->sync($pivotData);

            if ($updateAll && $appointment->isRecurring()) {
                foreach ($appointment->children as $child) {
                    $child->services()->sync($pivotData);
                }
            }
        }

        if (array_key_exists('product_ids', $data)) {
            $productPivotData = [];
            if (!empty($data['product_ids'])) {
                $productIds = $data['product_ids'];
                $quantities = $data['product_quantities'] ?? [];
                $products = \App\Models\Product::whereIn('id', $productIds)->get();
                foreach ($products as $index => $product) {
                    $qty = $quantities[$index] ?? 1;
                    $productPivotData[$product->id] = [
                        'quantity'   => $qty,
                        'unit_price' => $product->sale_price,
                    ];
                }
            }
            $appointment->products()->sync($productPivotData);
        }

        $servicesTotal = $appointment->services()->sum('appointment_service.price');
        $productsTotal = $appointment->products()
            ->sum(\Illuminate\Support\Facades\DB::raw('appointment_product.quantity * appointment_product.unit_price'));
        $totalPrice = $servicesTotal + $productsTotal;

        if ($wasCompleted && !$appointment->hasPayment()) {
            $appointment->payment()->create([
                'amount'        => $totalPrice,
                'method'        => 'dinheiro',
                'paid_at'       => now(),
                'registered_by' => auth()->id(),
            ]);
        }

        if ($wasCompleted) {
            $allServices = $appointment->services()->with('products')->get();
            $totalCommission = 0;
            foreach ($allServices as $service) {
                $totalCommission += $service->calculateCommission($service->pivot->price);
            }

            if ($totalCommission > 0) {
                Commission::updateOrCreate(
                    ['appointment_id' => $appointment->id],
                    [
                        'user_id' => $appointment->user_id,
                        'value'   => $totalCommission,
                        'paid'    => false,
                    ]
                );
            }

            $productsDeducted = [];
            foreach ($allServices as $service) {
                foreach ($service->products as $product) {
                    if (isset($productsDeducted[$product->id])) continue;
                    $productsDeducted[$product->id] = true;

                    $qty = $product->pivot->quantity;

                    if ($product->pivot->is_per_session) {
                        $jaConsumidoHoje = Appointment::where('customer_id', $appointment->customer_id)
                            ->where('id', '!=', $appointment->id)
                            ->whereIn('status', ['completed', 'in_progress'])
                            ->whereDate('start', today())
                            ->whereHas('services.products', fn($q) => $q->where('product_id', $product->id))
                            ->exists();

                        if ($jaConsumidoHoje) {
                            continue;
                        }
                    }

                    $product->removeStock($qty, "Procedimento {$service->name} - {$appointment->customer->name}");
                }
            }

            $soldProducts = $appointment->products;
            foreach ($soldProducts as $soldProduct) {
                $soldProduct->removeStock($soldProduct->pivot->quantity, "Venda no atendimento - {$appointment->customer->name}");
            }
        }

        return response()->json(['success' => true, 'appointment' => $appointment->fresh()->load(['customer', 'services', 'products', 'user', 'payment'])]);
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $deleteAll = $request->boolean('delete_all_series');

        if ($deleteAll && $appointment->isRecurring()) {
            $appointment->children()->delete();
        }

        $appointment->delete();
        return response()->json(['success' => true]);
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after:start',
        ]);

        $appointment->update($data);

        return response()->json(['success' => true]);
    }

    private function sendWhatsAppConfirmation(Appointment $appointment)
    {
        try {
            $appointment->load('services');
            $wa = new WhatsAppService();
            $serviceList = $appointment->services->map(fn($s) => $s->name . ' (' . $s->pivot->duration_min . 'min)')->implode("\n");
            $totalPrice = $appointment->services->sum('pivot.price');
            $confirmLink = url('/confirmar/' . $appointment->confirmation_token);
            $rescheduleLink = url('/reagendar/' . $appointment->confirmation_token);
            $msg = "Olá {$appointment->customer->name}, seu agendamento foi confirmado!\n"
                 . "Serviços:\n{$serviceList}\n"
                 . "Data: {$appointment->start->format('d/m/Y H:i')}\n"
                 . "Valor: R$ " . number_format($totalPrice, 2, ',', '.')
                 . "\n\n✅ Confirme sua presença:\n{$confirmLink}"
                 . "\n\n🔄 Precisa remarcar?\n{$rescheduleLink}";

            $wa->send($appointment->customer->phone, $msg);
        } catch (\Exception $e) {
            \Log::error('WhatsApp send failed: ' . $e->getMessage());
        }
    }
}
