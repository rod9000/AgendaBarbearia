<!DOCTYPE html>
<html lang="pt-BR" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="() => { if (dark) { document.documentElement.classList.add('dark'); } $watch('dark', val => { localStorage.setItem('dark', val); document.documentElement.classList.toggle('dark', val); }); }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmação de Presença</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') . '?nocache=' . env('APP_VERSION', '1.0') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-stone-50 via-brand-50 to-stone-100 min-h-screen font-sans text-stone-800 dark:from-stone-900 dark:via-stone-900 dark:to-stone-800 dark:text-stone-200 flex items-center justify-center p-4">

    <div class="max-w-md w-full animate-fade-in">
        @if ($success)
            <div class="bg-white dark:bg-stone-800 rounded-3xl shadow-2xl p-8 text-center animate-bounce-in">
                <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-stone-800 dark:text-stone-100 mb-2">Presença Confirmada!</h2>
                <p class="text-stone-500 dark:text-stone-400 mb-6">{{ $message }}</p>

                @if(isset($appointment))
                <div class="bg-stone-50 dark:bg-stone-700/50 rounded-2xl p-4 text-left space-y-2 text-sm mb-6">
                    <div class="flex justify-between">
                        <span class="text-stone-400 dark:text-stone-500">Cliente:</span>
                        <span class="font-medium text-stone-800 dark:text-stone-100">{{ $appointment->customer->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-400 dark:text-stone-500">Data:</span>
                        <span class="font-medium text-stone-800 dark:text-stone-100">{{ $appointment->start->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($appointment->services->count() > 0)
                    <div class="flex justify-between">
                        <span class="text-stone-400 dark:text-stone-500">Serviços:</span>
                        <span class="font-medium text-stone-800 dark:text-stone-100 text-right">{{ $appointment->services->pluck('name')->implode(', ') }}</span>
                    </div>
                    @endif
                </div>
                @endif

                <a href="{{ url('/agendar') }}" class="inline-block w-full bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-md text-center">
                    Fazer Novo Agendamento
                </a>
            </div>
        @else
            <div class="bg-white dark:bg-stone-800 rounded-3xl shadow-2xl p-8 text-center animate-bounce-in">
                <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-stone-800 dark:text-stone-100 mb-2">Link Inválido</h2>
                <p class="text-stone-500 dark:text-stone-400 mb-6">{{ $message }}</p>
                <a href="{{ url('/agendar') }}" class="inline-block w-full bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-md text-center">
                    Voltar ao Agendamento
                </a>
            </div>
        @endif
    </div>

</body>
</html>
