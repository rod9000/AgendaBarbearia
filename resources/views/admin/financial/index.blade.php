@extends('layouts.app')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Financeiro</h2>
        <form method="GET" class="flex items-center gap-2">
            <select name="method" onchange="this.form.submit()" class="input-pastel text-sm">
                <option value="">Todas formas</option>
                <option value="dinheiro" @selected(request('method') === 'dinheiro')>Dinheiro</option>
                <option value="cartao" @selected(request('method') === 'cartao')>Cartão</option>
                <option value="pix" @selected(request('method') === 'pix')>PIX</option>
            </select>
            <select name="period" onchange="this.form.submit()" class="input-pastel text-sm">
                <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Hoje</option>
                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Esta Semana</option>
                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Este Mês</option>
                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Este Ano</option>
            </select>
        </form>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card-pastel border-l-4 border-emerald-400">
                <div class="text-sm font-medium text-emerald-600">Receita do Período</div>
                <div class="mt-1 text-3xl font-semibold text-emerald-700">R$ {{ number_format($totalReceipts, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-red-400">
                <div class="text-sm font-medium text-red-600">Custo de Insumos</div>
                <div class="mt-1 text-3xl font-semibold text-red-700">R$ {{ number_format($productCost, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-blue-400">
                <div class="text-sm font-medium text-blue-600">Lucro Líquido</div>
                <div class="mt-1 text-3xl font-semibold text-blue-700">R$ {{ number_format($profit, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-amber-400">
                <div class="text-sm font-medium text-amber-600">A Receber (pendentes)</div>
                <div class="mt-1 text-3xl font-semibold text-amber-700">R$ {{ number_format($totalPending, 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card-pastel">
                <h3 class="text-lg font-semibold text-brand-800 mb-4">Evolução Financeira</h3>
                <canvas id="financialChart" height="200"></canvas>
            </div>
            <div class="card-pastel">
                <h3 class="text-lg font-semibold text-brand-800 mb-4">Por Forma de Pagamento</h3>
                @if($byMethod->count() > 0)
                    <div class="space-y-3">
                        @php $methodLabels = ['dinheiro' => 'Dinheiro', 'cartao' => 'Cartão', 'pix' => 'PIX'] @endphp
                        @php $methodColors = ['dinheiro' => 'bg-emerald-400', 'cartao' => 'bg-blue-400', 'pix' => 'bg-purple-400'] @endphp
                        @php $totalAll = $byMethod->sum('total') @endphp
                        @foreach($byMethod as $m)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium">{{ $methodLabels[$m->method] ?? $m->method }}</span>
                                <span>R$ {{ number_format($m->total, 2, ',', '.') }} ({{ $m->count }} pagamentos)</span>
                            </div>
                            <div class="w-full bg-brand-100 rounded-full h-2.5">
                                @php $pct = $totalAll > 0 ? ($m->total / $totalAll * 100) : 0 @endphp
                                <div class="{{ $methodColors[$m->method] ?? 'bg-brand-400' }} h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-brand-400 dark:text-brand-500 text-center py-8">Nenhum pagamento no período.</p>
                @endif
            </div>
        </div>

        <div class="card-pastel p-0 overflow-hidden">
            <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                <h3 class="font-semibold text-brand-700">Pagamentos Recebidos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-50/50 dark:bg-stone-700">
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Data</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Cliente</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Serviço</th>
                            <th class="px-2 py-2 text-right font-semibold text-brand-700 dark:text-brand-300">Valor</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Forma</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $p->paid_at->format('d/m/Y H:i') }}</td>
                            <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $p->appointment?->customer?->name ?? '—' }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $p->appointment?->service?->name ?? '—' }}</td>
                            <td class="px-2 py-2 font-semibold text-emerald-700 dark:text-emerald-400 text-right">R$ {{ number_format($p->amount, 2, ',', '.') }}</td>
                            <td class="px-2 py-2">
                                @php
                                    $methodLabels = ['dinheiro' => 'Dinheiro', 'cartao' => 'Cartão', 'pix' => 'PIX'];
                                    $methodColors = ['dinheiro' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'cartao' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'pix' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $methodColors[$p->method] ?? 'bg-stone-100 text-stone-700' }}">
                                    {{ $methodLabels[$p->method] ?? $p->method }}
                                </span>
                            </td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $p->registeredBy?->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center">
                            <svg class="w-10 h-10 mx-auto mb-2 text-brand-300 dark:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-brand-400 dark:text-brand-500 text-sm">Nenhum pagamento encontrado.</p>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-brand-100">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var isDark = document.documentElement.classList.contains('dark');
var gridColor = isDark ? '#44403c' : '#f0fdf4';
new Chart(document.getElementById('financialChart'), {
    type: 'bar',
    data: {
        labels: @json(array_column($chartData, 'label')),
        datasets: [{
            label: 'Receita (R$)',
            data: @json(array_column($chartData, 'value')),
            backgroundColor: '#486585',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, grid: { color: gridColor } } }
    }
});
</script>
@endpush