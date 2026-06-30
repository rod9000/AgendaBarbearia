@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Cliente: {{ $customer->name }}</h2>
        <div class="space-x-2">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn-pastel-primary">Editar</a>
            <a href="{{ route('admin.customers.index') }}" class="btn-pastel-secondary">Voltar</a>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card-pastel text-center">
                <div class="text-3xl font-bold text-brand-600">{{ $customer->appointments_count }}</div>
                <div class="text-sm text-stone-500 mt-1">Total de Agendamentos</div>
            </div>
            <div class="card-pastel text-center">
                <div class="text-3xl font-bold text-emerald-600">R$ {{ number_format($totalSpent, 2, ',', '.') }}</div>
                <div class="text-sm text-stone-500 mt-1">Total Gasto</div>
            </div>
            <div class="card-pastel text-center">
                <div class="text-3xl font-bold text-amber-600">{{ $customer->birth_date->age }} anos</div>
                <div class="text-sm text-stone-500 mt-1">Idade</div>
            </div>
            <div class="card-pastel text-center cursor-pointer hover:shadow-md transition-shadow" onclick="window.location='{{ route('admin.loyalty.customer', $customer) }}'">
                <div class="text-3xl font-bold text-violet-600">{{ $customer->points ?? 0 }}</div>
                <div class="text-sm text-stone-500 mt-1">Pontos @if(($customer->points ?? 0) > 0)<span class="text-xs text-violet-500">Ver &rarr;</span>@endif</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1 space-y-6">
                <div class="card-pastel">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-brand-100 flex items-center justify-center flex-shrink-0">
                            @if($customer->photo)
                                <img src="{{ $customer->photo }}" alt="{{ $customer->name }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-7 h-7 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-brand-700">Dados do Cliente</h3>
                        </div>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Nome</dt>
                            <dd class="font-medium">{{ $customer->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">CPF</dt>
                            <dd>{{ $customer->cpf }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Telefone</dt>
                            <dd>{{ $customer->phone }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Nascimento</dt>
                            <dd>{{ $customer->birth_date?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Email</dt>
                            <dd>{{ $customer->email ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Cadastrado por</dt>
                            <dd>{{ $customer->creator?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Desde</dt>
                            <dd>{{ $customer->created_at->format('d/m/Y') }}</dd>
                        </div>
                    </dl>
                </div>

                @if($lastAppointment)
                <div class="card-pastel">
                    <h3 class="font-semibold text-brand-700 mb-3">Último Atendimento</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Data</dt>
                            <dd>{{ $lastAppointment->start->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Serviço</dt>
                            <dd>{{ $lastAppointment->service->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Profissional</dt>
                            <dd>{{ $lastAppointment->user->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-stone-500">Status</dt>
                            <dd>
                                <x-status-badge :status="$lastAppointment->status" />
                            </dd>
                        </div>
                    </dl>
                </div>
                @endif

                @if($customer->notes)
                <div class="card-pastel">
                    <h3 class="font-semibold text-brand-700 mb-3">Observações</h3>
                    <p class="text-sm text-stone-600">{{ $customer->notes }}</p>
                </div>
                @endif
            </div>

            <div class="md:col-span-2 space-y-6">
                <div class="card-pastel p-0 overflow-hidden">
                    <div class="p-4 border-b border-brand-100 bg-brand-50/30 flex justify-between items-center">
                        <h3 class="font-semibold text-brand-700">Histórico de Agendamentos</h3>
                        <a href="{{ route('admin.appointments.index') }}?customer_id={{ $customer->id }}" class="text-sm text-brand-600 hover:text-brand-800">Ver na Agenda</a>
                    </div>
                    <table class="table-pastel">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Serviço</th>
                                <th>Profissional</th>
                                <th>Status</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $app)
                            <tr>
                                <td>{{ $app->start->format('d/m/Y H:i') }}</td>
                                <td>{{ $app->service->name }}</td>
                                <td>{{ $app->user->name }}</td>
                                <td>
                                    <x-status-badge :status="$app->status" />
                                </td>
                                <td>R$ {{ number_format($app->service->price, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-brand-400">Nenhum agendamento encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="p-4 border-t border-brand-100">
                        {{ $appointments->links() }}
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
@endsection