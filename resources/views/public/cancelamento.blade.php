<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cancelar Agendamento</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        @if($cancelled)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center animate-bounce-in">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Agendamento Cancelado</h1>
                <p class="text-gray-500 mb-6">Seu agendamento foi cancelado com sucesso.</p>

                <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm text-left">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">Data:</span>
                        <span class="font-medium">{{ $appointment->start->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Serviço:</span>
                        <span class="font-medium">{{ $appointment->services->pluck('name')->implode(', ') }}</span>
                    </div>
                </div>

                <a href="{{ url('/reagendar/' . $appointment->confirmation_token) }}"
                   class="block w-full bg-emerald-600 text-white font-semibold rounded-xl py-3 hover:bg-emerald-700 transition-colors">
                    Reagendar Horário
                </a>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Cancelar Agendamento?</h1>
                <p class="text-gray-500 mb-6">Tem certeza que deseja cancelar este agendamento?</p>

                <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm text-left">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">Cliente:</span>
                        <span class="font-medium">{{ $appointment->customer->name }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">Data:</span>
                        <span class="font-medium">{{ $appointment->start->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">Serviço:</span>
                        <span class="font-medium">{{ $appointment->services->pluck('name')->implode(', ') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Profissional:</span>
                        <span class="font-medium">{{ $appointment->user->name }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ url('/cancelar/' . $appointment->confirmation_token) }}">
                    @csrf
                    <div class="flex gap-3">
                        <a href="{{ url('/confirmar/' . $appointment->confirmation_token) }}"
                           class="flex-1 bg-emerald-600 text-white font-semibold rounded-xl py-3 hover:bg-emerald-700 transition-colors text-center">
                            Manter
                        </a>
                        <button type="submit"
                                class="flex-1 bg-red-600 text-white font-semibold rounded-xl py-3 hover:bg-red-700 transition-colors">
                            Confirmar Cancelamento
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <p class="text-center text-gray-400 text-xs mt-6">
            © {{ date('Y') }} Barbearia
        </p>
    </div>
</body>
</html>
