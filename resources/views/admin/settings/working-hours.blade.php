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
            {{-- Desktop: grid de 3 colunas --}}
            <div class="hidden md:grid grid-cols-[130px_1fr_150px] gap-4 px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 dark:bg-stone-700 dark:text-stone-400 rounded-t-xl border-b border-gray-100 dark:border-stone-700">
                <span>Dia</span>
                <span>Horários</span>
                <span class="text-right">Ações</span>
            </div>

            @foreach(range(0, 6) as $day)
            <div class="grid grid-cols-1 md:grid-cols-[130px_1fr_150px] gap-4 px-4 py-3 border-b border-gray-100 dark:border-stone-700 items-start {{ $loop->last ? 'rounded-b-xl' : '' }}">

                {{-- Dia + ações mobile --}}
                <div class="font-medium pt-1 flex md:block items-center justify-between">
                    <span class="text-stone-800 dark:text-stone-200">{{ $days[$day] }}</span>
                    <div class="flex md:hidden items-center gap-2">
                        <button type="submit" form="day-form-{{ $user->id }}-{{ $day }}"
                            class="text-sm text-brand-600 hover:text-brand-800 font-medium">
                            Salvar
                        </button>
                        <div class="copy-wrap relative">
                            <button type="button" class="copy-trigger text-sm text-brand-600 hover:text-brand-800 font-medium flex items-center gap-1">
                                Copiar <span class="text-xs">&#9660;</span>
                            </button>
                            <div class="copy-dropdown hidden absolute right-0 z-50 mt-1 bg-white border border-brand-200 rounded-xl shadow-lg p-3 min-w-[200px]">
                                @include('admin.settings._copy_form', ['user' => $user, 'day' => $day, 'days' => $days])
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Formulário de horários --}}
                <form method="POST" action="{{ route('admin.settings.working-hours.store') }}" class="day-form" id="day-form-{{ $user->id }}-{{ $day }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="day_of_week" value="{{ $day }}">
                    <div class="blocks-container space-y-2">
                        @php $blocks = $hours[$user->id][$day] ?? collect(); @endphp
                        @forelse($blocks as $b)
                        <div class="block-row flex flex-wrap items-center gap-2">
                            <input type="time" name="blocks[{{ $loop->index }}][start]" value="{{ $b->start_time }}" required class="input-pastel w-28 sm:w-32">
                            <span class="text-stone-400">—</span>
                            <input type="time" name="blocks[{{ $loop->index }}][end]" value="{{ $b->end_time }}" required class="input-pastel w-28 sm:w-32">
                            <button type="button" class="remove-block text-red-400 hover:text-red-600 text-lg leading-none px-1" title="Remover">&times;</button>
                        </div>
                        @empty
                        <div class="text-sm text-stone-400">Nenhum horário definido</div>
                        @endforelse
                    </div>
                    <div class="mt-2">
                        <button type="button" class="add-block text-sm text-brand-600 hover:text-brand-800 font-medium">+ Adicionar horário</button>
                    </div>
                </form>

                {{-- Ações desktop --}}
                <div class="hidden md:flex flex-col items-end gap-2 pt-1">
                    <div class="copy-wrap relative">
                        <button type="button" class="copy-trigger text-sm text-brand-600 hover:text-brand-800 font-medium flex items-center gap-1">
                            Copiar <span class="text-xs">&#9660;</span>
                        </button>
                        <div class="copy-dropdown hidden absolute right-0 z-50 mt-1 bg-white border border-brand-200 rounded-xl shadow-lg p-3 min-w-[200px]">
                            @include('admin.settings._copy_form', ['user' => $user, 'day' => $day, 'days' => $days])
                        </div>
                    </div>
                    <button type="submit" form="day-form-{{ $user->id }}-{{ $day }}"
                        class="text-sm text-brand-600 hover:text-brand-800 font-medium">
                        Salvar dia
                    </button>
                </div>

            </div>
            @endforeach
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
