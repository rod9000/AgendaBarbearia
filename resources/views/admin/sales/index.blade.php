@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Vendas</h2>
        <a href="{{ route('admin.sales.create') }}" class="btn-pastel-primary">+ Nova Venda</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded-xl relative mb-4">{{ session('success') }}</div>
        @endif

        <div class="card-pastel p-0">
            <div class="p-4 border-b border-brand-100 dark:border-stone-700 bg-brand-50/30 dark:bg-stone-800">
                <form method="GET" class="flex flex-col sm:flex-row gap-2">
                    <input type="text" name="search" placeholder="Buscar por cliente..." value="{{ request('search') }}" class="input-pastel flex-1">
                    <button type="submit" class="btn-pastel-secondary">Buscar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-50/50 dark:bg-stone-700">
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">#</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Cliente</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Valor</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Pagamento</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Data</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Vendedor</th>
                            <th class="px-2 py-2 text-right font-semibold text-brand-700 dark:text-brand-300"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $s)
                        <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                            <td class="px-2 py-2 font-medium text-brand-600 dark:text-brand-400">#{{ $s->id }}</td>
                            <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $s->customer->name }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">R$ {{ number_format($s->total, 2, ',', '.') }}</td>
                            <td class="px-2 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($s->payment_method == 'dinheiro') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                    @elseif($s->payment_method == 'pix') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                    @elseif($s->payment_method == 'debito') bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400
                                    @else bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                    @endif">
                                    {{ ucfirst($s->payment_method) }}
                                </span>
                            </td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $s->user?->name ?? '—' }}</td>
                            <td class="px-2 py-2 text-right">
                                <a href="{{ route('admin.sales.show', $s) }}" class="inline-block px-2 py-1 text-xs font-medium text-brand-600 hover:text-brand-800 dark:text-brand-400">Ver</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-brand-400">Nenhuma venda registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-2 py-2 sm:px-4 sm:py-3 border-t border-brand-100 dark:border-stone-700">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
