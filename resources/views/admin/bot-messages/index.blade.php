@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Mensagens do Bot</h2>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.bot-messages.sync') }}" class="inline">
                @csrf
                <button type="submit" class="btn-pastel-secondary" onclick="return confirm('Sincronizar conversas existentes do WhatsApp?')">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sincronizar
                </button>
            </form>
            <button onclick="document.getElementById('newConversationModal').classList.remove('hidden')" class="btn-pastel-primary">
                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Conversa
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-4 text-sm text-emerald-600 dark:text-emerald-400 mb-4">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-4 text-sm text-red-600 dark:text-red-400 mb-4">
            {{ session('error') }}
        </div>
        @endif

        {{-- Busca --}}
        <div class="card-pastel mb-4">
            <form method="GET" action="{{ route('admin.bot-messages.index') }}" class="flex gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por telefone ou nome..." class="input-pastel flex-1">
                <button type="submit" class="btn-pastel-primary">Buscar</button>
                @if($search)
                    <a href="{{ route('admin.bot-messages.index') }}" class="btn-pastel-secondary">Limpar</a>
                @endif
            </form>
        </div>

        {{-- Lista de conversas --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Conversas</h3>

            @if($conversations->isEmpty())
                <p class="text-stone-500 text-sm">Nenhuma conversa encontrada.</p>
            @else
                <div class="space-y-2">
                    @foreach($conversations as $conversation)
                        <a href="{{ route('admin.bot-messages.show', $conversation) }}" class="block p-4 bg-stone-50 dark:bg-stone-800 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-700 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-stone-800 dark:text-stone-200">
                                            {{ $conversation->customer?->name ?? 'Desconhecido' }}
                                        </span>
                                        <span class="text-xs text-stone-400">({{ $conversation->phone }})</span>
                                        @if($conversation->customer_id)
                                            <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs rounded-full">Cliente</span>
                                        @endif
                                    </div>
                                    @if($conversation->messages->isNotEmpty())
                                        <p class="text-sm text-stone-500 truncate">
                                            <span class="{{ $conversation->messages->first()->direction === 'outbound' ? 'text-blue-500' : 'text-stone-700 dark:text-stone-300' }}">
                                                {{ $conversation->messages->first()->direction === 'outbound' ? 'Bot:' : 'Você:' }}
                                            </span>
                                            {{ Str::limit($conversation->messages->first()->content, 80) }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right ml-4 shrink-0">
                                    <div class="text-xs text-stone-400">{{ $conversation->last_message_at?->diffForHumans() }}</div>
                                    <div class="text-xs text-stone-400 mt-1">{{ $conversation->messages_count }} msgs</div>
                                    <div class="mt-1">
                                        <span class="px-2 py-0.5 text-xs rounded-full {{ $conversation->state === 'initial' ? 'bg-stone-200 text-stone-600' : 'bg-brand-100 text-brand-700' }}">
                                            {{ $conversation->state }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $conversations->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Nova Conversa --}}
<div id="newConversationModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-stone-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" onclick="document.getElementById('newConversationModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-stone-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-brand-700 mb-4">Iniciar Nova Conversa</h3>
                <form method="POST" action="{{ route('admin.bot-messages.start') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="label">Número do WhatsApp</label>
                            <input type="text" name="phone" placeholder="5544999999999" required minlength="10" maxlength="15" class="input-pastel">
                            <p class="text-xs text-stone-400 mt-1">Formato: DDD + Número (com DDD), ex: 5544999999999</p>
                        </div>
                        <div>
                            <label class="label">Mensagem</label>
                            <textarea name="message" rows="4" placeholder="Digite a mensagem que deseja enviar..." required maxlength="4000" class="input-pastel"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('newConversationModal').classList.add('hidden')" class="btn-pastel-secondary">Cancelar</button>
                        <button type="submit" class="btn-pastel-primary">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
