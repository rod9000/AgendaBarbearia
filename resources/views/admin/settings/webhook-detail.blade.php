@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.webhook-logs.index') }}" class="text-stone-500 hover:text-stone-700">&larr; Voltar</a>
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Detalhe do Webhook</h2>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Info --}}
        <div class="card-pastel">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-stone-500">Data/Hora</span>
                    <p class="font-medium">{{ $webhook->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                <div>
                    <span class="text-stone-500">Evento</span>
                    <p class="font-medium">{{ $webhook->event ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-stone-500">Telefone</span>
                    <p class="font-medium">{{ $webhook->sender_phone ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-stone-500">Direção</span>
                    <p class="font-medium">{{ $webhook->from_me ? 'Bot (enviada)' : 'Cliente (recebida)' }}</p>
                </div>
            </div>
        </div>

        {{-- Mensagem --}}
        @if($webhook->message_content)
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-3">Mensagem</h3>
            <p class="text-sm whitespace-pre-line bg-stone-50 dark:bg-stone-800 rounded-xl p-4">{{ $webhook->message_content }}</p>
        </div>
        @endif

        {{-- JSON Completo --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-3">Payload Completo (JSON)</h3>
            <pre class="bg-stone-900 text-green-400 rounded-xl p-4 text-xs overflow-x-auto max-h-96 overflow-y-auto">{{ json_encode($webhook->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

    </div>
</div>
@endsection
