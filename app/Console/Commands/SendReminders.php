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
    protected $description = 'Send WhatsApp reminders for upcoming appointments (24h before)';

    public function handle()
    {
        $now = Carbon::now();
        $targetStart = $now->copy();
        $targetEnd = $now->copy()->addHours(24);

        $appointments = Appointment::with(['customer', 'services', 'user'])
            ->whereBetween('start', [$targetStart, $targetEnd])
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereDoesntHave('notifications', function ($q) {
                $q->where('type', 'reminder');
            })
            ->get();

        $count = 0;
        $wa = app(WhatsAppService::class);

        foreach ($appointments as $app) {
            $success = $wa->sendReminder($app);

            NotificationLog::create([
                'appointment_id' => $app->id,
                'customer_id'    => $app->customer_id,
                'type'           => 'reminder',
                'recipient'      => $app->customer->phone,
                'message'        => 'Lembrete enviado para ' . $app->customer->name,
                'status'         => $success ? 'sent' : 'failed',
                'sent_at'        => now(),
            ]);

            $count++;
        }

        $this->info("Lembretes enviados: {$count}");

        return 0;
    }
}
