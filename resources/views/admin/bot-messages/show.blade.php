@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bot-messages.index') }}" class="text-stone-500 hover:text-stone-700">&larr; Voltar</a>
            <h2 class="font-semibold text-xl text-brand-800 leading-tight">
                {{ $conversation->customer?->name ?? $conversation->phone }}
            </h2>
        </div>
        <span class="px-3 py-1 text-xs rounded-full {{ $conversation->state === 'initial' ? 'bg-stone-200 text-stone-600' : 'bg-brand-100 text-brand-700' }}">
            Estado: {{ $conversation->state }}
        </span>
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

        {{-- Info da conversa --}}
        <div class="card-pastel">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-stone-500">Telefone</span>
                    <p class="font-medium">{{ $conversation->phone }}</p>
                </div>
                <div>
                    <span class="text-stone-500">Cliente</span>
                    <p class="font-medium">{{ $conversation->customer?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-stone-500">Mensagens</span>
                    <p class="font-medium">{{ $conversation->messages_count }}</p>
                </div>
                <div>
                    <span class="text-stone-500">Última msg</span>
                    <p class="font-medium">{{ $conversation->last_message_at?->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            @if($conversation->context)
            <div class="mt-3 pt-3 border-t border-stone-200 dark:border-stone-700">
                <span class="text-stone-500 text-xs">Contexto:</span>
                <pre class="text-xs text-stone-600 dark:text-stone-400 mt-1">{{ json_encode($conversation->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
        </div>

        {{-- Mensagens --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Histórico de Mensagens</h3>

            @if($conversation->messages->isEmpty())
                <p class="text-stone-500 text-sm">Nenhuma mensagem registrada.</p>
            @else
                <div class="space-y-3 max-h-[500px] overflow-y-auto mb-4">
                    @foreach($conversation->messages as $message)
                        <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[75%] p-3 rounded-2xl text-sm {{ $message->direction === 'outbound'
                                ? 'bg-emerald-500 text-white rounded-br-sm'
                                : 'bg-blue-600 text-white rounded-bl-sm' }}">
                                <p class="whitespace-pre-line">{{ $message->content }}</p>
                                <p class="text-xs mt-1 {{ $message->direction === 'outbound' ? 'text-emerald-200' : 'text-blue-200' }}">
                                    {{ $message->created_at->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Enviar mensagem --}}
            <div class="pt-4 border-t border-stone-200 dark:border-stone-700">
                <form method="POST" action="{{ route('admin.bot-messages.send', $conversation) }}" class="flex gap-3">
                    @csrf
                    <input type="text" name="message" placeholder="Digite sua mensagem..." required maxlength="4000"
                        class="input-pastel flex-1" autofocus>
                    <button type="submit" class="btn-pastel-primary whitespace-nowrap">
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Enviar
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.querySelector('.overflow-y-auto');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});
</script>
@endsection
