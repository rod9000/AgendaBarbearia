@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Bloqueios na Agenda</h2>
        <a href="{{ route('admin.settings.working-hours') }}" class="btn-pastel-secondary">Horários</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="card-pastel max-w-lg">
            <h3 class="font-semibold text-brand-700 mb-4">Novo Bloqueio</h3>
            <form method="POST" action="{{ route('admin.settings.blocked-slots.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="label">Profissional (opcional)</label>
                    <select name="user_id" class="input-pastel">
                        <option value="">Todos</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Início</label>
                        <input type="datetime-local" name="start" required class="input-pastel">
                    </div>
                    <div>
                        <label class="label">Fim</label>
                        <input type="datetime-local" name="end" required class="input-pastel">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="label">Motivo</label>
                    <input type="text" name="reason" class="input-pastel" placeholder="Ex: Feriado, Reunião...">
                </div>
                <button type="submit" class="btn-pastel-primary">Salvar Bloqueio</button>
            </form>
        </div>

        <div class="card-pastel p-0 overflow-hidden">
            <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                <h3 class="font-semibold text-brand-700">Bloqueios Futuros</h3>
            </div>
            <table class="table-pastel">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Profissional</th>
                        <th>Motivo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $s)
                    <tr>
                        <td>{{ $s->start->format('d/m/Y H:i') }} - {{ $s->end->format('H:i') }}</td>
                        <td>{{ $s->user?->name ?? 'Todos' }}</td>
                        <td>{{ $s->reason ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.settings.blocked-slots.destroy', $s) }}" class="inline" onsubmit="return confirm('Remover bloqueio?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 font-medium text-sm">Remover</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-brand-400">Nenhum bloqueio cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-brand-100">{{ $slots->links() }}</div>
        </div>
    </div>
</div>
@endsection