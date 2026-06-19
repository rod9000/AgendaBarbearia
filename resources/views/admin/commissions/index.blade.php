@extends('layouts.app')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Comissões</h2>
        <form method="GET" class="flex items-center gap-2">
            <select name="user_id" onchange="this.form.submit()" class="input-pastel text-sm">
                <option value="">Todos os profissionais</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected($userId == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <select name="period" onchange="this.form.submit()" class="input-pastel text-sm">
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card-pastel border-l-4 border-brand-400">
                <div class="text-sm font-medium text-brand-600">Total em Comissões</div>
                <div class="mt-1 text-3xl font-semibold text-brand-900">R$ {{ number_format($totalCommissions, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-emerald-400">
                <div class="text-sm font-medium text-emerald-600">Pago</div>
                <div class="mt-1 text-3xl font-semibold text-emerald-700">R$ {{ number_format($totalPaid, 2, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-amber-400">
                <div class="text-sm font-medium text-amber-600">A Pagar</div>
                <div class="mt-1 text-3xl font-semibold text-amber-700">R$ {{ number_format($totalPending, 2, ',', '.') }}</div>
            </div>
        </div>

        @if($byUser->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($byUser as $b)
            <div class="card-pastel">
                <div class="font-semibold text-brand-700">{{ $b->user->name }}</div>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-stone-500">Total:</span>
                    <span class="font-medium">R$ {{ number_format($b->total, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-stone-500">Pago:</span>
                    <span class="font-medium text-emerald-600">R$ {{ number_format($b->paid_total, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-stone-500">A pagar:</span>
                    <span class="font-medium text-amber-600">R$ {{ number_format($b->total - $b->paid_total, 2, ',', '.') }}</span>
                </div>
                <div class="mt-2 w-full bg-brand-100 rounded-full h-2">
                    @php $pct = $b->total > 0 ? ($b->paid_total / $b->total * 100) : 0 @endphp
                    <div class="bg-emerald-400 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
                <div class="mt-2 text-right">
                    <a href="{{ route('admin.commissions.professional', $b->user) }}" class="text-xs font-medium text-brand-600 hover:text-brand-800">Extrato &rarr;</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="card-pastel p-0 overflow-hidden">
            <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                <h3 class="font-semibold text-brand-700">Comissões Geradas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-50/50 dark:bg-stone-700">
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Data</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Profissional</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Cliente</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Serviço</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Valor</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Status</th>
                            <th class="px-2 py-2 text-right font-semibold text-brand-700 dark:text-brand-300"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $c)
                        <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->created_at->format('d/m/Y') }}</td>
                            <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $c->user->name }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->appointment?->customer?->name ?? '—' }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->appointment?->service?->name ?? '—' }}</td>
                            <td class="px-2 py-2 font-semibold text-gray-800 dark:text-stone-200">R$ {{ number_format($c->value, 2, ',', '.') }}</td>
                            <td class="px-2 py-2">
                                @if($c->paid)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Pago</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pendente</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right">
                                @if(!$c->paid)
                                <form method="POST" action="{{ route('admin.commissions.mark-paid', $c) }}" class="inline" onsubmit="return confirm('Marcar comissão como paga?')">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs font-medium text-emerald-600 hover:text-emerald-800">Marcar Pago</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center">
                            <svg class="w-10 h-10 mx-auto mb-2 text-brand-300 dark:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="text-brand-400 dark:text-brand-500 text-sm">Nenhuma comissão encontrada.</p>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-brand-100">
                {{ $commissions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection