<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'send:reminders';
    protected $description = 'Send WhatsApp reminders for upcoming appointments';

    public function handle()
    {
        $now = Carbon::now();
        $targetStart = $now->copy()->addHour();
        $targetEnd = $now->copy()->addHours(2);

        $appointments = Appointment::with(['customer', 'service'])
            ->whereBetween('start', [$targetStart, $targetEnd])
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereDoesntHave('notifications', function ($q) {
                $q->where('type', 'reminder');
            })
            ->get();

        $count = 0;
        $wa = new WhatsAppService();

        foreach ($appointments as $app) {
            $msg = "🔔 Lembrete: {$app->customer->name}, seu horário é às "
                 . $app->start->format('H:i') . "h!\n"
                 . "Serviço: {$app->service->name}\n"
                 . "Duração: {$app->service->duration_min} min\n"
                 . "Valor: R$ " . number_format($app->service->price, 2, ',', '.') . "\n"
                 . "Local: Clínica de Estética";

            $success = $wa->send($app->customer->phone, $msg);

            NotificationLog::create([
                'appointment_id' => $app->id,
                'customer_id'    => $app->customer_id,
                'type'           => 'reminder',
                'recipient'      => $app->customer->phone,
                'message'        => $msg,
                'status'         => $success ? 'sent' : 'failed',
                'sent_at'        => now(),
            ]);

            $count++;
        }

        $this->info("Lembretes enviados: {$count}");

        return 0;
    }
}
