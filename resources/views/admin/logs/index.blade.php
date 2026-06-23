@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Auditoria de Atividades</h2>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel p-0 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
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
                        <option value="Service" @selected(request('model') === 'Service')>Serviço</option>
                        <option value="Product" @selected(request('model') === 'Product')>Produto</option>
                        <option value="User" @selected(request('model') === 'User')>Usuário</option>
                        <option value="Payment" @selected(request('model') === 'Payment')>Pagamento</option>
                        <option value="Commission" @selected(request('model') === 'Commission')>Comissão</option>
                        <option value="WorkingHour" @selected(request('model') === 'WorkingHour')>Horário</option>
                        <option value="BlockedSlot" @selected(request('model') === 'BlockedSlot')>Bloqueio</option>
                        <option value="LoyaltyReward" @selected(request('model') === 'LoyaltyReward')>Recompensa</option>
                        <option value="Company" @selected(request('model') === 'Company')>Empresa</option>
                    </select>
                    <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}" class="input-pastel flex-1 min-w-0">
                    <button type="submit" class="btn-pastel-secondary">Filtrar</button>
                    <a href="{{ route('admin.logs.index') }}" class="btn-pastel-secondary">Limpar</a>
                </form>
            </div>

            {{-- Desktop: Tabela --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Data/Hora</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Usuário</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Ação</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Modelo</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Descrição</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($log->action === 'created')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Criação</span>
                                @elseif($log->action === 'updated')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">Atualização</span>
                                @elseif($log->action === 'deleted')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Exclusão</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $log->modelLabel() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhum registro encontrado.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile: Cards --}}
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($logs as $log)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                        @if($log->action === 'created')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Criação</span>
                        @elseif($log->action === 'updated')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">Atualização</span>
                        @elseif($log->action === 'deleted')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Exclusão</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $log->user?->name ?? '—' }}</span>
                        <span class="text-xs text-gray-400">•</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $log->modelLabel() }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $log->description }}</p>
                </div>
                @empty
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhum registro encontrado.</p>
                </div>
                @endforelse
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
