<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\ReceiveWebhook;
use App\Services\Bot\BotHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receive(Request $request, BotHandler $botHandler): JsonResponse
    {
        $payload = $request->all();

        if (isset($payload['body']) && is_array($payload['body'])) {
            $payload = $payload['body'];
        }

        $data = $payload['data'] ?? $payload;

        $this->saveWebhook($payload, $data);

        Log::info('[Webhook Evolution] Payload recebido:', ['event' => $payload['event'] ?? 'none', 'instance' => $payload['instance'] ?? 'none']);

        if (!$this->isValidEvolutionPayload($payload)) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (($data['key']['fromMe'] ?? false)) {
            return response()->json(['status' => 'ignored_self'], 200);
        }

        $phone = $this->extractPhone($data);
        $message = $this->extractMessage($data);

        Log::info('[Webhook Evolution] Mensagem processada:', ['phone' => $phone, 'message' => $message]);

        if (!$phone || !$message) {
            return response()->json(['status' => 'no_data'], 200);
        }

        $instanceName = $payload['instance'] ?? null;
        $company = $this->resolveCompany($instanceName);

        if (!$company) {
            Log::warning('[Webhook Evolution] Empresa não encontrada:', ['instance' => $instanceName]);
            return response()->json(['status' => 'no_company'], 200);
        }

        if (!$company->bot_enabled) {
            return response()->json(['status' => 'bot_disabled'], 200);
        }

        $botHandler->handle($phone, $message, $company);

        return response()->json(['status' => 'ok']);
    }

    private function saveWebhook(array $payload, array $data): void
    {
        try {
            ReceiveWebhook::create([
                'instance' => $payload['instance'] ?? null,
                'event' => $payload['event'] ?? null,
                'sender_phone' => $this->extractPhone($data),
                'remote_jid' => $data['key']['remoteJid'] ?? null,
                'from_me' => $data['key']['fromMe'] ?? false,
                'message_content' => $this->extractMessage($data),
                'payload' => $payload,
            ]);
        } catch (\Exception $e) {
            Log::error('[Webhook] Erro ao salvar webhook: ' . $e->getMessage());
        }
    }

    private function isValidEvolutionPayload(array $payload): bool
    {
        $event = strtolower($payload['event'] ?? '');
        return $event === 'messages.upsert' || $event === 'messages_upsert';
    }

    private function extractPhone(array $data): ?string
    {
        $senderPn = $data['key']['senderPn'] ?? null;
        if ($senderPn) {
            $phone = str_replace(['@s.whatsapp.net', '@lid'], '', $senderPn);
            $phone = preg_replace('/\D/', '', $phone);
            if (!empty($phone)) {
                return $phone;
            }
        }

        $remoteJid = $data['key']['remoteJid'] ?? null;
        if (!$remoteJid) {
            return null;
        }

        $phone = str_replace(['@s.whatsapp.net', '@lid'], '', $remoteJid);
        return preg_replace('/\D/', '', $phone);
    }

    private function extractMessage(array $data): ?string
    {
        $message = $data['message']['conversation']
            ?? $data['message']['extendedTextMessage']['text']
            ?? null;

        if ($message) {
            $message = trim($message);
        }

        return $message;
    }

    private function resolveCompany(?string $instanceName): ?Company
    {
        if ($instanceName) {
            $company = Company::where('evolution_instance_name', $instanceName)->first();
            if ($company) {
                return $company;
            }
        }

        return Company::where('bot_enabled', true)->first();
    }
}
