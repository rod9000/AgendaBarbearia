@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-brand-800 leading-tight">Backup do Banco de Dados</h2>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel text-center">
            <div class="text-6xl mb-4">💾</div>
            <h3 class="text-lg font-semibold text-brand-800 mb-2">Backup do Banco de Dados</h3>
            <p class="text-sm text-stone-500 mb-6">Faça o download de uma cópia completa do banco de dados em formato SQL.</p>

            <div class="flex justify-center gap-4">
                <form method="POST" action="{{ route('admin.backup.run') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-pastel-primary">
                        Gerar e Baixar Backup
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="mt-4 bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif
        </div>

        @if(isset($backups) && count($backups) > 0)
        <div class="card-pastel p-0 overflow-hidden mt-6">
            <div class="p-4 border-b border-brand-100 bg-brand-50/30">
                <h3 class="font-semibold text-brand-700">Backups Recentes</h3>
            </div>
            <table class="table-pastel">
                <thead>
                    <tr>
                        <th>Arquivo</th>
                        <th>Data</th>
                        <th>Tamanho</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $b)
                    <tr>
                        <td class="font-medium">{{ $b['name'] }}</td>
                        <td>{{ $b['date'] }}</td>
                        <td>{{ $b['size'] }}</td>
                        <td>
                            <a href="{{ route('admin.backup.download', basename($b['name'])) }}" class="text-brand-600 hover:text-brand-800 font-medium text-sm">Download</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection