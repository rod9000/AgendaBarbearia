@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-brand-800 leading-tight">{{ isset($product) ? 'Editar Produto' : 'Novo Produto' }}</h2>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel">
            <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}">
                @csrf
                @if(isset($product)) @method('PUT') @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Nome do Produto</label>
                    <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required class="input-pastel">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Marca</label>
                    <input type="text" name="brand" value="{{ old('brand', $product->brand ?? '') }}" class="input-pastel">
                    @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Data de Validade</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', isset($product) && $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '') }}" class="input-pastel">
                    @error('expiry_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Valor Pago (R$)</label>
                    <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? '') }}" required min="0" class="input-pastel">
                    @error('purchase_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Preço de Venda (R$)</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? '') }}" min="0" class="input-pastel">
                    @error('sale_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Quantidade em Estoque</label>
                    <input type="number" name="quantity" value="{{ old('quantity', $product->quantity ?? 0) }}" min="0" class="input-pastel">
                    @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Estoque Mínimo (alerta)</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? 0) }}" min="0" class="input-pastel">
                    @error('min_stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Fornecedor</label>
                    <input type="text" name="supplier" value="{{ old('supplier', $product->supplier ?? '') }}" class="input-pastel">
                    @error('supplier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.products.index') }}" class="btn-pastel-secondary">Cancelar</a>
                    <button type="submit" class="btn-pastel-primary">
                        {{ isset($product) ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
