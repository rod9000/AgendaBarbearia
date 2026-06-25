@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Evolution API — WhatsApp</h2>
        <a href="{{ route('admin.settings.whatsapp') }}" class="btn-pastel-secondary">Número WhatsApp</a>
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
                        <div id="qrcodeLoading" class="hidden text-sm text-brand-600">Aguardando conexão...</div>
                    </div>
                </div>
            @endif
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

        container.classList.remove('hidden');
        btn.textContent = 'Conectar WhatsApp';
        btn.disabled = false;

        if (data.qrcode) {
            var qrSrc = data.qrcode.startsWith('data:') ? data.qrcode : 'data:image/png;base64,' + data.qrcode;
            qrImage.innerHTML = '<img src="' + qrSrc + '" class="w-56 h-56">';
        } else if (data.pairingCode) {
            qrImage.innerHTML = '<div class="text-center"><p class="text-lg font-bold text-brand-700">' + data.pairingCode + '</p><p class="text-xs text-stone-500 mt-1">Código de pareamento</p></div>';
        }

        qrLoading.classList.remove('hidden');
        pollConnection();
    })
    .catch(function() {
        alert('Erro ao conectar. Verifique se o servidor Evolution está rodando.');
        btn.disabled = false;
        btn.textContent = 'Conectar WhatsApp';
    });
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
</script>
@endpush
