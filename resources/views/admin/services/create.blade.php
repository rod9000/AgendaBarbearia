@extends('layouts.app')

@section('header')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">{{ isset($service) ? 'Editar Procedimento' : 'Novo Procedimento' }}</h2>
        <a href="{{ route('admin.services.index') }}" class="btn-pastel-secondary w-full sm:w-auto justify-center text-sm">← Voltar</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel">
            <form method="POST" action="{{ isset($service) ? route('admin.services.update', $service) : route('admin.services.store') }}">
                @csrf
                @if(isset($service)) @method('PUT') @endif

                <div class="mb-4">
                    <label class="block text-sm hidpi:text-base font-medium text-brand-700">Nome do Procedimento</label>
                    <input type="text" name="name" value="{{ old('name', $service->name ?? '') }}" required class="input-pastel hidpi:text-base hidpi:py-2.5">
                    @error('name') <p class="text-rose-500 text-xs hidpi:text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4">
                    <div>
                        <label class="block text-sm hidpi:text-base font-medium text-brand-700">Duração (minutos)</label>
                        <input type="number" name="duration_min" value="{{ old('duration_min', $service->duration_min ?? '') }}" required min="15" class="input-pastel hidpi:text-base hidpi:py-2.5">
                        @error('duration_min') <p class="text-rose-500 text-xs hidpi:text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm hidpi:text-base font-medium text-brand-700">Valor (R$)</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $service->price ?? '') }}" required min="0" class="input-pastel hidpi:text-base hidpi:py-2.5">
                        @error('price') <p class="text-rose-500 text-xs hidpi:text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm hidpi:text-base font-medium text-brand-700">Cor do Calendário</label>
                    <input type="color" name="color_hex" value="{{ old('color_hex', $service->color_hex ?? '#fbbf24') }}" class="mt-1 h-10 hidpi:h-12 w-20 hidpi:w-24 rounded-lg border-brand-200 shadow-sm cursor-pointer">
                </div>

                <div class="mb-4">
                    <label class="block text-sm hidpi:text-base font-medium text-brand-700">Estimativa do valor usado do produto (R$)</label>
                    <input type="number" step="0.01" name="estimated_product_cost" value="{{ old('estimated_product_cost', $service->estimated_product_cost ?? '') }}" min="0" class="input-pastel hidpi:text-base hidpi:py-2.5">
                    @error('estimated_product_cost') <p class="text-rose-500 text-xs hidpi:text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4 p-4 bg-indigo-50 rounded-xl border border-indigo-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-sm hidpi:text-base text-indigo-800">Produtos Utilizados</h4>
                        <button type="button" id="addProductRow" class="text-xs hidpi:text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Adicionar produto</button>
                    </div>
                    <div id="productsContainer" class="space-y-2">
                        @php $oldProducts = old('products', isset($service) && $service->relationLoaded('products') ? $service->products->map(fn($p) => ['product_id' => $p->id, 'quantity' => $p->pivot->quantity, 'is_per_session' => $p->pivot->is_per_session])->toArray() : []); @endphp
                        @forelse($oldProducts as $i => $p)
                        <div class="product-row flex items-center gap-2 hidpi:gap-3">
                            <select name="products[{{ $i }}][product_id]" class="input-pastel flex-1 hidpi:text-base hidpi:py-2.5">
                                <option value="">Selecione...</option>
                                @foreach($products as $prod)
                                <option value="{{ $prod->id }}" {{ ($p['product_id'] ?? '') == $prod->id ? 'selected' : '' }}>{{ $prod->name }} (estoque: {{ $prod->quantity }})</option>
                                @endforeach
                            </select>
                            <input type="number" name="products[{{ $i }}][quantity]" value="{{ $p['quantity'] ?? 1 }}" min="1" class="input-pastel w-16 hidpi:w-20 hidpi:text-base hidpi:py-2.5" placeholder="Qtd">
                            <label class="flex items-center gap-1 text-xs hidpi:text-sm text-indigo-700 whitespace-nowrap cursor-pointer">
                                <input type="checkbox" name="products[{{ $i }}][is_per_session]" value="1" {{ !empty($p['is_per_session']) ? 'checked' : '' }} class="rounded border-indigo-300 text-indigo-600 shadow-sm focus:ring-indigo-300">
                                Sessão
                            </label>
                            <button type="button" class="remove-product text-rose-400 hover:text-rose-600 font-bold px-1">×</button>
                        </div>
                        @empty
                        <p class="text-xs hidpi:text-sm text-indigo-400">Nenhum produto vinculado. Clique em "Adicionar produto" para começar.</p>
                        @endforelse
                    </div>
                    <p class="text-xs hidpi:text-sm text-indigo-400 mt-2">Marcar como <strong>"Sessão"</strong> = utilizado uma única vez por visita do cliente (ex: luvas, máscaras). Desmarcado = consumido a cada procedimento.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm hidpi:text-base font-medium text-brand-700">Descrição</label>
                    <textarea name="description" rows="3" class="input-pastel hidpi:text-base">{{ old('description', $service->description ?? '') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center gap-2 hidpi:gap-3 cursor-pointer">
                        <input type="checkbox" name="active" value="1" {{ old('active', $service->active ?? true) ? 'checked' : '' }} class="w-4 h-4 hidpi:w-5 hidpi:h-5 rounded border-brand-300 text-brand-600 shadow-sm focus:ring-brand-300">
                        <span class="text-sm hidpi:text-base text-brand-700">Ativo</span>
                    </label>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 hidpi:gap-4">
                    <a href="{{ route('admin.services.index') }}" class="btn-pastel-secondary w-full sm:w-auto justify-center hidpi:text-base hidpi:py-2.5">Cancelar</a>
                    <button type="submit" class="btn-pastel-primary w-full sm:w-auto justify-center hidpi:text-base hidpi:py-2.5">
                        {{ isset($service) ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('productsContainer');
    const addBtn = document.getElementById('addProductRow');
    let index = container.querySelectorAll('.product-row').length;

    addBtn.addEventListener('click', function () {
        const empty = container.querySelector('.text-indigo-400');
        if (empty) empty.remove();

        const options = `@json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'quantity' => $p->quantity]))`;
        const prods = JSON.parse(options);
        const html = `
            <div class="product-row flex items-center gap-2 hidpi:gap-3">
                <select name="products[${index}][product_id]" class="input-pastel flex-1 hidpi:text-base hidpi:py-2.5">
                    <option value="">Selecione...</option>
                    ${prods.map(p => `<option value="${p.id}">${p.name} (estoque: ${p.quantity})</option>`).join('')}
                </select>
                <input type="number" name="products[${index}][quantity]" value="1" min="1" class="input-pastel w-16 hidpi:w-20 hidpi:text-base hidpi:py-2.5" placeholder="Qtd">
                <label class="flex items-center gap-1 text-xs hidpi:text-sm text-indigo-700 whitespace-nowrap cursor-pointer">
                    <input type="checkbox" name="products[${index}][is_per_session]" value="1" class="rounded border-indigo-300 text-indigo-600 shadow-sm focus:ring-indigo-300">
                    Sessão
                </label>
                <button type="button" class="remove-product text-rose-400 hover:text-rose-600 font-bold px-1">×</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        index++;
    });

    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-product')) {
            e.target.closest('.product-row').remove();
            if (!container.querySelector('.product-row')) {
                container.innerHTML = '<p class="text-xs hidpi:text-sm text-indigo-400">Nenhum produto vinculado. Clique em "Adicionar produto" para começar.</p>';
            }
        }
    });
});
</script>
@endpush
