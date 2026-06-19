@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Auditoria de Atividades</h2>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel p-0 overflow-hidden">
            <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                <form method="GET" class="flex gap-2 flex-wrap">
                    <select name="action" class="input-pastel w-auto">
                        <option value="">Todas as ações</option>
                        <option value="created" @selected(request('action') === 'created')>Criação</option>
                        <option value="updated" @selected(request('action') === 'updated')>Atualização</option>
                        <option value="deleted" @selected(request('action') === 'deleted')>Exclusão</option>
                    </select>
                    <select name="model" class="input-pastel w-auto">
                        <option value="">Todos os modelos</option>
                        <option value="Customer" @selected(request('model') === 'Customer')>Cliente</option>
                        <option value="Appointment" @selected(request('model') === 'Appointment')>Agendamento</option>
                        <option value="Service" @selected(request('model') === 'Service')>Procedimento</option>
                        <option value="Product" @selected(request('model') === 'Product')>Produto</option>
                        <option value="User" @selected(request('model') === 'User')>Usuário</option>
                        <option value="AnamnesisForm" @selected(request('model') === 'AnamnesisForm')>Anamnese</option>
                    </select>
                    <input type="text" name="search" placeholder="Buscar na descrição..." value="{{ request('search') }}" class="input-pastel flex-1">
                    <button type="submit" class="btn-pastel-secondary">Filtrar</button>
                    <a href="{{ route('admin.logs.index') }}" class="btn-pastel-secondary">Limpar</a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table-pastel">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Modelo</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? '—' }}</td>
                            <td>
                                <span class="badge-pastel
                                    @if($log->action === 'created') bg-emerald-100 text-emerald-700
                                    @elseif($log->action === 'updated') bg-sky-100 text-sky-700
                                    @elseif($log->action === 'deleted') bg-rose-100 text-rose-700
                                    @endif">
                                    {{ $log->actionLabel() }}
                                </span>
                            </td>
                            <td>{{ $log->modelLabel() }}</td>
                            <td class="text-sm text-stone-600 max-w-md truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-brand-400">Nenhum registro encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-brand-100">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection