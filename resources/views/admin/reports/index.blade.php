@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Relatórios e Estatísticas</h2>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Filter Bar --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 pb-4 border-b border-brand-100">
            <div>
                <label class="label text-xs">Período</label>
                <select name="period" onchange="toggleCustomRange(this)" class="input-pastel text-sm">
                    <option value="year" @selected($period == 'year')>Últimos 12 meses</option>
                    <option value="quarter" @selected($period == 'quarter')>Últimos 3 meses</option>
                    <option value="month" @selected($period == 'month')>Este mês</option>
                    <option value="last_month" @selected($period == 'last_month')>Mês passado</option>
                    <option value="all" @selected($period == 'all')>Todo período</option>
                    <option value="custom" @selected($period == 'custom')>Personalizado</option>
                </select>
            </div>
            <div id="custom-range" class="{{ $period == 'custom' ? '' : 'hidden' }} flex gap-2">
                <div>
                    <label class="label text-xs">De</label>
                    <input type="date" name="start" value="{{ $startInput ?? '' }}" class="input-pastel text-sm">
                </div>
                <div>
                    <label class="label text-xs">Até</label>
                    <input type="date" name="end" value="{{ $endInput ?? '' }}" class="input-pastel text-sm">
                </div>
            </div>
            <div>
                <label class="label text-xs">Profissional</label>
                <select name="user_id" class="input-pastel text-sm">
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($userId == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label text-xs">Status</label>
                <select name="status" class="input-pastel text-sm">
                    <option value="completed" @selected($statusFilter == 'completed')>Concluído</option>
                    <option value="scheduled" @selected($statusFilter == 'scheduled')>Agendado</option>
                    <option value="confirmed" @selected($statusFilter == 'confirmed')>Confirmado</option>
                    <option value="cancelled" @selected($statusFilter == 'cancelled')>Cancelado</option>
                    <option value="no_show" @selected($statusFilter == 'no_show')>Não Compareceu</option>
                </select>
            </div>
            <button type="submit" class="btn-pastel-primary text-sm">Filtrar</button>
            <a href="{{ route('admin.reports.index') }}" class="btn-pastel-secondary text-sm">Limpar</a>
        </form>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card-pastel">
                <p class="text-xs text-brand-400 uppercase tracking-wider font-semibold">Faturamento Total</p>
                <p class="text-2xl font-bold text-brand-700 mt-1">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
            </div>
            <div class="card-pastel">
                <p class="text-xs text-brand-400 uppercase tracking-wider font-semibold">Atendimentos Concluídos</p>
                <p class="text-2xl font-bold text-brand-700 mt-1">{{ $totalAppointments }}</p>
            </div>
            <div class="card-pastel">
                <p class="text-xs text-brand-400 uppercase tracking-wider font-semibold">Média por Mês</p>
                <p class="text-2xl font-bold text-brand-700 mt-1">{{ $avgPerMonth }}</p>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card-pastel">
                <h3 class="font-semibold text-brand-700 mb-4">Faturamento Mensal</h3>
                <canvas id="revenueChart" height="200"></canvas>
            </div>
            <div class="card-pastel">
                <h3 class="font-semibold text-brand-700 mb-4">Serviços Mais Realizados</h3>
                <canvas id="topServicesChart" height="200"></canvas>
            </div>
        </div>

        {{-- Appointments Evolution --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Evolução de Atendimentos</h3>
            <canvas id="appointmentsChart" height="150"></canvas>
        </div>

        {{-- Tables Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card-pastel">
                <h3 class="font-semibold text-brand-700 mb-4">Comissões por Barbeiro</h3>
                @if($commissions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-brand-400 text-xs uppercase tracking-wider">
                                <th class="pb-2 font-semibold">Barbeiro</th>
                                <th class="pb-2 font-semibold">Comissões</th>
                                <th class="pb-2 font-semibold">Total</th>
                                <th class="pb-2 font-semibold">Pago</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-100">
                            @foreach($commissions as $c)
                            <tr>
                                <td class="py-2 text-stone-700">{{ $c->user->name ?? '—' }}</td>
                                <td class="py-2 text-stone-700">{{ $c->count }}</td>
                                <td class="py-2 text-stone-700 font-medium">R$ {{ number_format($c->total, 2, ',', '.') }}</td>
                                <td class="py-2 text-emerald-600 font-medium">R$ {{ number_format($c->paid_total, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-sm text-stone-400 dark:text-stone-500">Nenhuma comissão registrada.</p>
                @endif
            </div>

            <div class="card-pastel">
                <h3 class="font-semibold text-brand-700 mb-4">Formas de Pagamento</h3>
                @if($paymentMethods->count() > 0)
                <div class="space-y-4">
                    @php
                        $methodLabels = ['dinheiro' => 'Dinheiro', 'cartao' => 'Cartão', 'pix' => 'PIX'];
                        $methodColors = ['dinheiro' => '#22c55e', 'cartao' => '#3b82f6', 'pix' => '#a855f7'];
                        $totalPaid = $paymentMethods->sum('total');
                    @endphp
                    @foreach($paymentMethods as $pm)
                        @php $pct = $totalPaid > 0 ? round(($pm->total / $totalPaid) * 100, 1) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-stone-700 font-medium">{{ $methodLabels[$pm->method] ?? $pm->method }}</span>
                                <span class="text-stone-500">R$ {{ number_format($pm->total, 2, ',', '.') }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-stone-100 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full transition-all" style="width: {{ $pct }}%; background: {{ $methodColors[$pm->method] ?? '#c94f2e' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                    <p class="text-sm text-stone-400 dark:text-stone-500">Nenhum pagamento registrado.</p>
                @endif
            </div>
        </div>

        {{-- Export --}}
        <div class="card-pastel max-w-lg">
            <h3 class="font-semibold text-brand-700 mb-4">Exportar Agendamentos</h3>
            <form method="GET" action="{{ route('admin.reports.export-csv') }}">
                <div class="mb-4">
                    <label class="label">Período</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="start" value="{{ request('start', now()->startOfMonth()->format('Y-m-d')) }}" class="input-pastel">
                        <input type="date" name="end" value="{{ request('end', now()->format('Y-m-d')) }}" class="input-pastel">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="label">Status</label>
                    <select name="status" class="input-pastel">
                        <option value="">Todos</option>
                        <option value="completed">Concluído</option>
                        <option value="cancelled">Cancelado</option>
                        <option value="scheduled">Agendado</option>
                        <option value="confirmed">Confirmado</option>
                        <option value="no_show">Não Compareceu</option>
                    </select>
                </div>
                <button type="submit" class="btn-pastel-primary">Exportar CSV</button>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleCustomRange(sel) {
    var el = document.getElementById('custom-range');
    if (sel) el.classList.toggle('hidden', sel.value !== 'custom');
}
document.addEventListener('DOMContentLoaded', function() {
    var brandColor = '#7B8564';
    var brandLight = '#E8EDDB';

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: @json(array_column($monthlyRevenue, 'label')),
            datasets: [{
                label: 'Faturamento',
                data: @json(array_column($monthlyRevenue, 'value')),
                backgroundColor: brandLight,
                borderColor: brandColor,
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(v) { return 'R$ ' + v.toFixed(0).replace('.', ','); } }
                }
            }
        }
    });

    new Chart(document.getElementById('topServicesChart'), {
        type: 'doughnut',
        data: {
            labels: @json($topServices->pluck('name')),
            datasets: [{
                data: @json($topServices->pluck('total')),
                backgroundColor: ['#7B8564','#959E7D','#AFB796','#BAC893','#D1DAB7','#616C4B','#475332','#2D3A19','#E8EDDB','#F4F7EE'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } }
            }
        }
    });

    new Chart(document.getElementById('appointmentsChart'), {
        type: 'line',
        data: {
            labels: @json(array_column($monthlyAppointments, 'label')),
            datasets: [{
                label: 'Atendimentos',
                data: @json(array_column($monthlyAppointments, 'value')),
                borderColor: brandColor,
                backgroundColor: brandLight,
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: brandColor,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>
@endpush
