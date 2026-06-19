@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Produto: {{ $product->name }}</h2>
        <div class="space-x-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn-pastel-primary">Editar</a>
            <a href="{{ route('admin.products.index') }}" class="btn-pastel-secondary">Voltar</a>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card-pastel text-center">
                <div class="text-3xl font-bold
                    @if($product->isOutOfStock()) text-rose-600
                    @elseif($product->isLowStock()) text-amber-600
                    @else text-emerald-600
                    @endif">
                    {{ $product->quantity }}
                </div>
                <div class="text-sm text-stone-500 mt-1">Estoque Atual</div>
            </div>
            <div class="card-pastel text-center">
                <div class="text-3xl font-bold text-brand-600">{{ $product->min_stock }}</div>
                <div class="text-sm text-stone-500 mt-1">Estoque Mínimo</div>
            </div>
            <div class="card-pastel text-center">
                <div class="text-3xl font-bold text-sky-600">R$ {{ number_format($product->purchase_price, 2, ',', '.') }}</div>
                <div class="text-sm text-stone-500 mt-1">Preço Compra</div>
            </div>
            <div class="card-pastel text-center">
                <div class="text-3xl font-bold text-emerald-600">R$ {{ number_format($product->sale_price ?? 0, 2, ',', '.') }}</div>
                <div class="text-sm text-stone-500 mt-1">Preço Venda</div>
            </div>
        </div>

        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Registrar Movimentação</h3>
            <form method="POST" action="{{ route('admin.products.movement.store') }}" class="flex gap-4 items-end flex-wrap">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div>
                    <label class="label">Tipo</label>
                    <select name="type" required class="input-pastel">
                        <option value="in">Entrada</option>
                        <option value="out">Saída</option>
                    </select>
                </div>
                <div>
                    <label class="label">Quantidade</label>
                    <input type="number" name="quantity" min="1" required class="input-pastel">
                </div>
                <div>
                    <label class="label">Observação</label>
                    <input type="text" name="notes" class="input-pastel" placeholder="Motivo da movimentação">
                </div>
                <button type="submit" class="btn-pastel-primary">Registrar</button>
            </form>
        </div>

        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-3">Informações do Produto</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-stone-500">Marca</dt>
                    <dd class="font-medium">{{ $product->brand ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Validade</dt>
                    <dd class="font-medium">{{ $product->expiry_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Fornecedor</dt>
                    <dd class="font-medium">{{ $product->supplier ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Cadastrado em</dt>
                    <dd class="font-medium">{{ $product->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        <div class="card-pastel p-0 overflow-hidden">
            <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                <h3 class="font-semibold text-brand-700">Histórico de Movimentações</h3>
            </div>
            <table class="table-pastel">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Tipo</th>
                        <th>Quantidade</th>
                        <th>Observação</th>
                        <th>Usuário</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($product->stockMovements->sortByDesc('created_at') as $m)
                    <tr>
                        <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge-pastel {{ $m->type === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $m->type === 'in' ? 'Entrada' : 'Saída' }}
                            </span>
                        </td>
                        <td class="font-semibold">{{ $m->quantity }}</td>
                        <td>{{ $m->notes ?? '—' }}</td>
                        <td>{{ $m->user?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-brand-400">Nenhuma movimentação registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection