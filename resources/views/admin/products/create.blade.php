@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">{{ isset($product) ? 'Editar Produto' : 'Novo Produto' }}</h2>
        <a href="{{ route('admin.products.index') }}" class="btn-pastel-secondary">Voltar</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel max-w-lg">
            <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}">
                @csrf
                @if(isset($product)) @method('PUT') @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-1">Nome</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-pastel">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-1">Marca</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" class="input-pastel">
                    @error('brand') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-1">Validade</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="input-pastel">
                    @error('expiry_date') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-1">Preço de Compra</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price') }}" required class="input-pastel">
                    @error('purchase_price') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-1">Preço de Venda</label>
                    <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price') }}" class="input-pastel">
                    @error('sale_price') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-1">Quantidade em Estoque</label>
                    <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0" class="input-pastel">
                    @error('quantity') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-1">Estoque Mínimo (alerta)</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', 0) }}" min="0" class="input-pastel">
                    @error('min_stock') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-1">Fornecedor</label>
                    <input type="text" name="supplier" value="{{ old('supplier') }}" class="input-pastel">
                    @error('supplier') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.products.index') }}" class="btn-pastel-secondary">Cancelar</a>
                    <button type="submit" class="btn-pastel-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
