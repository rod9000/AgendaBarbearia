@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Pontos: {{ $customer->name }}</h2>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn-pastel-secondary">Voltar ao Cliente</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card-pastel text-center border-l-4 border-amber-400">
                <div class="text-3xl font-bold text-amber-600">{{ $customer->points }}</div>
                <div class="text-sm text-stone-500 mt-1">Pontos Disponíveis</div>
            </div>
            <div class="card-pastel text-center border-l-4 border-brand-400">
                <div class="text-3xl font-bold text-brand-600">{{ $customer->total_visits }}</div>
                <div class="text-sm text-stone-500 mt-1">Total de Visitas</div>
            </div>
            <div class="card-pastel text-center border-l-4 border-emerald-400">
                <div class="text-3xl font-bold text-emerald-600">{{ $customer->redemptions->count() }}</div>
                <div class="text-sm text-stone-500 mt-1">Recompensas Resgatadas</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <div class="card-pastel p-0 overflow-hidden">
                    <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                        <h3 class="font-semibold text-brand-700">Recompensas Disponíveis</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm whitespace-nowrap">
                            <thead>
                                <tr class="bg-brand-50/50 dark:bg-stone-700">
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Recompensa</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Desconto</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Pontos</th>
                                    <th class="px-2 py-2 text-right font-semibold text-brand-700 dark:text-brand-300"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rewards as $r)
                                <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50 {{ $customer->points < $r->points_required ? 'opacity-50' : '' }}">
                                    <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">
                                        {{ $r->name }}
                                        @if($r->description)
                                            <p class="text-xs text-stone-400">{{ $r->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ number_format($r->discount_percent, 0) }}%</td>
                                    <td class="px-2 py-2">
                                        <span class="font-semibold {{ $customer->points >= $r->points_required ? 'text-emerald-600' : 'text-rose-500' }}">{{ $r->points_required }}</span>
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        @if($customer->points >= $r->points_required)
                                        <form method="POST" action="{{ route('admin.loyalty.redeem', $customer) }}" class="inline" onsubmit="return confirm('Resgatar {{ $r->name }} por {{ $r->points_required }} pontos?')">
                                            @csrf
                                            <input type="hidden" name="reward_id" value="{{ $r->id }}">
                                            <button type="submit" class="px-2 py-1 text-xs font-medium text-brand-600 hover:text-brand-800">Resgatar</button>
                                        </form>
                                        @else
                                        <span class="text-xs text-stone-400">Faltam {{ $r->points_required - $customer->points }} pts</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-brand-400">Nenhuma recompensa disponível.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <div class="card-pastel p-0 overflow-hidden">
                    <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                        <h3 class="font-semibold text-brand-700">Histórico de Resgates</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm whitespace-nowrap">
                            <thead>
                                <tr class="bg-brand-50/50 dark:bg-stone-700">
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Data</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Recompensa</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Pontos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->redemptions->sortByDesc('created_at') as $rd)
                                <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                                    <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $rd->created_at->format('d/m/Y') }}</td>
                                    <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $rd->reward->name ?? '—' }}</td>
                                    <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $rd->points_spent }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="px-4 py-8 text-center text-brand-400">Nenhum resgate realizado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
