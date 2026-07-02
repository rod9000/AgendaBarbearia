<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class EvolutionController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $company = Auth::user()->company;

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Nenhuma empresa configurada. Cadastre uma empresa primeiro.');
        }

        $this->whatsapp->forCompany($company);

        $status = $this->whatsapp->getInstanceStatus();

        return view('admin.settings.evolution', compact('company', 'status'));
    }

    public function store(Request $request)
    {
        $company = Auth::user()->company;

        if (!$company) {
            return back()->with('error', 'Nenhuma empresa configurada.');
        }

        $data = $request->validate([
            'evolution_api_url' => 'required|url',
            'evolution_api_key' => 'required|string',
            'evolution_instance_name' => 'required|string|max:50',
            'evolution_webhook_url' => 'nullable|url',
            'whatsapp_type' => 'required|in:normal,business',
        ]);

        $company = Auth::user()->company;
        $company->update($data);

        return redirect()->route('admin.settings.evolution')
            ->with('success', 'Configurações salvas!');
    }

    public function connect()
    {
        $company = Auth::user()->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma empresa configurada.',
            ], 400);
        }

        $this->whatsapp->forCompany($company);

        $this->whatsapp->disconnectInstance();

        sleep(1);

        $this->whatsapp->createInstance();

        $result = $this->whatsapp->connectInstance();

        return response()->json($result);
    }

    public function status()
    {
        $company = Auth::user()->company;

        if (!$company) {
            return response()->json(['connected' => false, 'message' => 'Empresa não encontrada']);
        }

        $this->whatsapp->forCompany($company);

        $result = $this->whatsapp->getInstanceStatus();

        return response()->json($result);
    }

    public function disconnect()
    {
        $company = Auth::user()->company;
        $this->whatsapp->forCompany($company);

        $result = $this->whatsapp->disconnectInstance();

        if ($result['success']) {
            return redirect()->route('admin.settings.evolution')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.settings.evolution')
            ->with('error', $result['message']);
    }

    public function setWebhook()
    {
        $company = Auth::user()->company;
        $this->whatsapp->forCompany($company);

        if (!$company->evolutionConfigured()) {
            return response()->json(['success' => false, 'message' => 'Configure a Evolution API primeiro.'], 400);
        }

        $webhookUrl = $company->evolution_webhook_url;

        if (empty($webhookUrl)) {
            $webhookUrl = url('/api/webhook/evolution');
            $company->update(['evolution_webhook_url' => $webhookUrl]);
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $company->evolution_api_key,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post(
                rtrim($company->evolution_api_url, '/') . '/webhook/set/' . $company->evolution_instance_name,
                [
                    'enabled' => true,
                    'url' => $webhookUrl,
                    'webhook_by_events' => true,
                    'webhook_base64' => false,
                    'events' => ['MESSAGES_UPSERT'],
                ]
            );

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Webhook configurado: ' . $webhookUrl]);
            }

            return response()->json(['success' => false, 'message' => 'Erro ao configurar webhook', 'status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }
}
