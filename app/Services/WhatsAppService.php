<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?Company $company;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $instance;

    public function __construct(?Company $company = null)
    {
        $this->company = $company;

        if ($company && $company->evolutionConfigured()) {
            $this->baseUrl  = rtrim($company->evolution_api_url, '/');
            $this->apiKey   = $company->evolution_api_key;
            $this->instance = $company->evolution_instance_name;
        } else {
            $this->baseUrl  = rtrim(config('services.whatsapp.api_url', 'http://localhost:8080'), '/');
            $this->apiKey   = config('services.whatsapp.api_key', '');
            $this->instance = config('services.whatsapp.instance', '');
        }
    }

    public function forCompany(Company $company): static
    {
        $this->company = $company;

        if ($company->evolutionConfigured()) {
            $this->baseUrl  = rtrim($company->evolution_api_url, '/');
            $this->apiKey   = $company->evolution_api_key;
            $this->instance = $company->evolution_instance_name;
        }

        return $this;
    }

    protected function headers(): array
    {
        return [
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    protected function resolveCompany(): void
    {
        if ($this->company) return;

        if (Auth::check()) {
            $this->forCompany(Auth::user()->company);
            return;
        }

        $first = Company::first();
        if ($first && $first->evolutionConfigured()) {
            $this->forCompany($first);
        }
    }

    protected function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->instance) && !empty($this->baseUrl);
    }

    public function send(string $phone, string $message): bool
    {
        $this->resolveCompany();

        if (!$this->isConfigured()) {
            Log::info('[WhatsApp Mock] Para: ' . $phone . ' | Msg: ' . $message);
            return true;
        }

        $phone = preg_replace('/\D/', '', $phone);

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                    'number'  => $phone,
                    'textMessage' => [
                        'text' => $message,
                    ],
                    'delay'   => 0,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('[WhatsApp] Erro ao enviar: ' . $e->getMessage());
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
             . "\n\nConfirme sua presença:\n{$confirmLink}"
             . "\n\nPrecisa remarcar?\n{$rescheduleLink}"
             . "\n\nCancelar:\n{$cancelLink}";

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
             . "Para reagendar:\n{$rescheduleLink}";

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
             . "Confirmar presença:\n{$confirmLink}\n\n"
             . "Cancelar:\n{$cancelLink}";

        return $this->send($appointment->customer->phone, $msg);
    }

    public function getInstanceStatus(): array
    {
        $this->resolveCompany();

        if (!$this->isConfigured()) {
            return ['connected' => false, 'message' => 'Evolution API não configurada'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");

            if (!$response->successful()) {
                return ['connected' => false, 'message' => 'Erro ao consultar status'];
            }

            $body = $response->json();

            $state = $body['state'] ?? $body['instance']['state'] ?? 'disconnected';

            return [
                'connected' => $state === 'open',
                'state' => $state,
                'message' => $state === 'open' ? 'Conectado' : 'Desconectado',
                'raw' => $body,
            ];
        } catch (\Exception $e) {
            return ['connected' => false, 'message' => 'Servidor Evolution não responde: ' . $e->getMessage()];
        }
    }

    public function connectInstance(): array
    {
        $this->resolveCompany();

        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Evolution API não configurada'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->get("{$this->baseUrl}/instance/connect/{$this->instance}");

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Erro ao conectar instância: ' . $response->body()];
            }

            $body = $response->json() ?? [];

            if (isset($body['base64'])) {
                $qrcode = $body['base64'];
                if (!str_starts_with($qrcode, 'data:')) {
                    $qrcode = 'data:image/png;base64,' . $qrcode;
                }
                return ['success' => true, 'qrcode' => $qrcode, 'message' => 'QR Code gerado'];
            }

            if (isset($body['qrcode'])) {
                $qrcode = $body['qrcode'];
                if (!str_starts_with($qrcode, 'data:')) {
                    $qrcode = 'data:image/png;base64,' . $qrcode;
                }
                return ['success' => true, 'qrcode' => $qrcode, 'message' => 'QR Code gerado'];
            }

            if (isset($body['pairingCode'])) {
                return ['success' => true, 'pairingCode' => $body['pairingCode'], 'message' => 'Código de pareamento gerado'];
            }

            return ['success' => false, 'message' => 'Resposta inesperada da API', 'debug' => array_keys($body)];
        } catch (\Exception $e) {
            \Log::error('[Evolution] Connect error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao conectar: ' . $e->getMessage()];
        }
    }

    public function disconnectInstance(): array
    {
        $this->resolveCompany();

        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Evolution API não configurada'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->delete("{$this->baseUrl}/instance/logout/{$this->instance}");

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Desconectado com sucesso'];
            }

            return ['success' => true, 'message' => 'Instância desconectada'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erro ao desconectar: ' . $e->getMessage()];
        }
    }

    public function createInstance(): array
    {
        $this->resolveCompany();

        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Evolution API não configurada'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->post("{$this->baseUrl}/instance/create", [
                    'instanceName' => $this->instance,
                    'qrcode' => true,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Instância criada'];
            }

            $body = $response->json() ?? [];
            $rawBody = is_string($body) ? $body : json_encode($body);

            if (str_contains($rawBody, 'already exists')) {
                return ['success' => true, 'message' => 'Instância já existe'];
            }

            return ['success' => true, 'message' => 'Continuando...'];
        } catch (\Exception $e) {
            return ['success' => true, 'message' => 'Continuando...'];
        }
    }
}
