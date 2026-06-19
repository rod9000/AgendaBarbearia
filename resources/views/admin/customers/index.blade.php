@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Clientes</h2>
        <a href="{{ route('admin.customers.create') }}" class="btn-pastel-primary">+ Novo Cliente</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel p-0">
            <div class="p-4 border-b border-brand-100 dark:border-stone-700 bg-brand-50/30 dark:bg-stone-800">
                <form method="GET" class="flex flex-col sm:flex-row gap-2">
                    <input type="text" name="search" placeholder="Buscar por nome, CPF ou telefone..." value="{{ request('search') }}" class="input-pastel flex-1">
                    <button type="submit" class="btn-pastel-secondary">Buscar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-50/50 dark:bg-stone-700">
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300"></th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Nome</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">CPF</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Telefone</th>
                            <th class="px-2 py-2 text-left font-semibold text-brand-700 dark:text-brand-300">Nascimento</th>
                            <th class="px-2 py-2 text-right font-semibold text-brand-700 dark:text-brand-300"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                        <tr class="border-t border-brand-100/50 hover:bg-brand-50/50 dark:border-stone-700 dark:hover:bg-stone-700/50">
                            <td class="px-2 py-2">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-brand-100 flex items-center justify-center flex-shrink-0">
                                    @if($c->photo)
                                        <img src="{{ $c->photo }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-2 font-medium text-gray-800 dark:text-stone-200">{{ $c->name }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->cpf }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->phone }}</td>
                            <td class="px-2 py-2 text-stone-600 dark:text-stone-400">{{ $c->birth_date->format('d/m/Y') }}</td>
                            <td class="px-2 py-2 text-right">
                                <a href="{{ route('admin.customers.show', $c) }}" class="inline-block px-2 py-1 text-xs font-medium text-brand-600 hover:text-brand-800 dark:text-brand-400">Ver</a>
                                <a href="{{ route('admin.customers.edit', $c) }}" class="inline-block px-2 py-1 text-xs font-medium text-brand-600 hover:text-brand-800 dark:text-brand-400">Editar</a>
                                <form method="POST" action="{{ route('admin.customers.destroy', $c) }}" class="inline" onsubmit="return confirm('Excluir cliente? Os agendamentos vinculados também serão removidos.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-xs font-medium text-rose-500 hover:text-rose-700">Excluir</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-brand-400">Nenhum cliente cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-2 py-2 sm:px-4 sm:py-3 border-t border-brand-100 dark:border-stone-700">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
