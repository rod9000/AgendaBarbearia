<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Service;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentService
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function create(array $data): Appointment
    {
        $services = Service::whereIn('id', $data['service_ids'])->get();
        $maxDuration = $services->max('duration_min');
        $data['service_id'] = $services->first()->id;

        if (!isset($data['end']) || !$data['end']) {
            $data['end'] = Carbon::parse($data['start'])->addMinutes($maxDuration);
        }

        $data['status'] = 'scheduled';

        $appointment = Appointment::create($data);

        $this->syncServices($appointment, $services);

        if (!empty($data['recurring_frequency']) && !empty($data['recurring_until'])) {
            $this->createRecurring($appointment, $data);
        }

        $this->whatsapp->sendConfirmation($appointment);

        return $appointment;
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        $oldStatus = $appointment->status;
        $wasCompleted = $oldStatus !== 'completed' && ($data['status'] ?? '') === 'completed';
        $wasCancelled = $oldStatus !== 'cancelled' && ($data['status'] ?? '') === 'cancelled';
        $updateAll = !empty($data['update_all_series']);

        if ($updateAll && ($wasCompleted || $wasCancelled) && $appointment->isRecurring()) {
            $appointment->children()->update(['status' => $data['status']]);
        }

        if (!empty($data['service_ids'])) {
            $services = Service::whereIn('id', $data['service_ids'])->get();
            $data['service_id'] = $services->first()->id;
            $this->syncServices($appointment, $services);

            if ($updateAll && $appointment->isRecurring()) {
                foreach ($appointment->children as $child) {
                    $this->syncServices($child, $services);
                }
            }
        }

        $appointment->update($data);

        if ($wasCompleted) {
            $this->complete($appointment);
        }

        if ($wasCancelled) {
            $this->whatsapp->sendCancellation($appointment);
        }

        return $appointment;
    }

    public function complete(Appointment $appointment): void
    {
        $totalPrice = $appointment->services()->sum('appointment_service.price');

        if (!$appointment->hasPayment()) {
            $appointment->payment()->create([
                'amount'        => $totalPrice,
                'method'        => 'dinheiro',
                'paid_at'       => now(),
                'registered_by' => auth()->id(),
            ]);
        }

        $this->createCommission($appointment);
        $this->deductServiceProducts($appointment);
        $this->deductSoldProducts($appointment);
    }

    public function getCalendarData(Request $request): \Illuminate\Support\Collection
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

        return $query->get()->map(function ($app) {
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
                    'price'          => $servicesTotal + $productsTotal,
                    'services_price' => $servicesTotal,
                    'products_price' => $productsTotal,
                    'phone'          => $app->customer->phone,
                    'notes'          => $app->notes,
                    'user'           => $app->user->name,
                    'payment'        => $app->payment ? ['method' => $app->payment->method, 'amount' => $app->payment->amount] : null,
                    'recurring'      => $app->isRecurring() ? ['frequency' => $app->recurring_frequency, 'until' => $app->recurring_until?->format('Y-m-d')] : ($app->isChild() ? ['parent_id' => $app->parent_id] : null),
                ],
            ];
        });
    }

    private function syncServices(Appointment $appointment, $services): void
    {
        $pivotData = [];
        foreach ($services as $service) {
            $pivotData[$service->id] = [
                'price'        => $service->price,
                'duration_min' => $service->duration_min,
            ];
        }
        $appointment->services()->sync($pivotData);
    }

    private function createRecurring(Appointment $appointment, array $data): void
    {
        $frequency = $data['recurring_frequency'];
        $until = Carbon::parse($data['recurring_until']);
        $start = Carbon::parse($data['start']);
        $durationMinutes = $start->diffInMinutes(Carbon::parse($data['end']));

        $current = $start->copy();
        $intervalMap = [
            'daily' => 'addDay', 'weekly' => 'addWeek',
            'biweekly' => 'addWeeks', 'monthly' => 'addMonth',
        ];
        $intervalFn = $intervalMap[$frequency] ?? 'addWeek';
        $intervalArg = $frequency === 'biweekly' ? 2 : 1;

        $services = $appointment->services;
        $pivotData = [];
        foreach ($services as $service) {
            $pivotData[$service->id] = [
                'price' => $service->pivot->price,
                'duration_min' => $service->pivot->duration_min,
            ];
        }

        while (true) {
            $current->{$intervalFn}($intervalArg);
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

    private function createCommission(Appointment $appointment): void
    {
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
    }

    private function deductServiceProducts(Appointment $appointment): void
    {
        $allServices = $appointment->services()->with('products')->get();
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

                    if ($jaConsumidoHoje) continue;
                }

                $product->removeStock($qty, "Serviço {$service->name} - {$appointment->customer->name}");
            }
        }
    }

    private function deductSoldProducts(Appointment $appointment): void
    {
        $soldProducts = $appointment->products;
        foreach ($soldProducts as $soldProduct) {
            $soldProduct->removeStock($soldProduct->pivot->quantity, "Venda no atendimento - {$appointment->customer->name}");
        }
    }
}
