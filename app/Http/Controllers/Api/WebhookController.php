<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Bot\BotHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receive(Request $request, BotHandler $botHandler): JsonResponse
    {
        $payload = $request->all();

        Log::info('[Webhook Evolution] Payload recebido:', ['body' => $payload]);

        if (!$this->isValidEvolutionPayload($payload)) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $data = $payload['data'] ?? $payload;

        if (($data['key']['fromMe'] ?? false)) {
            return response()->json(['status' => 'ignored_self'], 200);
        }

        $phone = $this->extractPhone($data);
        $message = $this->extractMessage($data);

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

    private function isValidEvolutionPayload(array $payload): bool
    {
        return isset($payload['event']) && $payload['event'] === 'messages.upsert';
    }

    private function extractPhone(array $data): ?string
    {
        $remoteJid = $data['key']['remoteJid'] ?? null;

        if (!$remoteJid) {
            return null;
        }

        $phone = str_replace('@s.whatsapp.net', '', $remoteJid);
        $phone = str_replace('@lid', '', $phone);

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
