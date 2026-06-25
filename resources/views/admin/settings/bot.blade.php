@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Bot WhatsApp</h2>
        <a href="{{ route('admin.settings.evolution') }}" class="btn-pastel-secondary">Evolution API</a>
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

        {{-- Status do Bot --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Status do Bot</h3>
            <div class="flex items-center gap-3">
                @if($company->bot_enabled)
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="text-emerald-600 font-medium">Ativo</span>
                @else
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                    <span class="text-red-500 font-medium">Inativo</span>
                @endif
            </div>
        </div>

        {{-- Configurações --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Configurações do Bot</h3>
            <form method="POST" action="{{ route('admin.settings.bot.store') }}">
                @csrf
                <div class="space-y-6">

                    {{-- Toggle Bot --}}
                    <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <div>
                            <p class="font-medium text-stone-700 dark:text-stone-300">Habilitar Bot</p>
                            <p class="text-sm text-stone-500 dark:text-stone-400">Ativa o atendimento automático via WhatsApp</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="bot_enabled" value="1" {{ $company->bot_enabled ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-stone-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    {{-- Mensagem de Boas-vindas --}}
                    <div>
                        <label class="label">Mensagem de Boas-vindas</label>
                        <p class="text-xs text-stone-500 mb-2">Mensagem enviada quando o cliente inicia a conversa. Deixe vazio para usar o padrão.</p>
                        <textarea name="welcome_message" rows="5" class="input-pastel" placeholder="Olá! Bem-vindo(a) à barberia! Como posso te ajudar?&#10;&#10;1 - Agendar horário&#10;2 - Horários de funcionamento&#10;3 - Serviços e preços&#10;4 - Consultar agendamentos&#10;5 - Cancelar agendamento&#10;6 - Localização&#10;&#10;Digite o número da opção desejada:">{{ old('welcome_message', $company->welcome_message) }}</textarea>
                        @error('welcome_message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Mensagem Fora do Horário --}}
                    <div>
                        <label class="label">Mensagem Fora do Horário</label>
                        <p class="text-xs text-stone-500 mb-2">Mensagem enviada quando o cliente envia msg fora do horário de funcionamento.</p>
                        <textarea name="off_hours_message" rows="4" class="input-pastel" placeholder="Olá! No momento estamos fora do horário de atendimento.&#10;Funcionamos de segunda a sábado, das 09:00 às 19:00.&#10;Deixe sua mensagem que retornamos no próximo horário!">{{ old('off_hours_message', $company->off_hours_message) }}</textarea>
                        @error('off_hours_message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-pastel-primary">Salvar Configurações</button>
                </div>
            </form>
        </div>

        {{-- Como funciona --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Como Funciona</h3>
            <div class="space-y-3 text-sm text-stone-600 dark:text-stone-400">
                <p>Quando um cliente envia uma mensagem para o número do WhatsApp conectado:</p>
                <ol class="list-decimal list-inside space-y-2">
                    <li>O bot recebe a mensagem automaticamente</li>
                    <li>Apresenta o menu de opções</li>
                    <li>O cliente escolhe o que deseja (agendar, consultar, cancelar, etc.)</li>
                    <li>O bot guia o cliente passo a passo</li>
                    <li>O agendamento é criado automaticamente no sistema</li>
                    <li>O cliente recebe a confirmação pelo WhatsApp</li>
                </ol>
                <p class="mt-4 font-medium">Comandos especiais:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>0</strong> ou <strong>voltar</strong> — Volta ao menu principal</li>
                    <li><strong>menu</strong> — Exibe o menu de opções</li>
                </ul>
            </div>
        </div>

        {{-- Pré-visualização --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Pré-visualização</h3>
            <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                        B
                    </div>
                    <div class="bg-white dark:bg-stone-800 rounded-2xl rounded-tl-none p-4 max-w-sm shadow-sm">
                        <p class="text-sm whitespace-pre-line">{{ $company->getDefaultWelcomeMessage() }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
