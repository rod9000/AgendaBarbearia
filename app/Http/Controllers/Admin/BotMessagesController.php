<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\BotMessage;
use App\Models\Customer;
use App\Models\Company;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BotMessagesController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');

        $conversations = Conversation::with(['customer', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])
        ->withCount('messages')
        ->latest('last_message_at')
        ->when($search, function ($query, $search) {
            $query->where('phone', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        })
        ->paginate(20);

        return view('admin.bot-messages.index', compact('conversations', 'search'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load(['customer', 'messages' => function ($query) {
            $query->oldest();
        }]);

        return view('admin.bot-messages.show', compact('conversation'));
    }

    public function send(Request $request, Conversation $conversation)
    {
        $data = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $company = Auth::user()->company;
        $this->whatsapp->forCompany($company);

        $sent = $this->whatsapp->send($conversation->phone, $data['message']);

        if ($sent) {
            $conversation->messages()->create([
                'direction' => 'outbound',
                'content' => $data['message'],
            ]);

            $conversation->update(['last_message_at' => now()]);

            return back()->with('success', 'Mensagem enviada!');
        }

        return back()->with('error', 'Erro ao enviar mensagem. Verifique a conexão.');
    }

    public function startConversation(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'message' => 'required|string|max:4000',
        ]);

        $company = Auth::user()->company;
        $this->whatsapp->forCompany($company);

        $phone = preg_replace('/\D/', '', $data['phone']);

        $conversation = Conversation::firstOrCreate(
            ['company_id' => $company->id, 'phone' => $phone],
            ['state' => 'initial', 'last_message_at' => now()]
        );

        $sent = $this->whatsapp->send($phone, $data['message']);

        if ($sent) {
            $conversation->messages()->create([
                'direction' => 'outbound',
                'content' => $data['message'],
            ]);

            $conversation->update(['last_message_at' => now()]);

            return redirect()->route('admin.bot-messages.show', $conversation)
                ->with('success', 'Conversa iniciada e mensagem enviada!');
        }

        return back()->with('error', 'Erro ao enviar mensagem. Verifique a conexão.');
    }

    public function sync()
    {
        $company = Auth::user()->company;
        $this->whatsapp->forCompany($company);

        if (!$company->evolutionConfigured()) {
            return back()->with('error', 'Configure a Evolution API primeiro.');
        }

        $baseUrl = rtrim($company->evolution_api_url, '/');
        $apiKey = $company->evolution_api_key;
        $instance = $company->evolution_instance_name;

        try {
            $chatsResponse = Http::withHeaders([
                'apikey' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get("{$baseUrl}/chat/findChats/{$instance}");

            if (!$chatsResponse->successful()) {
                return back()->with('error', 'Erro ao buscar chats: ' . $chatsResponse->body());
            }

            $chats = $chatsResponse->json();
            $synced = 0;

            foreach ($chats as $chat) {
                $remoteJid = $chat['key']['remoteJid'] ?? null;
                if (!$remoteJid || str_contains($remoteJid, '@g.us')) {
                    continue;
                }

                $phone = preg_replace('/\D/', '', str_replace(['@s.whatsapp.net', '@lid'], '', $remoteJid));
                if (empty($phone)) {
                    continue;
                }

                $pushName = $chat['pushName'] ?? null;
                $lastMessage = $chat['messages'][0] ?? null;

                $conversation = Conversation::firstOrCreate(
                    ['company_id' => $company->id, 'phone' => $phone],
                    ['state' => 'initial']
                );

                if ($pushName && !$conversation->customer_id) {
                    $customer = Customer::where('phone', $phone)->first();
                    if (!$customer) {
                        $customer = Customer::create([
                            'name' => $pushName,
                            'phone' => $phone,
                            'created_by' => null,
                        ]);
                    }
                    $conversation->update(['customer_id' => $customer->id]);
                }

                $messagesResponse = Http::withHeaders([
                    'apikey' => $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post("{$baseUrl}/chat/findMessages/{$instance}", [
                    'where' => ['key' => ['remoteJid' => $remoteJid]],
                    'limit' => 50,
                ]);

                if ($messagesResponse->successful()) {
                    $messages = $messagesResponse->json();

                    foreach ($messages as $msg) {
                        $msgKey = $msg['key'] ?? [];
                        $fromMe = $msgKey['fromMe'] ?? false;
                        $msgContent = $msg['message']['conversation']
                            ?? $msg['message']['extendedTextMessage']['text']
                            ?? null;

                        if (!$msgContent) {
                            continue;
                        }

                        $exists = $conversation->messages()
                            ->where('content', $msgContent)
                            ->where('direction', $fromMe ? 'outbound' : 'inbound')
                            ->exists();

                        if (!$exists) {
                            $conversation->messages()->create([
                                'direction' => $fromMe ? 'outbound' : 'inbound',
                                'content' => $msgContent,
                            ]);
                        }
                    }

                    $latestMsg = $conversation->messages()->latest()->first();
                    if ($latestMsg) {
                        $conversation->update(['last_message_at' => $latestMsg->created_at]);
                    }
                }

                $synced++;
            }

            return back()->with('success', "{$synced} conversas sincronizadas com sucesso!");
        } catch (\Exception $e) {
            Log::error('[Bot Sync] Erro: ' . $e->getMessage());
            return back()->with('error', 'Erro ao sincronizar: ' . $e->getMessage());
        }
    }
}
