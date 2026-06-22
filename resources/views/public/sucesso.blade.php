<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-stone-50 via-stone-50 to-brand-50 dark:from-stone-900 dark:via-stone-900 dark:to-stone-800 px-4 py-12">
        <div class="w-full max-w-lg">
            <div class="bg-white dark:bg-stone-800 rounded-2xl shadow-lg border border-stone-100 dark:border-stone-700 p-8 text-center">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-stone-800 dark:text-stone-100 mb-2">Agendamento Confirmado!</h1>
                <p class="text-stone-500 dark:text-stone-400 text-sm mb-6">Seu horário foi reservado com sucesso.</p>

                @if(session('agendamento'))
                    @php $a = session('agendamento'); @endphp
                    <div class="bg-stone-50 dark:bg-stone-700/50 rounded-xl p-4 mb-6 text-left space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-stone-500 dark:text-stone-400">Data</span>
                            <span class="font-medium text-stone-800 dark:text-stone-200">{{ \Carbon\Carbon::parse($a['date'])->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-500 dark:text-stone-400">Horário</span>
                            <span class="font-medium text-stone-800 dark:text-stone-200">{{ $a['time'] }}</span>
                        </div>
                        <div class="border-t border-stone-200 dark:border-stone-600 my-2"></div>
                        @foreach($a['items'] as $item)
                            <div class="flex justify-between">
                                <span class="text-stone-500 dark:text-stone-400">{{ $item['service'] }}</span>
                                <span class="font-medium text-stone-800 dark:text-stone-200">{{ $item['professional'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <p class="text-stone-400 dark:text-stone-500 text-xs mb-6">Entraremos em contato para confirmar.</p>

                <a href="{{ route('public.booking') }}"
                   class="inline-block w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl py-3 transition-all duration-200 text-sm text-center">
                    Novo Agendamento
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
