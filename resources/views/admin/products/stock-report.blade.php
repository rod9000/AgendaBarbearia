@extends('layouts.app')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Relatório de Estoque</h2>
        <a href="{{ route('admin.products.index') }}" class="text-sm text-brand-600 hover:text-brand-800">&larr; Voltar para Produtos</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="card-pastel border-l-4 border-brand-400">
                <div class="text-xs font-medium text-brand-600">Total Produtos</div>
                <div class="mt-1 text-2xl font-semibold text-brand-900">{{ $totalProducts }}</div>
            </div>
            <div class="card-pastel border-l-4 border-amber-400">
                <div class="text-xs font-medium text-amber-600">Estoque Baixo</div>
                <div class="mt-1 text-2xl font-semibold text-amber-700">{{ $lowStockCount }}</div>
            </div>
            <div class="card-pastel border-l-4 border-rose-400">
                <div class="text-xs font-medium text-rose-600">Fora de Estoque</div>
                <div class="mt-1 text-2xl font-semibold text-rose-700">{{ $outOfStockCount }}</div>
            </div>
            <div class="card-pastel border-l-4 border-violet-400">
                <div class="text-xs font-medium text-violet-600">Vencendo em 30 dias</div>
                <div class="mt-1 text-2xl font-semibold text-violet-700">{{ $expiringCount }}</div>
            </div>
            <div class="card-pastel border-l-4 border-emerald-400">
                <div class="text-xs font-medium text-emerald-600">Valor em Estoque</div>
                <div class="mt-1 text-2xl font-semibold text-emerald-700">R$ {{ number_format($totalStockValue, 2, ',', '.') }}</div>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 pb-4 border-b border-brand-100">
            <div>
                <label class="text-xs font-medium text-brand-600">Filtro</label>
                <select name="filter" onchange="this.form.submit()" class="input-pastel text-sm">
                    <option value="all" @selected($filter == 'all')>Todos</option>
                    <option value="low_stock" @selected($filter == 'low_stock')>Estoque Baixo</option>
                    <option value="out_of_stock" @selected($filter == 'out_of_stock')>Fora de Estoque</option>
                    <option value="expiring" @selected($filter == 'expiring')>Vencendo em 30 dias</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-brand-600">Fornecedor</label>
                <select name="supplier" onchange="this.form.submit()" class="input-pastel text-sm">
                    <option value="">Todos</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s }}" @selected($supplier == $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <a href="{{ route('admin.products.stock-report') }}" class="text-xs text-brand-500 hover:text-brand-700">Limpar filtros</a>
            </div>
        </form>

        <div class="card-pastel p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-50/50 dark:bg-stone-700">
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Produto</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Marca</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Qtd</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Mínimo</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Status</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Validade</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Fornecedor</th>
                            <th class="px-2 py-2 text-right font-semibold text-brand-700 dark:text-brand-300">Custo Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                            <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $p->name }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $p->brand ?? '—' }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $p->quantity }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $p->min_stock ?? '—' }}</td>
                            <td class="px-2 py-2">
                                @if($p->isOutOfStock())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Fora</span>
                                @elseif($p->isLowStock())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Baixo</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">OK</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">
                                @if($p->expiry_date)
                                    @php $diff = now()->diffInDays($p->expiry_date, false); @endphp
                                    <span class="{{ $diff <= 30 ? 'text-rose-600 font-medium' : 'text-stone-600' }}">{{ $p->expiry_date->format('d/m/Y') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $p->supplier ?? '—' }}</td>
                            <td class="px-2 py-2 text-right font-semibold text-gray-800 dark:text-stone-200">
                                R$ {{ number_format($p->quantity * $p->purchase_price, 2, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-brand-400">Nenhum produto encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
