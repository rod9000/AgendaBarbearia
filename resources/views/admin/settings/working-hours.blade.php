@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Horários de Funcionamento</h2>
        <a href="{{ route('admin.settings.blocked-slots') }}" class="btn-pastel-secondary">Bloqueios</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @foreach($users as $user)
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">{{ $user->name }}</h3>
            <div class="overflow-x-auto">
                <table class="table-pastel w-full">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Horários</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(range(0, 6) as $day)
                        <tr>
                            <td class="font-medium align-top pt-4">{{ $days[$day] }}</td>
                            <td class="py-2">
                                <form method="POST" action="{{ route('admin.settings.working-hours.store') }}" class="day-form">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <input type="hidden" name="day_of_week" value="{{ $day }}">
                                    <div class="blocks-container space-y-2">
                                        @php $blocks = $hours[$user->id][$day] ?? collect(); @endphp
                                        @forelse($blocks as $b)
                                        <div class="block-row flex items-center gap-2">
                                            <input type="time" name="blocks[{{ $loop->index }}][start]" value="{{ $b->start_time }}" required class="input-pastel w-32">
                                            <span class="text-stone-400">—</span>
                                            <input type="time" name="blocks[{{ $loop->index }}][end]" value="{{ $b->end_time }}" required class="input-pastel w-32">
                                            <button type="button" class="remove-block text-red-400 hover:text-red-600 text-lg leading-none px-1" title="Remover">&times;</button>
                                        </div>
                                        @empty
                                        <div class="text-sm text-stone-400">Nenhum horário definido</div>
                                        @endforelse
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <button type="button" class="add-block text-sm text-brand-600 hover:text-brand-800 font-medium">+ Adicionar horário</button>
                                        <button type="submit" class="text-sm text-brand-600 hover:text-brand-800 font-medium ml-auto">Salvar dia</button>
                                    </div>
                                </form>
                            </td>
                            <td class="align-top pt-6">
                                <div class="copy-wrap relative">
                                    <button type="button" class="copy-trigger text-sm text-brand-600 hover:text-brand-800 font-medium flex items-center gap-1">
                                        Copiar <span class="text-xs">&#9660;</span>
                                    </button>
                                    <div class="copy-dropdown hidden absolute right-0 z-50 mt-1 bg-white border border-brand-200 rounded-xl shadow-lg p-3 min-w-[200px]">
                                        <form method="POST" action="{{ route('admin.settings.working-hours.copy') }}" class="copy-form">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <input type="hidden" name="source_day" value="{{ $day }}">
                                            <p class="text-xs text-stone-500 mb-2">Copiar para:</p>
                                            @foreach(range(0, 6) as $td)
                                            @if($td !== $day)
                                            <label class="flex items-center gap-2 py-1 text-sm text-stone-700 cursor-pointer hover:text-brand-700">
                                                <input type="checkbox" name="target_days[]" value="{{ $td }}" class="rounded border-brand-300 text-brand-600">
                                                {{ $days[$td] }}
                                            </label>
                                            @endif
                                            @endforeach
                                            <button type="submit" class="mt-2 w-full text-sm btn-pastel-primary py-1.5">Copiar</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.day-form').forEach(function(form) {
    var addBtn = form.querySelector('.add-block');
    var container = form.querySelector('.blocks-container');

    addBtn.addEventListener('click', function() {
        var idx = container.querySelectorAll('.block-row').length;
        var row = document.createElement('div');
        row.className = 'block-row flex items-center gap-2';
        row.innerHTML = [
            '<input type="time" name="blocks[' + idx + '][start]" required class="input-pastel w-32">',
            '<span class="text-stone-400">\u2014</span>',
            '<input type="time" name="blocks[' + idx + '][end]" required class="input-pastel w-32">',
            '<button type="button" class="remove-block text-red-400 hover:text-red-600 text-lg leading-none px-1" title="Remover">&times;</button>'
        ].join('');

        var placeholder = container.querySelector('.text-sm.text-stone-400');
        if (placeholder) placeholder.remove();

        container.appendChild(row);
        attachRemoveHandler(row.querySelector('.remove-block'));
    });

    form.querySelectorAll('.remove-block').forEach(function(btn) {
        attachRemoveHandler(btn);
    });

    form.addEventListener('submit', function() {
        var rows = form.querySelectorAll('.block-row');
        rows.forEach(function(row, idx) {
            row.querySelectorAll('input[name$="[start]"]').forEach(function(inp) {
                inp.name = 'blocks[' + idx + '][start]';
            });
            row.querySelectorAll('input[name$="[end]"]').forEach(function(inp) {
                inp.name = 'blocks[' + idx + '][end]';
            });
        });
    });
});

function attachRemoveHandler(btn) {
    btn.addEventListener('click', function() {
        var row = this.closest('.block-row');
        var container = row.closest('.blocks-container');
        row.remove();
        if (container.querySelectorAll('.block-row').length === 0) {
            var msg = document.createElement('div');
            msg.className = 'text-sm text-stone-400';
            msg.textContent = 'Nenhum horário definido';
            container.appendChild(msg);
        }
    });
}

// Copy dropdown toggle
document.querySelectorAll('.copy-wrap').forEach(function(wrap) {
    var trigger = wrap.querySelector('.copy-trigger');
    var dropdown = wrap.querySelector('.copy-dropdown');

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = !dropdown.classList.contains('hidden');
        document.querySelectorAll('.copy-dropdown').forEach(function(d) {
            if (d !== dropdown) d.classList.add('hidden');
        });
        dropdown.classList.toggle('hidden', isOpen);
    });

    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

document.addEventListener('click', function() {
    document.querySelectorAll('.copy-dropdown').forEach(function(d) {
        d.classList.add('hidden');
    });
});
</script>
@endpush
