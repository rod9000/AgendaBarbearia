@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Venda #{{ $sale->id }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.sales.index') }}" class="btn-pastel-secondary">Voltar</a>
            <button onclick="window.print()" class="btn-pastel-primary">Imprimir</button>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded-xl relative mb-4">{{ session('success') }}</div>
        @endif

        <div class="card-pastel p-0">
            <div class="p-6 border-b border-brand-100 dark:border-stone-700">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold text-brand-800 dark:text-brand-200">Recibo</h3>
                        <p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Venda #{{ $sale->id }}</p>
                        <p class="text-sm text-stone-500 dark:text-stone-400">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($sale->payment_method == 'dinheiro') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                        @elseif($sale->payment_method == 'pix') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                        @elseif($sale->payment_method == 'debito') bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400
                        @else bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                        @endif">
                        {{ ucfirst($sale->payment_method) }}
                    </span>
                </div>
            </div>

            <div class="p-6 border-b border-brand-100 dark:border-stone-700">
                <h4 class="text-sm font-semibold text-stone-600 dark:text-stone-400 uppercase tracking-wider mb-2">Cliente</h4>
                <p class="text-base font-medium text-gray-800 dark:text-stone-200">{{ $sale->customer->name }}</p>
                <p class="text-sm text-stone-500 dark:text-stone-400">{{ $sale->customer->phone }}</p>
            </div>

            <div class="p-6 border-b border-brand-100 dark:border-stone-700">
                <h4 class="text-sm font-semibold text-stone-600 dark:text-stone-400 uppercase tracking-wider mb-3">Itens</h4>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-stone-500 dark:text-stone-400 border-b border-brand-100 dark:border-stone-700">
                            <th class="pb-2 font-medium">Produto</th>
                            <th class="pb-2 font-medium text-center">Qtd</th>
                            <th class="pb-2 font-medium text-right">Preço Un.</th>
                            <th class="pb-2 font-medium text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->products as $p)
                        <tr class="border-b border-brand-50 dark:border-stone-800">
                            <td class="py-2 text-gray-800 dark:text-stone-200">{{ $p->name }}</td>
                            <td class="py-2 text-center text-stone-600 dark:text-stone-400">{{ $p->pivot->quantity }}</td>
                            <td class="py-2 text-right text-stone-600 dark:text-stone-400">R$ {{ number_format($p->pivot->unit_price, 2, ',', '.') }}</td>
                            <td class="py-2 text-right font-medium text-gray-800 dark:text-stone-200">R$ {{ number_format($p->pivot->quantity * $p->pivot->unit_price, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-6">
                <div class="space-y-1 text-sm max-w-xs ml-auto">
                    <div class="flex justify-between text-stone-600 dark:text-stone-400">
                        <span>Subtotal</span>
                        <span>R$ {{ number_format($sale->total + $sale->discount, 2, ',', '.') }}</span>
                    </div>
                    @if($sale->discount > 0)
                    <div class="flex justify-between text-rose-600 dark:text-rose-400">
                        <span>Desconto</span>
                        <span>− R$ {{ number_format($sale->discount, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-brand-800 dark:text-brand-200 pt-2 border-t border-brand-100 dark:border-stone-700">
                        <span>Total</span>
                        <span>R$ {{ number_format($sale->total, 2, ',', '.') }}</span>
                    </div>
                </div>

                @if($sale->notes)
                <div class="mt-4 pt-4 border-t border-brand-100 dark:border-stone-700">
                    <h4 class="text-xs font-semibold text-stone-600 dark:text-stone-400 uppercase tracking-wider mb-1">Observações</h4>
                    <p class="text-sm text-stone-500 dark:text-stone-400">{{ $sale->notes }}</p>
                </div>
                @endif

                <div class="mt-4 pt-4 border-t border-brand-100 dark:border-stone-700">
                    <p class="text-xs text-stone-400 dark:text-stone-500">Vendido por: {{ $sale->user?->name ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    nav, header, .btn-pastel-primary, .btn-pastel-secondary { display: none !important; }
    .card-pastel { box-shadow: none !important; border: 1px solid #ddd !important; }
    body { background: white !important; }
}
</style>
@endsection
