<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-stone-800 dark:text-stone-200">Fechamento de Comissões</h2>
            <a href="{{ route('admin.reports.commissions.csv', ['month' => $month, 'year' => $year]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exportar CSV
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filters --}}
            <div class="bg-white dark:bg-stone-800 shadow-sm rounded-xl p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-stone-500 dark:text-stone-400 mb-1">Mês</label>
                        <select name="month" class="rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 text-sm">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 dark:text-stone-400 mb-1">Ano</label>
                        <select name="year" class="rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 text-sm">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 dark:text-stone-400 mb-1">Barbeiro</label>
                        <select name="user_id" class="rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 text-sm">
                            <option value="">Todos</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition">Filtrar</button>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-stone-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-stone-500 dark:text-stone-400 mb-1">Total Comissões</p>
                    <p class="text-2xl font-bold text-stone-800 dark:text-stone-100">R$ {{ number_format($grandTotal, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-stone-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-stone-500 dark:text-stone-400 mb-1">Já Pago</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($grandPaid, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-stone-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-stone-500 dark:text-stone-400 mb-1">Pendente</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">R$ {{ number_format($grandPending, 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-stone-800 shadow-sm rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-stone-50 dark:bg-stone-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-stone-600 dark:text-stone-300">Barbeiro</th>
                                <th class="px-4 py-3 text-center font-medium text-stone-600 dark:text-stone-300">Atendimentos</th>
                                <th class="px-4 py-3 text-right font-medium text-stone-600 dark:text-stone-300">Total</th>
                                <th class="px-4 py-3 text-right font-medium text-stone-600 dark:text-stone-300">Pago</th>
                                <th class="px-4 py-3 text-right font-medium text-stone-600 dark:text-stone-300">Pendente</th>
                                <th class="px-4 py-3 text-center font-medium text-stone-600 dark:text-stone-300">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-stone-700">
                            @forelse($summary as $row)
                            <tr class="hover:bg-stone-50 dark:hover:bg-stone-700/30">
                                <td class="px-4 py-3 font-medium text-stone-800 dark:text-stone-100">
                                    {{ $row->user->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-center text-stone-600 dark:text-stone-300">
                                    {{ $row->total_appointments }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-stone-800 dark:text-stone-100">
                                    R$ {{ number_format($row->total_commission, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400">
                                    R$ {{ number_format($row->paid, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">
                                    R$ {{ number_format($row->pending, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('admin.commissions.professional', $row->user_id) . '?month=' . $month . '&year=' . $year }}"
                                       class="text-brand-600 dark:text-brand-400 hover:underline text-xs">
                                        Detalhes
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-stone-400">Nenhuma comissão no período.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($summary->count() > 0)
                        <tfoot class="bg-stone-50 dark:bg-stone-700/50 font-semibold">
                            <tr>
                                <td class="px-4 py-3 text-stone-800 dark:text-stone-100">Total</td>
                                <td class="px-4 py-3 text-center text-stone-800 dark:text-stone-100">{{ $summary->sum('total_appointments') }}</td>
                                <td class="px-4 py-3 text-right text-stone-800 dark:text-stone-100">R$ {{ number_format($grandTotal, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-emerald-600">R$ {{ number_format($grandPaid, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-amber-600">R$ {{ number_format($grandPending, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
