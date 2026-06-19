@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Produtos</h2>
        <a href="{{ route('admin.products.create') }}" class="btn-pastel-primary">+ Novo Produto</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(session('error'))
            <div class="bg-rose-100 border border-rose-300 text-rose-700 px-4 py-3 rounded-xl relative mb-4">{{ session('error') }}</div>
        @endif
        <div class="card-pastel p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-50/50 dark:bg-stone-700">
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Nome</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Marca</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Preço</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Estoque</th>
                            <th class="px-2 py-2 text-right font-semibold text-brand-700 dark:text-brand-300"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                            <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $p->name }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $p->brand ?? '—' }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">R$ {{ number_format($p->purchase_price, 2, ',', '.') }}</td>
                            <td class="px-2 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($p->isOutOfStock()) bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400
                                    @elseif($p->isLowStock()) bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                    @else bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                    @endif">
                                    {{ $p->quantity }}
                                </span>
                            </td>
                            <td class="px-2 py-2 text-right">
                                <a href="{{ route('admin.products.show', $p) }}" class="inline-block px-2 py-1 text-xs font-medium text-brand-600 hover:text-brand-800 dark:text-brand-400">Ver</a>
                                <a href="{{ route('admin.products.edit', $p) }}" class="inline-block px-2 py-1 text-xs font-medium text-brand-600 hover:text-brand-800 dark:text-brand-400">Editar</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="inline" onsubmit="return confirm('Excluir produto?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-xs font-medium text-rose-500 hover:text-rose-700">Excluir</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-brand-400">Nenhum produto cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-2 py-2 sm:px-4 sm:py-3 border-t border-brand-100 dark:border-stone-700">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
