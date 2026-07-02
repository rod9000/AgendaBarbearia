<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\ReceiveWebhook;
use App\Models\Conversation;
use App\Services\Bot\BotHandler;
use Illuminate\Support\Facades\DB;

echo "=== Teste Webhook Evolution API ===\n\n";

// 1. Verificar empresa
$company = Company::where('evolution_instance_name', 'agenda-barbearia')->first();
if (!$company) {
    $company = Company::first();
}
if (!$company) {
    echo "ERRO: Nenhuma empresa encontrada no banco!\n";
    exit(1);
}
echo "Empresa: {$company->name} (ID: {$company->id})\n";
echo "Instance: {$company->evolution_instance_name}\n";
echo "Bot enabled: " . ($company->bot_enabled ? 'Sim' : 'Nao') . "\n\n";

// 2. Simular payload da Evolution API
$payload = [
    'event' => 'messages.upsert',
    'instance' => $company->evolution_instance_name,
    'data' => [
        'key' => [
            'remoteJid' => '5511999888777@s.whatsapp.net',
            'fromMe' => false,
            'id' => 'test_webhook_' . time(),
            'senderPn' => '5511999888777@s.whatsapp.net'
        ],
        'message' => [
            'conversation' => 'menu'
        ],
        'pushName' => 'Cliente Teste Webhook'
    ]
];

echo "Payload simulado:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// 3. Salvar webhook no banco
echo "3. Salvando webhook no banco...\n";
try {
    ReceiveWebhook::create([
        'instance' => $payload['instance'],
        'event' => $payload['event'],
        'sender_phone' => '5511999888777',
        'remote_jid' => $payload['data']['key']['remoteJid'],
        'from_me' => false,
        'message_content' => 'menu',
        'payload' => $payload,
    ]);
    echo "   OK: Webhook salvo em receive_webhooks\n";
} catch (\Exception $e) {
    echo "   ERRO: " . $e->getMessage() . "\n";
}

// 4. Processar via BotHandler
echo "\n4. Processando via BotHandler...\n";
try {
    $botHandler = app(BotHandler::class);
    $botHandler->handle('5511999888777', 'menu', $company, 'Cliente Teste Webhook');
    echo "   OK: BotHandler processou mensagem\n";
} catch (\Exception $e) {
    echo "   ERRO: " . $e->getMessage() . "\n";
}

// 5. Verificar gravações
echo "\n5. Verificando tabelas...\n";

$webhookCount = ReceiveWebhook::count();
echo "   receive_webhooks: {$webhookCount} registro(s)\n";

$conversationCount = Conversation::count();
echo "   conversations: {$conversationCount} registro(s)\n";

$lastConversation = Conversation::latest()->first();
if ($lastConversation) {
    echo "   Ultima conversa: phone={$lastConversation->phone}, state={$lastConversation->state}, customer_id={$lastConversation->customer_id}\n";
}

$lastWebhook = ReceiveWebhook::latest()->first();
if ($lastWebhook) {
    echo "   Ultimo webhook: event={$lastWebhook->event}, sender={$lastWebhook->sender_phone}\n";
}

echo "\n=== Teste concluido ===\n";
