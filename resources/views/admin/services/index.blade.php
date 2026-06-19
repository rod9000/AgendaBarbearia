@extends('layouts.app')

@section('header')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Serviços</h2>
        <a href="{{ route('admin.services.create') }}" class="btn-pastel-primary w-full sm:w-auto justify-center">+ Novo Procedimento</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-pastel w-full">
                    <thead>
                        <tr>
                            <th>Procedimento</th>
                            <th>Duração</th>
                            <th>Valor</th>
                            <th class="hidden sm:table-cell">Ativo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $s)
                        <tr>
                            <td class="hidpi:text-base">
                                <span class="inline-block w-3 h-3 hidpi:w-4 hidpi:h-4 rounded-full mr-2 shadow-sm shrink-0" style="background: {{ $s->color_hex }}"></span>
                                <span class="font-medium text-gray-800 dark:text-stone-200">{{ $s->name }}</span>
                            </td>
                            <td class="hidpi:text-base">{{ $s->duration_min }} min</td>
                            <td class="text-emerald-700 font-medium hidpi:text-base">R$ {{ number_format($s->price, 2, ',', '.') }}</td>
                            <td class="hidden sm:table-cell hidpi:text-base">{{ $s->active ? 'Sim' : 'Não' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1 sm:gap-2 hidpi:gap-3">
                                    <a href="{{ route('admin.services.edit', $s) }}" class="text-brand-600 hover:text-brand-800 font-medium px-2 hidpi:px-3 py-1 hidpi:py-1.5">Editar</a>
                                    <form method="POST" action="{{ route('admin.services.destroy', $s) }}" class="inline" onsubmit="return confirm('Excluir procedimento? As agendas vinculadas também serão removidas.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 font-medium px-2 hidpi:px-3 py-1 hidpi:py-1.5">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 hidpi:px-6 py-8 text-center text-brand-400">Nenhum procedimento cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 hidpi:p-5 border-t border-brand-100 overflow-x-auto">
                {{ $services->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
