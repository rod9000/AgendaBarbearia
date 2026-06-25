@extends('layouts.app')

@section('header')
    @if (session('success'))
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded-xl relative mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Dashboard</h2>
        <form method="GET" class="flex items-center gap-2">
            <select name="period" onchange="this.form.submit()" class="input-pastel text-sm">
                <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Hoje</option>
                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Esta Semana</option>
                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Este Mês</option>
            </select>
        </form>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- Métricas principais --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="card-pastel border-l-4 border-brand-400">
                <div class="flex items-center gap-2 text-xs font-medium text-brand-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Concluídos
                </div>
                <div class="mt-1 text-2xl font-semibold text-brand-900 dark:text-brand-100">{{ $completedCount }}</div>
            </div>
            <div class="card-pastel border-l-4 border-orange-400">
                <div class="flex items-center gap-2 text-xs font-medium text-orange-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pendentes
                </div>
                <div class="mt-1 text-2xl font-semibold text-orange-700 dark:text-orange-300">{{ $pendingCount }}</div>
            </div>
            <div class="card-pastel border-l-4 border-red-400">
                <div class="flex items-center gap-2 text-xs font-medium text-red-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancelados
                </div>
                <div class="mt-1 text-2xl font-semibold text-red-700 dark:text-red-300">{{ $cancelledCount }}</div>
            </div>
            <div class="card-pastel border-l-4 border-emerald-400">
                <div class="flex items-center gap-2 text-xs font-medium text-emerald-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Faturamento Serviços
                </div>
                <div class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-300">R$ {{ number_format($revenue, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-teal-400">
                <div class="flex items-center gap-2 text-xs font-medium text-teal-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Faturamento Total
                </div>
                <div class="mt-1 text-2xl font-semibold text-teal-700 dark:text-teal-300">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-purple-400">
                <div class="flex items-center gap-2 text-xs font-medium text-purple-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Vendas
                </div>
                <div class="mt-1 text-2xl font-semibold text-purple-700 dark:text-purple-300">R$ {{ number_format($salesTotal, 2, ',', '.') }}</div>
                <div class="text-xs text-brand-500 mt-0.5">{{ $salesCount }} {{ $salesCount == 1 ? 'venda' : 'vendas' }}</div>
            </div>
            <div class="card-pastel border-l-4 border-violet-400">
                <div class="flex items-center gap-2 text-xs font-medium text-violet-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Ticket Médio
                </div>
                <div class="mt-1 text-2xl font-semibold text-violet-700 dark:text-violet-300">R$ {{ number_format($avgTicket, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-sky-400">
                <div class="flex items-center gap-2 text-xs font-medium text-sky-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Clientes Atend.
                </div>
                <div class="mt-1 text-2xl font-semibold text-sky-700 dark:text-sky-300">{{ $uniqueCustomers }}</div>
            </div>
        </div>

        {{-- Indicadores --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="card-pastel">
                <div class="text-xs font-medium text-brand-600 uppercase tracking-wider">Vs. Período Anterior</div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-bold {{ $revenueChange >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $revenueChange >= 0 ? '+' : '' }}{{ $revenueChange }}%
                    </span>
                    <span class="text-xs text-brand-400">receita</span>
                    @if($revenueChange >= 0)
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    @else
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    @endif
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-sm font-medium {{ $completedChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                        {{ $completedChange >= 0 ? '+' : '' }}{{ $completedChange }}%
                    </span>
                    <span class="text-xs text-brand-400">atendimentos</span>
                </div>
            </div>

            <div class="card-pastel">
                <div class="text-xs font-medium uppercase tracking-wider {{ $cancellationRate > 20 ? 'text-red-600' : 'text-brand-600' }}">Cancelamento</div>
                <div class="mt-2 text-2xl font-bold {{ $cancellationRate > 20 ? 'text-red-600' : 'text-amber-600' }}">
                    {{ $cancellationRate }}%
                </div>
                <div class="text-xs text-brand-400 mt-1">
                    {{ $cancelledCount }} cancel. de {{ $totalFinished }} {{ $totalFinished == 1 ? 'agendamento' : 'agendamentos' }}
                </div>
            </div>

            <div class="card-pastel">
                <div class="text-xs font-medium uppercase tracking-wider {{ $conversionRate < 50 ? 'text-red-600' : 'text-emerald-600' }}">Conversão</div>
                <div class="mt-2 text-2xl font-bold {{ $conversionRate >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $conversionRate }}%
                </div>
                <div class="text-xs text-brand-400 mt-1">
                    {{ $completedCount }} concl. de {{ $totalInPeriod }} {{ $totalInPeriod == 1 ? 'total' : 'totais' }}
                </div>
            </div>

            <div class="card-pastel">
                <div class="text-xs font-medium text-brand-600 uppercase tracking-wider">Dia + Movimentado</div>
                <div class="mt-2 text-xl font-bold text-brand-800">{{ $busiestDayName }}</div>
                <div class="text-xs text-brand-400 mt-1">{{ $busiestDayCount }} {{ $busiestDayCount == 1 ? 'atendimento' : 'atendimentos' }}</div>
            </div>
        </div>

        {{-- Vendas --}}
        <div class="card-pastel mb-6 border-l-4 border-purple-400">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs font-medium text-purple-600 uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        Vendas
                    </div>
                    <div class="mt-1 text-2xl font-semibold text-purple-700 dark:text-purple-300">
                        R$ {{ number_format($salesTotal, 2, ',', '.') }}
                    </div>
                    <div class="text-sm text-brand-500">{{ $salesCount }} {{ $salesCount == 1 ? 'venda' : 'vendas' }} no período</div>
                </div>
                <a href="{{ route('admin.sales.index') }}" class="btn-pastel-secondary text-sm">Ver Vendas</a>
            </div>
        </div>

        {{-- Receita Dia / Semana / Mês --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
            <div class="card-pastel text-center">
                <div class="text-xs font-semibold uppercase tracking-wider text-brand-500">Receita Hoje</div>
                <div class="mt-2 text-2xl font-bold text-brand-800">R$ {{ number_format($revenueDay, 2, ',', '.') }}</div>
                <div class="mt-1 text-sm text-brand-500">{{ $countDay }} concluído(s)</div>
            </div>
            <div class="card-pastel text-center">
                <div class="text-xs font-semibold uppercase tracking-wider text-brand-500">Receita Semana</div>
                <div class="mt-2 text-2xl font-bold text-brand-800">R$ {{ number_format($revenueWeek, 2, ',', '.') }}</div>
                <div class="mt-1 text-sm text-brand-500">{{ $countWeek }} concluído(s)</div>
            </div>
            <div class="card-pastel text-center">
                <div class="text-xs font-semibold uppercase tracking-wider text-brand-500">Receita Mês</div>
                <div class="mt-2 text-2xl font-bold text-brand-800">R$ {{ number_format($revenueMonth, 2, ',', '.') }}</div>
                <div class="mt-1 text-sm text-brand-500">{{ $countMonth }} concluído(s)</div>
            </div>
        </div>

        {{-- Gráficos lado a lado --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="card-pastel">
                <h3 class="text-lg font-semibold text-brand-800 mb-4">Faturamento - {{ ucfirst($period) }}</h3>
                <canvas id="revenueChart" height="200"></canvas>
            </div>
            <div class="card-pastel">
                <h3 class="text-lg font-semibold text-brand-800 mb-4">Serviços Mais Realizados</h3>
                @if($topServices->count() > 0)
                    <canvas id="servicesChart" height="200"></canvas>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto mb-3 text-brand-300 dark:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-brand-400 dark:text-brand-500 text-sm">Nenhum serviço realizado no período.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Atendimentos Hoje + Próximos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="card-pastel">
                <h3 class="text-lg font-semibold text-brand-800 mb-4">Atendimentos de Hoje</h3>
                @if($todayAppointments->count() > 0)
                    <ul class="divide-y divide-brand-100">
                        @foreach($todayAppointments as $app)
                        <li class="py-3 flex justify-between items-center">
                            <div class="min-w-0 flex-1">
                                <span class="font-semibold text-brand-700">{{ $app->start->format('H:i') }}</span>
                                <span class="ml-2 text-stone-700">{{ $app->customer->name }}</span>
                                <span class="text-sm text-brand-500 ml-2">
                                    {{ $app->services->pluck('name')->implode(', ') ?: $app->service->name }}
                                </span>
                            </div>
                            <x-status-badge :status="$app->status" class="shrink-0 ml-2" />
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto mb-3 text-brand-300 dark:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-brand-400 dark:text-brand-500 text-sm">Nenhum atendimento hoje.</p>
                    </div>
                @endif
            </div>

            <div class="card-pastel">
                <h3 class="text-lg font-semibold text-brand-800 mb-4">Próximos Agendamentos</h3>
                @if($upcomingAppointments->count() > 0)
                    <ul class="divide-y divide-brand-100">
                        @foreach($upcomingAppointments as $app)
                        <li class="py-3 flex justify-between items-center">
                            <div class="min-w-0 flex-1">
                                <span class="font-semibold text-brand-700">{{ $app->start->format('d/m H:i') }}</span>
                                <span class="ml-2 text-stone-700">{{ $app->customer->name }}</span>
                                <span class="text-sm text-brand-500 ml-2">
                                    {{ $app->services->pluck('name')->implode(', ') ?: $app->service->name }}
                                </span>
                                <div class="text-xs text-brand-400 mt-0.5 ml-1">{{ $app->user->name }}</div>
                            </div>
                            <x-status-badge :status="$app->status" class="shrink-0 ml-2" />
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto mb-3 text-brand-300 dark:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-brand-400 dark:text-brand-500 text-sm">Nenhum agendamento futuro.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Comissões Pendentes + Aniversariantes (admin) --}}
        @if($isAdmin)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="card-pastel">
                <h3 class="text-lg font-semibold text-brand-800 mb-4">Comissões Pendentes</h3>
                <div class="text-3xl font-bold text-amber-600 mb-2">
                    R$ {{ number_format($pendingCommissions, 2, ',', '.') }}
                </div>
                <a href="{{ route('admin.commissions.index') }}" class="text-sm text-brand-500 hover:text-brand-700 underline">
                    Ver comissões →
                </a>
            </div>

            <div class="card-pastel">
                <h3 class="text-lg font-semibold text-brand-800 mb-4">
                    Aniversariantes do Mês
                    <span class="text-sm font-normal text-brand-400">({{ $monthBirthdays->count() }})</span>
                </h3>
                @if($monthBirthdays->count() > 0)
                    <ul class="divide-y divide-brand-100">
                        @foreach($monthBirthdays as $cust)
                        <li class="py-2 flex justify-between items-center">
                            <span class="text-stone-700">{{ $cust->name }}</span>
                            <span class="text-sm text-brand-500">
                                {{ $cust->birth_date->format('d/m') }}
                                @if($cust->birth_date->isToday())
                                    <span class="badge-pastel bg-rose-100 text-rose-700 ml-1">Hoje!</span>
                                @endif
                            </span>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4">
                        <svg class="w-10 h-10 mx-auto mb-2 text-brand-300 dark:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.5 1.5 0 003 15.546M12 2v4m0 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        <p class="text-brand-400 dark:text-brand-500 text-sm">Nenhum aniversariante este mês.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Performance por Profissional (admin) --}}
        @if($isAdmin && $profPerformance->count() > 0)
        <div class="card-pastel mb-6">
            <h3 class="text-lg font-semibold text-brand-800 mb-4">Atendimentos por Profissional</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <canvas id="profChart" height="180"></canvas>
                </div>
                <div class="space-y-3">
                    @foreach($profPerformance as $prof)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-stone-700">{{ $prof->name }}</span>
                            <span class="font-semibold text-brand-700">{{ $prof->appointments_count }} atend.</span>
                        </div>
                        <div class="w-full bg-brand-100 rounded-full h-2.5">
                            @php $pct = $profPerformance->max('appointments_count') > 0 ? ($prof->appointments_count / $profPerformance->max('appointments_count')) * 100 : 0; @endphp
                            <div class="bg-gradient-to-r from-brand-400 to-brand-600 h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Receita por Profissional (admin) --}}
        @if($isAdmin && $profRevenue->count() > 0)
        <div class="card-pastel mb-6">
            <h3 class="text-lg font-semibold text-brand-800 mb-4">Receita por Profissional</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-brand-400 text-xs uppercase tracking-wider">
                            <th class="pb-2 font-semibold">Profissional</th>
                            <th class="pb-2 font-semibold">Atend.</th>
                            <th class="pb-2 font-semibold">Receita</th>
                            <th class="pb-2 font-semibold">Ticket Médio</th>
                            <th class="pb-2 font-semibold">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-100">
                        @foreach($profRevenue as $pr)
                        <tr>
                            <td class="py-2 text-stone-700 font-medium">{{ $pr->name }}</td>
                            <td class="py-2 text-stone-700">{{ $pr->total_appointments }}</td>
                            <td class="py-2 text-emerald-600 font-medium">R$ {{ number_format($pr->total_revenue, 2, ',', '.') }}</td>
                            <td class="py-2 text-stone-700">
                                R$ {{ number_format($pr->total_appointments > 0 ? $pr->total_revenue / $pr->total_appointments : 0, 2, ',', '.') }}
                            </td>
                            <td class="py-2">
                                @php $pct = $revenue > 0 ? ($pr->total_revenue / $revenue) * 100 : 0; @endphp
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-brand-100 rounded-full h-2">
                                        <div class="bg-brand-500 h-2 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs text-brand-500">{{ round($pct) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var isDark = document.documentElement.classList.contains('dark');
var gridColor = isDark ? '#334E68' : '#D9E2EC';
var textColor = isDark ? '#a8a29e' : '#78716c';

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: @json(array_column($chartData, 'label')),
        datasets: [{
            label: 'Faturamento (R$)',
            data: @json(array_column($chartData, 'value')),
            backgroundColor: ['#334E68', '#486585', '#627D98', '#829AB1', '#334E68', '#486585', '#627D98'],
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor } },
            x: { ticks: { color: textColor } }
        }
    }
});

@if($topServices->count() > 0)
new Chart(document.getElementById('servicesChart'), {
    type: 'doughnut',
    data: {
        labels: @json($topServices->pluck('name')),
        datasets: [{
            data: @json($topServices->pluck('total')),
            backgroundColor: [
                '#102A43', '#243B53', '#334E68', '#486585', '#627D98'
            ],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 12, usePointStyle: true, font: { size: 12 }, color: textColor }
            }
        }
    }
});
@endif

@if($isAdmin && $profPerformance->count() > 0)
new Chart(document.getElementById('profChart'), {
    type: 'bar',
    data: {
        labels: @json($profPerformance->pluck('name')),
        datasets: [{
            label: 'Atendimentos',
            data: @json($profPerformance->pluck('appointments_count')),
            backgroundColor: ['#102A43', '#243B53', '#334E68', '#486585', '#627D98', '#829AB1'],
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, grid: { color: gridColor }, ticks: { stepSize: 1, color: textColor } },
            y: { grid: { display: false }, ticks: { color: textColor } }
        }
    }
});
@endif
</script>
@endpush
