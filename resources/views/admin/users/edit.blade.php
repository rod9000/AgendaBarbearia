@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-brand-800 leading-tight">Editar Usuário</h2>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Nome</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="input-pastel">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="input-pastel">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Senha (deixe em branco para manter)</label>
                    <input type="password" name="password" class="input-pastel">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" class="input-pastel">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Função</label>
                    <select name="role" required class="input-pastel" id="roleSelect">
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="attendant" {{ old('role', $user->role) == 'attendant' ? 'selected' : '' }}>Barbeiro</option>
                    </select>
                    @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="active" value="1" {{ old('active', $user->active) ? 'checked' : '' }} class="rounded border-brand-300 text-brand-600 shadow-sm focus:ring-brand-300">
                        <span class="text-sm text-brand-700">Ativo</span>
                    </label>
                </div>

                <div class="mb-4" id="permissionsSection">
                    <label class="block text-sm font-medium text-brand-700 mb-2">Permissões de Acesso</label>
                    <p class="text-xs text-brand-400 mb-3">Marque as páginas que este usuário pode acessar.</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($pages as $key => $label)
                        <label class="inline-flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-brand-50 dark:hover:bg-stone-800">
                            <input type="checkbox" name="pages[]" value="{{ $key }}"
                                {{ in_array($key, old('pages', $userPages)) ? 'checked' : '' }}
                                class="rounded border-brand-300 text-brand-600 shadow-sm focus:ring-brand-300">
                            <span class="text-sm text-brand-700 dark:text-stone-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn-pastel-secondary">Cancelar</a>
                    <button type="submit" class="btn-pastel-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    document.getElementById('permissionsSection').style.display = this.value === 'admin' ? 'none' : 'block';
});
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('roleSelect');
    document.getElementById('permissionsSection').style.display = sel.value === 'admin' ? 'none' : 'block';
});
</script>
@endpush
