<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 dark:text-stone-100">WhatsApp da Barbearia</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white dark:bg-stone-800 rounded-2xl shadow-sm border border-stone-100 dark:border-stone-700 p-6">
                <p class="text-sm text-stone-500 dark:text-stone-400 mb-4">
                    Este número será usado no botão de WhatsApp da página pública de agendamento.
                </p>

                @if(session('success'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-3 text-sm text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3 text-sm text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
                @endif

                <form method="POST" action="{{ route('admin.settings.whatsapp.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Número do WhatsApp</label>
                        <input type="text" name="whatsapp" id="whatsappInput"
                               value="{{ old('whatsapp', $company?->whatsapp ?? '') }}"
                               class="w-full rounded-xl border-2 border-stone-200 dark:border-stone-600 bg-white/80 dark:bg-stone-700 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-3 text-sm dark:text-stone-200"
                               placeholder="(44) 99999-9999">
                        @error('whatsapp')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 text-sm">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('whatsappInput').addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '');
            if (v.length <= 2) this.value = '(' + v;
            else if (v.length <= 6) this.value = '(' + v.slice(0,2) + ') ' + v.slice(2);
            else if (v.length <= 10) this.value = '(' + v.slice(0,2) + ') ' + v.slice(2,6) + '-' + v.slice(6);
            else this.value = '(' + v.slice(0,2) + ') ' + v.slice(2,7) + '-' + v.slice(7,11);
        });
    </script>
    @endpush
</x-app-layout>
