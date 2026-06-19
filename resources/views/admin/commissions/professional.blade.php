@extends('layouts.app')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
        <div>
            <h2 class="font-semibold text-xl text-brand-800 leading-tight">Extrato de Comissão</h2>
            <p class="text-sm text-brand-500">{{ $user->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex items-center gap-2">
                <select name="month" onchange="this.form.submit()" class="input-pastel text-sm">
                    @foreach($months as $m => $label)
                        <option value="{{ $m }}" @selected($month == $m)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="year" onchange="this.form.submit()" class="input-pastel text-sm">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.commissions.index') }}" class="text-sm text-brand-600 hover:text-brand-800">&larr; Voltar</a>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card-pastel border-l-4 border-brand-400">
                <div class="text-sm font-medium text-brand-600">Total do Período</div>
                <div class="mt-1 text-3xl font-semibold text-brand-900">R$ {{ number_format($total, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-emerald-400">
                <div class="text-sm font-medium text-emerald-600">Recebido</div>
                <div class="mt-1 text-3xl font-semibold text-emerald-700">R$ {{ number_format($totalPaid, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-amber-400">
                <div class="text-sm font-medium text-amber-600">A Receber</div>
                <div class="mt-1 text-3xl font-semibold text-amber-700">R$ {{ number_format($totalPending, 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="card-pastel p-0 overflow-hidden">
            <div class="p-4 border-b border-brand-100 bg-brand-50/30 flex justify-between items-center">
                <h3 class="font-semibold text-brand-700">Comissões do Período</h3>
                <button onclick="window.print()" class="text-xs font-medium text-brand-600 hover:text-brand-800 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimir / PDF
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-50/50 dark:bg-stone-700">
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Data</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Cliente</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Serviço</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Valor</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $c)
                        <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->created_at->format('d/m/Y') }}</td>
                            <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $c->appointment?->customer?->name ?? '—' }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->appointment?->services?->first()?->name ?? $c->appointment?->service?->name ?? '—' }}</td>
                            <td class="px-2 py-2 font-semibold text-gray-800 dark:text-stone-200">R$ {{ number_format($c->value, 2, ',', '.') }}</td>
                            <td class="px-2 py-2">
                                @if($c->paid)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Pago</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pendente</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-brand-400">Nenhuma comissão neste período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-brand-100 dark:border-stone-700">
                {{ $commissions->links() }}
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: white; }
    nav, header, .card-pastel > .flex, .card-pastel > .border-b:first-child, .btn-pastel-primary, a[href]:not([href^="#"]), form, .p-4.border-t { display: none; }
    .max-w-7xl { max-width: 100%; padding: 0; }
    .py-6 { padding: 0; }
    .card-pastel { border: 1px solid #ccc; box-shadow: none; }
    table { font-size: 10pt; }
    th { background: #f5f5f5 !important; color: #000 !important; }
    td { color: #000 !important; }
}
</style>
@endsection
