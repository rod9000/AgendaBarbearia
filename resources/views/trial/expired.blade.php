<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Período de Teste Expirado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-2">Período de Teste Expirado</h1>
            <p class="text-gray-500 mb-6">
                Seu período de teste de 30 dias finalizou. Para continuar usando o sistema, entre em contato com nosso suporte.
            </p>

            @if(auth()->user() && auth()->user()->company)
            <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">Empresa:</span>
                    <span class="font-medium text-gray-800">{{ auth()->user()->company->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Data de expiração:</span>
                    <span class="font-medium text-red-600">{{ auth()->user()->company->trial_ends_at->format('d/m/Y') }}</span>
                </div>
            </div>
            @endif

            <div class="space-y-3">
                <a href="https://wa.me/5511999999999" target="_blank"
                   class="block w-full bg-emerald-600 text-white font-semibold rounded-xl py-3 hover:bg-emerald-700 transition-colors">
                    Falar com Suporte via WhatsApp
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="block w-full bg-gray-100 text-gray-700 font-semibold rounded-xl py-3 hover:bg-gray-200 transition-colors">
                        Sair da Conta
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-gray-400 text-xs mt-6">
            © {{ date('Y') }} Barbearia
        </p>
    </div>
</body>
</html>
