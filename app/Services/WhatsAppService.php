<?php

namespace App\Services;

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
}
