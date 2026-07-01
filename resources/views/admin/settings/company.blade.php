@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Dados da Empresa</h2>
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

        <form method="POST" action="{{ route('admin.settings.company.store') }}">
            @csrf

            <div class="card-pastel">
                <h3 class="font-semibold text-brand-700 mb-4">Informações da Empresa</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Nome da Empresa</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" class="input-pastel" required>
                    </div>
                    <div>
                        <label class="label">Razão Social</label>
                        <input type="text" name="razao_social" value="{{ old('razao_social', $company->razao_social ?? '') }}" class="input-pastel">
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}" class="input-pastel">
                    </div>
                    <div>
                        <label class="label">Telefone</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="input-pastel" placeholder="(44) 99999-9999">
                    </div>
                    <div>
                        <label class="label">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $company->whatsapp) }}" class="input-pastel" placeholder="44999999999">
                    </div>
                </div>
            </div>

            <div class="card-pastel">
                <h3 class="font-semibold text-brand-700 mb-4">Endereço</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label">Endereço</label>
                        <input type="text" name="endereco" value="{{ old('endereco', $company->endereco ?? '') }}" class="input-pastel" placeholder="Rua, Avenida...">
                    </div>
                    <div>
                        <label class="label">Número</label>
                        <input type="text" name="numero" value="{{ old('numero', $company->numero ?? '') }}" class="input-pastel">
                    </div>
                    <div>
                        <label class="label">Bairro</label>
                        <input type="text" name="bairro" value="{{ old('bairro', $company->bairro ?? '') }}" class="input-pastel">
                    </div>
                    <div>
                        <label class="label">Cidade</label>
                        <input type="text" name="cidade" value="{{ old('cidade', $company->cidade ?? '') }}" class="input-pastel">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">CEP</label>
                            <input type="text" name="cep" value="{{ old('cep', $company->cep ?? '') }}" class="input-pastel" placeholder="00000-00">
                        </div>
                        <div>
                            <label class="label">UF</label>
                            <select name="uf" class="input-pastel">
                                <option value="">Selecione</option>
                                @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $sigla)
                                <option value="{{ $sigla }}" {{ old('uf', $company->uf ?? '') === $sigla ? 'selected' : '' }}>{{ $sigla }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Complemento</label>
                        <input type="text" name="complemento" value="{{ old('complemento', $company->complemento ?? '') }}" class="input-pastel" placeholder="Sala, Bloco, etc.">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-pastel-primary">Salvar Dados</button>
            </div>
        </form>

    </div>
</div>
@endsection
