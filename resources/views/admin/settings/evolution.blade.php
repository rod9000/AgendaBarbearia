@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Evolution API — WhatsApp</h2>
        <a href="{{ route('admin.settings.bot') }}" class="btn-pastel-secondary">Configurar Bot</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-4 text-sm text-emerald-600 dark:text-emerald-400">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-4 text-sm text-red-600 dark:text-red-400">
            {{ session('error') }}
        </div>
        @endif

        {{-- Config Form --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Configuração do Servidor</h3>
            <p class="text-sm text-stone-500 dark:text-stone-400 mb-4">
                Preencha com os dados do seu servidor Evolution API.
            </p>

            <form method="POST" action="{{ route('admin.settings.evolution.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="label">URL da API</label>
                        <input type="url" name="evolution_api_url" value="{{ old('evolution_api_url', $company->evolution_api_url) }}" placeholder="http://SEU_IP:8080" required class="input-pastel">
                        @error('evolution_api_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="label">API Key</label>
                        <input type="text" name="evolution_api_key" value="{{ old('evolution_api_key', $company->evolution_api_key) }}" placeholder="Chave definida no docker-compose" required class="input-pastel">
                        @error('evolution_api_key') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="label">Nome da Instância</label>
                        <input type="text" name="evolution_instance_name" value="{{ old('evolution_instance_name', $company->evolution_instance_name) }}" placeholder="agenda-barbearia" required class="input-pastel">
                        @error('evolution_instance_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="label">Tipo do WhatsApp</label>
                        <select name="whatsapp_type" required class="input-pastel">
                            <option value="normal" {{ old('whatsapp_type', $company->whatsapp_type ?? 'normal') === 'normal' ? 'selected' : '' }}>WhatsApp Normal</option>
                            <option value="business" {{ old('whatsapp_type', $company->whatsapp_type ?? 'normal') === 'business' ? 'selected' : '' }}>WhatsApp Business</option>
                        </select>
                        <p class="text-xs text-stone-400 mt-1">Selecione o tipo de WhatsApp que será conectado</p>
                        @error('whatsapp_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="label">URL do Webhook</label>
                        <input type="url" name="evolution_webhook_url" value="{{ old('evolution_webhook_url', $company->evolution_webhook_url) }}" placeholder="http://host.docker.internal:8001/api/webhook/evolution" class="input-pastel">
                        @error('evolution_webhook_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        <p class="text-xs text-stone-400 mt-1">URL que a Evolution API usará para enviar as mensagens recebidas. Em Docker, use <code>http://host.docker.internal:8001</code></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-pastel-primary">Salvar Configurações</button>
                </div>
            </form>
        </div>

        {{-- Connection Status --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Conexão WhatsApp</h3>

            <div id="connectionStatus" class="flex items-center gap-3 mb-4">
                @if($status['connected'] ?? false)
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="text-emerald-600 font-medium">Conectado</span>
                    <span class="text-xs text-stone-400 ml-2">({{ $company->evolution_instance_name }})</span>
                @else
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                    <span class="text-red-500 font-medium">Desconectado</span>
                @endif
            </div>

            @if(!$company->evolutionConfigured())
                <p class="text-sm text-stone-500">Salve as configurações do servidor para conectar.</p>
            @elseif($status['connected'] ?? false)
                <form method="POST" action="{{ route('admin.settings.evolution.disconnect') }}">
                    @csrf
                    <button type="submit" class="btn-pastel-secondary text-red-600 hover:text-red-700">Desconectar</button>
                </form>
            @else
                <div class="space-y-4">
                    <button id="connectBtn" onclick="connectInstance()" class="btn-pastel-primary">
                        Conectar WhatsApp
                    </button>
                    <div id="qrcodeContainer" class="hidden flex flex-col items-center gap-4 p-6">
                        <p class="text-sm text-stone-500">Escaneie o QR Code abaixo com seu WhatsApp:</p>
                        <div id="qrcodeImage" class="bg-white p-4 rounded-2xl shadow-sm"></div>
                        <p class="text-xs text-stone-400">Abra o WhatsApp > Menu > Aparelhos conectados > Conectar um dispositivo</p>
                        <button onclick="refreshQRCode()" id="refreshBtn" class="btn-pastel-secondary text-sm">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Atualizar QR Code
                        </button>
                        <div id="qrcodeLoading" class="hidden text-sm text-brand-600">Aguardando conexão...</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Webhook --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Webhook — Bot Automático</h3>
            <p class="text-sm text-stone-500 dark:text-stone-400 mb-4">
                Registre a URL de webhook no servidor Evolution para receber mensagens do WhatsApp e ativar o bot.
            </p>

            @if($company->evolution_webhook_url)
            <div class="mb-4 text-sm space-y-1">
                <p><strong>URL registrada:</strong> <code>{{ $company->evolution_webhook_url }}</code></p>
                @if($company->bot_enabled)
                    <p class="text-emerald-600">✅ Bot está ativo</p>
                @else
                    <p class="text-amber-600">⚠️ Bot está desativado nas <a href="{{ route('admin.settings.bot') }}" class="underline">configurações do bot</a></p>
                @endif
            </div>
            @endif

            <div class="flex gap-3">
                @if($company->evolutionConfigured())
                <button onclick="setWebhook()" id="webhookBtn" class="btn-pastel-primary">
                    {{ $company->evolution_webhook_url ? 'Reconfigurar Webhook' : 'Configurar Webhook' }}
                </button>
                @else
                <p class="text-sm text-stone-500">Salve as configurações do servidor primeiro.</p>
                @endif
            </div>
            <div id="webhookResult" class="mt-3 text-sm hidden"></div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function connectInstance() {
    const btn = document.getElementById('connectBtn');
    const container = document.getElementById('qrcodeContainer');
    const qrImage = document.getElementById('qrcodeImage');
    const qrLoading = document.getElementById('qrcodeLoading');

    btn.disabled = true;
    btn.textContent = 'Conectando...';
    container.classList.add('hidden');

    fetch('{{ route("admin.settings.evolution.connect") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            alert(data.message);
            btn.disabled = false;
            btn.textContent = 'Conectar WhatsApp';
            return;
        }

        // Inicia polling para buscar o QR Code
        pollQRCode();
    })
    .catch(function() {
        alert('Erro ao conectar. Verifique se o servidor Evolution está rodando.');
        btn.disabled = false;
        btn.textContent = 'Conectar WhatsApp';
    });
}

function pollQRCode() {
    var btn = document.getElementById('connectBtn');
    var container = document.getElementById('qrcodeContainer');
    var qrImage = document.getElementById('qrcodeImage');
    var qrLoading = document.getElementById('qrcodeLoading');
    var attempts = 0;
    var maxAttempts = 20;

    qrLoading.textContent = 'Buscando QR Code...';
    qrLoading.classList.remove('hidden');
    container.classList.remove('hidden');

    var interval = setInterval(function() {
        attempts++;

        fetch('{{ route("admin.settings.evolution.qrcode") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.qrcode) {
                clearInterval(interval);
                var qrSrc = data.qrcode.startsWith('data:') ? data.qrcode : 'data:image/png;base64,' + data.qrcode;
                qrImage.innerHTML = '<img src="' + qrSrc + '" class="w-56 h-56">';
                qrLoading.textContent = 'Escaneie o QR Code...';
                btn.disabled = false;
                btn.textContent = 'Conectar WhatsApp';
                pollConnection();
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                qrLoading.textContent = 'Tempo limite. Tente novamente.';
                btn.disabled = false;
                btn.textContent = 'Conectar WhatsApp';
            }
        })
        .catch(function() {
            if (attempts >= maxAttempts) {
                clearInterval(interval);
                qrLoading.textContent = 'Erro ao buscar QR Code.';
                btn.disabled = false;
                btn.textContent = 'Conectar WhatsApp';
            }
        });
    }, 3000);
}

function pollConnection() {
    var attempts = 0;
    var maxAttempts = 30;
    var interval = setInterval(function() {
        attempts++;
        fetch('{{ route("admin.settings.evolution.status") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.connected) {
                clearInterval(interval);
                document.getElementById('qrcodeLoading').textContent = 'Conectado!';
                location.reload();
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                document.getElementById('qrcodeLoading').textContent = 'Tempo limite excedido. Recarregue a página e tente novamente.';
            }
        })
        .catch(function() {
            if (attempts >= maxAttempts) {
                clearInterval(interval);
            }
        });
    }, 3000);
}

function setWebhook() {
    const btn = document.getElementById('webhookBtn');
    const result = document.getElementById('webhookResult');

    btn.disabled = true;
    btn.textContent = 'Configurando...';
    result.classList.add('hidden');

    fetch('{{ route("admin.settings.evolution.set-webhook") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        result.classList.remove('hidden');
        if (data.success) {
            result.className = 'mt-3 text-sm text-emerald-600';
            result.textContent = data.message;
        } else {
            result.className = 'mt-3 text-sm text-red-500';
            result.textContent = data.message;
        }
        btn.disabled = false;
        btn.textContent = 'Reconfigurar Webhook';
    })
    .catch(function() {
        result.classList.remove('hidden');
        result.className = 'mt-3 text-sm text-red-500';
        result.textContent = 'Erro de conexão. Verifique se o servidor Evolution está rodando.';
        btn.disabled = false;
        btn.textContent = 'Reconfigurar Webhook';
    });
}

function refreshQRCode() {
    const btn = document.getElementById('refreshBtn');
    const container = document.getElementById('qrcodeContainer');
    const qrImage = document.getElementById('qrcodeImage');
    const qrLoading = document.getElementById('qrcodeLoading');

    btn.disabled = true;
    btn.textContent = 'Atualizando...';
    container.classList.remove('hidden');

    fetch('{{ route("admin.settings.evolution.connect") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            alert(data.message);
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Atualizar QR Code';
            return;
        }

        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Atualizar QR Code';

        if (data.qrcode) {
            var qrSrc = data.qrcode.startsWith('data:') ? data.qrcode : 'data:image/png;base64,' + data.qrcode;
            qrImage.innerHTML = '<img src="' + qrSrc + '" class="w-56 h-56">';
        } else if (data.pairingCode) {
            qrImage.innerHTML = '<div class="text-center"><p class="text-lg font-bold text-brand-700">' + data.pairingCode + '</p><p class="text-xs text-stone-500 mt-1">Código de pareamento</p></div>';
        }

        qrLoading.classList.remove('hidden');
        qrLoading.textContent = 'Escaneie o novo QR Code...';
        pollConnection();
    })
    .catch(function() {
        alert('Erro ao atualizar. Verifique se o servidor Evolution está rodando.');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Atualizar QR Code';
    });
}
</script>
@endpush
