@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Webhooks Recebidos ({{ number_format($total) }})</h2>
        <a href="{{ route('admin.settings.evolution') }}" class="btn-pastel-secondary">Evolution API</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Filtros --}}
        <div class="card-pastel">
            <form method="GET" action="{{ route('admin.webhook-logs.index') }}" class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="label">Evento</label>
                    <select name="event" class="input-pastel">
                        <option value="">Todos</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>{{ $event }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="label">Telefone</label>
                    <input type="text" name="phone" value="{{ request('phone') }}" placeholder="Buscar por telefone..." class="input-pastel">
                </div>
                <button type="submit" class="btn-pastel-primary">Filtrar</button>
                @if(request('event') || request('phone'))
                    <a href="{{ route('admin.webhook-logs.index') }}" class="btn-pastel-secondary">Limpar</a>
                @endif
            </form>
        </div>

        {{-- Lista --}}
        <div class="card-pastel p-0 overflow-hidden">
            <table class="table-pastel w-full">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Evento</th>
                        <th>Telefone</th>
                        <th>De</th>
                        <th>Mensagem</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhooks as $webhook)
                    <tr class="{{ $webhook->from_me ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">
                        <td class="text-xs text-stone-500">{{ $webhook->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ str_contains($webhook->event ?? '', 'upsert') ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-600' }}">
                                {{ $webhook->event ?? '—' }}
                            </span>
                        </td>
                        <td class="text-sm">{{ $webhook->sender_phone ?? '—' }}</td>
                        <td class="text-sm">
                            @if($webhook->from_me)
                                <span class="text-blue-600 font-medium">Bot</span>
                            @else
                                <span class="text-stone-600">Cliente</span>
                            @endif
                        </td>
                        <td class="text-sm max-w-xs truncate">{{ $webhook->message_content ?? '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.webhook-logs.show', $webhook) }}" class="text-brand-600 hover:text-brand-800 text-xs font-medium">Ver JSON</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-stone-400">Nenhum webhook registrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4 border-t border-stone-200 dark:border-stone-700">
                {{ $webhooks->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
