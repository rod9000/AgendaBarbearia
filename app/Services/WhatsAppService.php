<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $instance;

    public function __construct()
    {
        $this->apiUrl   = config('services.whatsapp.api_url', 'http://localhost:8080');
        $this->apiKey   = config('services.whatsapp.api_key', '');
        $this->instance = config('services.whatsapp.instance', '');
    }

    public function send(string $phone, string $message): bool
    {
        if (empty($this->apiKey) || empty($this->instance)) {
            Log::info('[WhatsApp Mock] Para: ' . $phone . ' | Msg: ' . $message);
            return true;
        }

        $phone = preg_replace('/\D/', '', $phone);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->post("{$this->apiUrl}/message/sendText/{$this->instance}", [
                'number'  => $phone,
                'text'    => $message,
                'delay'   => 1000,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendConfirmation(Appointment $appointment): bool
    {
        $appointment->load('services', 'customer');

        $serviceList = $appointment->services->map(
            fn($s) => $s->name . ' (' . $s->pivot->duration_min . 'min)'
        )->implode("\n");

        $totalPrice = $appointment->services->sum('pivot.price');
        $confirmLink = url('/confirmar/' . $appointment->confirmation_token);
        $rescheduleLink = url('/reagendar/' . $appointment->confirmation_token);
        $cancelLink = url('/cancelar/' . $appointment->confirmation_token);

        $msg = "Olá {$appointment->customer->name}, seu agendamento foi confirmado!\n"
             . "Serviços:\n{$serviceList}\n"
             . "Data: {$appointment->start->format('d/m/Y H:i')}\n"
             . "Valor: R$ " . number_format($totalPrice, 2, ',', '.')
             . "\n\n✅ Confirme sua presença:\n{$confirmLink}"
             . "\n\n🔄 Precisa remarcar?\n{$rescheduleLink}"
             . "\n\n❌ Cancelar:\n{$cancelLink}";

        return $this->send($appointment->customer->phone, $msg);
    }

    public function sendCancellation(Appointment $appointment): bool
    {
        $appointment->load('services', 'customer');

        $serviceList = $appointment->services->pluck('name')->implode(', ');
        $rescheduleLink = url('/reagendar/' . $appointment->confirmation_token);

        $msg = "Olá {$appointment->customer->name}, seu agendamento foi cancelado.\n"
             . "Serviço: {$serviceList}\n"
             . "Data: {$appointment->start->format('d/m/Y H:i')}\n\n"
             . "🔄 Para reagendar:\n{$rescheduleLink}";

        return $this->send($appointment->customer->phone, $msg);
    }

    public function sendReminder(Appointment $appointment): bool
    {
        $appointment->load('services', 'customer');

        $serviceList = $appointment->services->pluck('name')->implode(', ');
        $confirmLink = url('/confirmar/' . $appointment->confirmation_token);
        $cancelLink = url('/cancelar/' . $appointment->confirmation_token);

        $msg = "Olá {$appointment->customer->name}, lembrete do seu agendamento!\n"
             . "Serviço: {$serviceList}\n"
             . "Data: {$appointment->start->format('d/m/Y H:i')}\n"
             . "Profissional: {$appointment->user->name}\n\n"
             . "✅ Confirmar presença:\n{$confirmLink}\n\n"
             . "❌ Cancelar:\n{$cancelLink}";

        return $this->send($appointment->customer->phone, $msg);
    }
}
