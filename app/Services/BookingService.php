<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\Customer;
use App\Models\Service;
use App\Models\WorkingHour;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class BookingService
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function getServiceUserCombos(): \Illuminate\Support\Collection
    {
        $services = Service::where('active', true)->get();
        $users = \App\Models\User::where('active', true)->where('role', 'attendant')->get();

        $combos = collect();
        foreach ($users as $user) {
            foreach ($services as $service) {
                $combos->push((object) [
                    'user_id'      => $user->id,
                    'user_name'    => $user->name,
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                    'duration_min'  => $service->duration_min,
                    'price'        => $service->price,
                    'color_hex'    => $service->color_hex,
                ]);
            }
        }

        return $combos;
    }

    public function getAvailableSlots(array $serviceIds, ?int $userId, string $date): array
    {
        $services = Service::whereIn('id', $serviceIds)->get();
        $maxDuration = $services->max('duration_min');

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        if ($userId) {
            $workingHours = WorkingHour::where('user_id', $userId)
                ->where('day_of_week', $dayOfWeek)
                ->where('active', true)
                ->get();
        } else {
            $workingHours = WorkingHour::where('day_of_week', $dayOfWeek)
                ->where('active', true)
                ->get();
        }

        if ($workingHours->isEmpty()) {
            return [];
        }

        $startWork = $workingHours->min('start_time');
        $hasMidnight = $workingHours->contains('end_time', '00:00:00');
        $endWork = $hasMidnight ? '00:00:00' : $workingHours->max('end_time');

        $existingAppointments = Appointment::whereDate('start', $date)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        $blockedSlots = BlockedSlot::whereDate('start', '<=', $date)
            ->whereDate('end', '>=', $date)
            ->when($userId, fn($q) => $q->where('user_id', $userId)->orWhereNull('user_id'))
            ->get();

        $slots = [];
        $current = Carbon::parse($date . ' ' . $startWork);
        $end = Carbon::parse($date . ' ' . $endWork);

        while ($current->copy()->addMinutes($maxDuration)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($maxDuration);

            $overlaps = false;

            foreach ($existingAppointments as $appointment) {
                if ($current->lt($appointment->end) && $slotEnd->gt($appointment->start)) {
                    $overlaps = true;
                    break;
                }
            }

            if (!$overlaps) {
                foreach ($blockedSlots as $blocked) {
                    $blockedStart = Carbon::parse($blocked->start);
                    $blockedEnd = Carbon::parse($blocked->end);
                    if ($current->lt($blockedEnd) && $slotEnd->gt($blockedStart)) {
                        $overlaps = true;
                        break;
                    }
                }
            }

            if (!$overlaps && $current->isFuture()) {
                $slots[] = $current->format('H:i');
            }

            $current->addMinutes(30);
        }

        return $slots;
    }

    public function createBooking(array $data): Appointment
    {
        $customer = $this->findOrCreateCustomer($data);

        $services = Service::whereIn('id', $data['service_ids'])->get();
        $maxDuration = $services->max('duration_min');

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'user_id'     => $data['user_id'],
            'service_id'  => $services->first()->id,
            'start'       => Carbon::parse($data['date'] . ' ' . $data['time']),
            'end'         => Carbon::parse($data['date'] . ' ' . $data['time'])->addMinutes($maxDuration),
            'status'      => 'scheduled',
            'notes'       => $data['notes'] ?? null,
        ]);

        $pivotData = [];
        foreach ($services as $service) {
            $pivotData[$service->id] = [
                'price'        => $service->price,
                'duration_min' => $service->duration_min,
            ];
        }
        $appointment->services()->sync($pivotData);

        $this->whatsapp->sendConfirmation($appointment);

        return $appointment;
    }

    public function reschedule(string $token, array $data): ?Appointment
    {
        $appointment = Appointment::where('confirmation_token', $token)->first();

        if (!$appointment) {
            return null;
        }

        $services = Service::whereIn('id', $data['service_ids'])->get();
        $maxDuration = $services->max('duration_min');

        $appointment->update([
            'user_id' => $data['user_id'],
            'service_id' => $services->first()->id,
            'start' => Carbon::parse($data['date'] . ' ' . $data['time']),
            'end' => Carbon::parse($data['date'] . ' ' . $data['time'])->addMinutes($maxDuration),
        ]);

        $pivotData = [];
        foreach ($services as $service) {
            $pivotData[$service->id] = [
                'price'        => $service->price,
                'duration_min' => $service->duration_min,
            ];
        }
        $appointment->services()->sync($pivotData);

        $this->whatsapp->sendConfirmation($appointment);

        return $appointment;
    }

    private function findOrCreateCustomer(array $data): Customer
    {
        $customer = Customer::where('cpf', $data['cpf'])->first();

        if (!$customer) {
            $customer = Customer::create([
                'name'       => $data['name'],
                'cpf'        => $data['cpf'],
                'phone'      => $data['phone'],
                'email'      => $data['email'] ?? null,
                'created_by' => null,
            ]);
        }

        return $customer;
    }
}
