@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Nova Recompensa</h2>
        <a href="{{ route('admin.loyalty.index') }}" class="btn-pastel-secondary">Voltar</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel">
            <form method="POST" action="{{ route('admin.loyalty.store') }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-brand-700 mb-1">Nome da Recompensa</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-2 border-brand-200 bg-white/80 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-2.5 text-sm dark:bg-stone-700 dark:border-stone-600 dark:text-stone-200">
                        @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-brand-700 mb-1">Descrição</label>
                        <textarea name="description" rows="3" class="w-full rounded-xl border-2 border-brand-200 bg-white/80 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-2.5 text-sm dark:bg-stone-700 dark:border-stone-600 dark:text-stone-200">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-brand-700 mb-1">Pontos Necessários</label>
                            <input type="number" name="points_required" value="{{ old('points_required') }}" required min="1" class="w-full rounded-xl border-2 border-brand-200 bg-white/80 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-2.5 text-sm dark:bg-stone-700 dark:border-stone-600 dark:text-stone-200">
                            @error('points_required') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-700 mb-1">% Desconto</label>
                            <input type="number" name="discount_percent" value="{{ old('discount_percent', 0) }}" required min="0" max="100" step="0.1" class="w-full rounded-xl border-2 border-brand-200 bg-white/80 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-2.5 text-sm dark:bg-stone-700 dark:border-stone-600 dark:text-stone-200">
                            @error('discount_percent') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="active" id="active" value="1" checked class="rounded border-brand-300 text-brand-600 focus:ring-brand-300">
                        <label for="active" class="text-sm text-brand-700">Ativa</label>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="btn-pastel-primary">Salvar</button>
                    <a href="{{ route('admin.loyalty.index') }}" class="btn-pastel-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
