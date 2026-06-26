@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Usuários</h2>
        <a href="{{ route('admin.users.create') }}" class="btn-pastel-primary">+ Novo Usuário</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel p-0 overflow-hidden">
            <table class="table-pastel w-full">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Função</th>
                        <th>Ativo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td class="font-medium text-stone-800">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?? '—' }}</td>
                        <td>
                            <span class="badge-pastel {{ $u->role === 'admin' ? 'bg-brand-100 text-brand-700' : 'bg-stone-100 text-stone-600' }}">
                                {{ $u->role === 'admin' ? 'Admin' : 'Barbeiro' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-pastel {{ $u->active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $u->active ? 'Sim' : 'Não' }}
                            </span>
                        </td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-brand-600 hover:text-brand-800 font-medium">Editar</a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Excluir usuário?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Excluir</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-brand-400">Nenhum usuário cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4 border-t border-brand-100">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
