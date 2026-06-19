@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Fidelidade</h2>
        <a href="{{ route('admin.loyalty.create') }}" class="btn-pastel-primary">+ Nova Recompensa</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="card-pastel border-l-4 border-brand-400">
                <div class="text-xs font-medium text-brand-600">Clientes com Pontos</div>
                <div class="mt-1 text-2xl font-semibold text-brand-900">{{ $totalCustomersWithPoints }}</div>
            </div>
            <div class="card-pastel border-l-4 border-amber-400">
                <div class="text-xs font-medium text-amber-600">Total de Pontos</div>
                <div class="mt-1 text-2xl font-semibold text-amber-700">{{ number_format($totalPointsGiven, 0, ',', '.') }}</div>
            </div>
            <div class="card-pastel border-l-4 border-emerald-400">
                <div class="text-xs font-medium text-emerald-600">Resgates</div>
                <div class="mt-1 text-2xl font-semibold text-emerald-700">{{ $totalRedemptions }}</div>
            </div>
            <div class="card-pastel border-l-4 border-violet-400">
                <div class="text-xs font-medium text-violet-600">Recompensas</div>
                <div class="mt-1 text-2xl font-semibold text-violet-700">{{ $rewards->count() }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <div class="card-pastel p-0 overflow-hidden">
                    <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                        <h3 class="font-semibold text-brand-700">Recompensas</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm whitespace-nowrap">
                            <thead>
                                <tr class="bg-brand-50/50 dark:bg-stone-700">
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Nome</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Pontos</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Desconto</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Status</th>
                                    <th class="px-2 py-2 text-right font-semibold text-brand-700 dark:text-brand-300"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rewards as $r)
                                <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                                    <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $r->name }}</td>
                                    <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $r->points_required }}</td>
                                    <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ number_format($r->discount_percent, 0) }}%</td>
                                    <td class="px-2 py-2">
                                        @if($r->active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Ativa</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-stone-500 dark:bg-stone-700 dark:text-stone-400">Inativa</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        <a href="{{ route('admin.loyalty.edit', $r) }}" class="px-2 py-1 text-xs font-medium text-brand-600 hover:text-brand-800">Editar</a>
                                        <form method="POST" action="{{ route('admin.loyalty.destroy', $r) }}" class="inline" onsubmit="return confirm('Excluir recompensa?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-2 py-1 text-xs font-medium text-rose-500 hover:text-rose-700">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-brand-400">Nenhuma recompensa cadastrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <div class="card-pastel p-0 overflow-hidden">
                    <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                        <h3 class="font-semibold text-brand-700">Clientes com Mais Pontos</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm whitespace-nowrap">
                            <thead>
                                <tr class="bg-brand-50/50 dark:bg-stone-700">
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">#</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Cliente</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Visitas</th>
                                    <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Pontos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $i => $c)
                                <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                                    <td class="px-2 py-2 text-stone-400">{{ $i + 1 }}</td>
                                    <td class="px-2 py-2">
                                        <a href="{{ route('admin.customers.show', $c) }}" class="font-medium text-brand-600 hover:text-brand-800">{{ $c->name }}</a>
                                    </td>
                                    <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->total_visits }}</td>
                                    <td class="px-2 py-2 font-semibold text-amber-600">{{ $c->points }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-brand-400">Nenhum cliente com pontos.</td></tr>
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
