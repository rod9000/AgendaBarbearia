@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.evolution') }}" class="text-stone-500 hover:text-stone-700">&larr; Voltar</a>
            <h2 class="font-semibold text-xl text-brand-800 leading-tight">Números Bloqueados</h2>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-4 text-sm text-emerald-600 dark:text-emerald-400">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-4 text-sm text-red-600 dark:text-red-400">
            {{ session('error') }}
        </div>
        @endif

        {{-- Adicionar número --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Bloquear Número</h3>
            <p class="text-sm text-stone-500 mb-4">Números bloqueados não receberão nenhuma mensagem do bot.</p>
            <form method="POST" action="{{ route('admin.blocked-numbers.store') }}">
                @csrf
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="label">Telefone</label>
                        <input type="text" name="phone" placeholder="5544999999999" required class="input-pastel">
                    </div>
                    <div class="flex-1">
                        <label class="label">Nome (opcional)</label>
                        <input type="text" name="name" placeholder="Nome do contato" class="input-pastel">
                    </div>
                    <div class="flex-1">
                        <label class="label">Motivo (opcional)</label>
                        <input type="text" name="reason" placeholder="Ex: spam, reclamação" class="input-pastel">
                    </div>
                    <button type="submit" class="btn-pastel-primary whitespace-nowrap">Bloquear</button>
                </div>
            </form>
        </div>

        {{-- Lista --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Números Bloqueados ({{ $blockedNumbers->total() }})</h3>

            @if($blockedNumbers->isEmpty())
                <p class="text-stone-500 text-sm">Nenhum número bloqueado.</p>
            @else
                <div class="space-y-2">
                    @foreach($blockedNumbers as $number)
                        <div class="flex items-center justify-between p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                            <div class="flex-1">
                                <p class="font-medium text-stone-800 dark:text-stone-200 text-sm">{{ $number->phone }}</p>
                                @if($number->name)
                                    <p class="text-xs text-stone-500">{{ $number->name }}</p>
                                @endif
                                @if($number->reason)
                                    <p class="text-xs text-stone-400">Motivo: {{ $number->reason }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">Bloqueado</span>
                                <form method="POST" action="{{ route('admin.blocked-numbers.destroy', $number) }}" onsubmit="return confirm('Desbloquear este número?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-stone-400 hover:text-emerald-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $blockedNumbers->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
