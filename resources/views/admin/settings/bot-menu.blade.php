@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.bot') }}" class="text-stone-500 hover:text-stone-700">&larr; Voltar</a>
            <h2 class="font-semibold text-xl text-brand-800 leading-tight">Menu do Bot</h2>
        </div>
        <div class="flex gap-2">
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-pastel-primary">
                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Adicionar
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

        {{-- Pré-visualização --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Pré-visualização</h3>
            <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-sm font-bold shrink-0">B</div>
                    <div class="bg-white dark:bg-stone-800 rounded-2xl rounded-tl-none p-4 max-w-sm shadow-sm">
                        <p class="text-sm whitespace-pre-line">{{ $company->getDefaultWelcomeMessage() }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Itens --}}
        <div class="card-pastel">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-brand-700">Itens do Menu</h3>
                <span class="text-sm text-stone-500">{{ $menuItems->count() }} itens</span>
            </div>

            @if($menuItems->isEmpty())
                <p class="text-stone-500 text-sm">Nenhum item configurado. Clique em "Adicionar" para começar.</p>
            @else
                <div class="space-y-2" id="menuItemsList">
                    @foreach($menuItems as $item)
                        <div class="flex items-center gap-3 p-4 bg-stone-50 dark:bg-stone-800 rounded-xl {{ !$item->is_active ? 'opacity-50' : '' }}" data-id="{{ $item->id }}">
                            <div class="flex flex-col gap-1 shrink-0">
                                <button onclick="moveUp({{ $item->id }})" class="text-stone-400 hover:text-brand-600 disabled:opacity-30" @if($loop->first) disabled @endif>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button onclick="moveDown({{ $item->id }})" class="text-stone-400 hover:text-brand-600 disabled:opacity-30" @if($loop->last) disabled @endif>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                            <span class="w-8 h-8 rounded-full bg-brand-500 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                {{ $item->menu_number }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-stone-800 dark:text-stone-200">{{ $item->label }}</p>
                                <p class="text-xs text-stone-500">{{ $item->getActionLabel() }}</p>
                                @if($item->response_text)
                                    <p class="text-xs text-stone-400 mt-1 truncate">{{ Str::limit($item->response_text, 60) }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-500' }}">
                                    {{ $item->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                                <button onclick="editItem({{ $item->id }}, '{{ addslashes($item->label) }}', '{{ $item->action }}', '{{ addslashes($item->response_text ?? '') }}', {{ $item->is_active ? 'true' : 'false' }})" class="text-stone-400 hover:text-brand-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.bot-menu.destroy', $item) }}" onsubmit="return confirm('Remover este item?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-stone-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Modal Adicionar --}}
<div id="addModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 dark:bg-stone-900 bg-opacity-75" onclick="document.getElementById('addModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-stone-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-brand-700 mb-4">Adicionar Item do Menu</h3>
                <form method="POST" action="{{ route('admin.bot-menu.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="label">Label</label>
                            <input type="text" name="label" required maxlength="100" placeholder="Ex: Agendar horário" class="input-pastel">
                        </div>
                        <div>
                            <label class="label">Ação</label>
                            <select name="action" id="addAction" required class="input-pastel" onchange="toggleCustomText('add')">
                                @foreach($actionTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="addCustomText" class="hidden">
                            <label class="label">Texto da Resposta</label>
                            <textarea name="response_text" rows="3" maxlength="1000" placeholder="Texto que o bot vai enviar..." class="input-pastel"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="btn-pastel-secondary">Cancelar</button>
                        <button type="submit" class="btn-pastel-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Editar --}}
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 dark:bg-stone-900 bg-opacity-75" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-stone-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-brand-700 mb-4">Editar Item do Menu</h3>
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="label">Label</label>
                            <input type="text" name="label" id="editLabel" required maxlength="100" class="input-pastel">
                        </div>
                        <div>
                            <label class="label">Ação</label>
                            <select name="action" id="editAction" required class="input-pastel" onchange="toggleCustomText('edit')">
                                @foreach($actionTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="editCustomText" class="hidden">
                            <label class="label">Texto da Resposta</label>
                            <textarea name="response_text" id="editResponseText" rows="3" maxlength="1000" class="input-pastel"></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="editIsActive" value="1" class="rounded">
                            <label class="text-sm text-stone-700">Ativo</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="btn-pastel-secondary">Cancelar</button>
                        <button type="submit" class="btn-pastel-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editItem(id, label, action, responseText, isActive) {
    document.getElementById('editForm').action = '{{ url("admin/bot-menu") }}/' + id;
    document.getElementById('editLabel').value = label;
    document.getElementById('editAction').value = action;
    document.getElementById('editResponseText').value = responseText;
    document.getElementById('editIsActive').checked = isActive;
    toggleCustomText('edit');
    document.getElementById('editModal').classList.remove('hidden');
}

function toggleCustomText(prefix) {
    var action = document.getElementById(prefix + 'Action').value;
    var div = document.getElementById(prefix + 'CustomText');
    div.classList.toggle('hidden', action !== 'custom');
}

function moveUp(id) {
    var list = document.getElementById('menuItemsList');
    var items = Array.from(list.children);
    var index = items.findIndex(el => el.dataset.id == id);
    if (index > 0) {
        list.insertBefore(items[index], items[index - 1]);
        saveOrder();
    }
}

function moveDown(id) {
    var list = document.getElementById('menuItemsList');
    var items = Array.from(list.children);
    var index = items.findIndex(el => el.dataset.id == id);
    if (index < items.length - 1) {
        list.insertBefore(items[index + 1], items[index]);
        saveOrder();
    }
}

function saveOrder() {
    var list = document.getElementById('menuItemsList');
    var ids = Array.from(list.children).map(el => el.dataset.id);
    fetch('{{ route("admin.bot-menu.reorder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ order: ids })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            var items = document.querySelectorAll('#menuItemsList > div');
            items.forEach((el, i) => {
                el.querySelector('.bg-brand-500').textContent = (i + 1);
            });
        }
    });
}
</script>
@endpush
