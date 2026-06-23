<!DOCTYPE html>
<html lang="pt-BR" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="() => { if (dark) { document.documentElement.classList.add('dark'); } $watch('dark', val => { localStorage.setItem('dark', val); document.documentElement.classList.toggle('dark', val); }); }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agende seu Horário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') . '?nocache=' . env('APP_VERSION', '1.0') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { scrollbar-width: thin; scrollbar-color: #627D98 transparent; }
        input, select { transition: all 0.2s; }
        .step-enter { animation: fadeUp 0.4s ease-out; }
        .card-hover { transition: all 0.25s ease; }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -8px rgba(0,0,0,0.1); }
        .card-hover.selected { transform: translateY(-3px); box-shadow: 0 0 0 2px #486585, 0 12px 25px -8px rgba(36,59,83,0.2); }
        .time-btn { transition: all 0.2s ease; }
        .time-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(36,59,83,0.2); }
        .time-btn.selected { transform: translateY(-2px); box-shadow: 0 0 0 2px #486585, 0 4px 12px rgba(36,59,83,0.15); }
        .skeleton { background: linear-gradient(90deg, #D9E2EC 25%, #BCCCDC 50%, #D9E2EC 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 8px; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .confetti-piece { position: fixed; width: 10px; height: 10px; border-radius: 2px; animation: confettiFall 3s ease-out forwards; pointer-events: none; z-index: 9999; }
        @keyframes confettiFall { 0% { opacity: 1; transform: translateY(-100vh) rotate(0deg); } 100% { opacity: 0; transform: translateY(100vh) rotate(720deg); } }
        .time-grid { display: grid; gap: 0.5rem; }
        @media (max-width: 640px) { .time-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 641px) { .time-grid { grid-template-columns: repeat(4, 1fr); } }
        .search-result { transition: all 0.2s; }
        .search-result:hover { transform: translateX(4px); }
    </style>
</head>
<body class="bg-gradient-to-br from-stone-50 via-brand-50 to-stone-100 min-h-screen font-sans text-stone-800 dark:from-stone-900 dark:via-stone-900 dark:to-stone-800 dark:text-stone-200">

    @if($companyWhatsapp)
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $companyWhatsapp) }}" target="_blank"
       style="position:fixed;bottom:20px;right:20px;z-index:999;width:56px;height:56px;background-color:#22c55e;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 15px -3px rgba(0,0,0,0.2);transition:all 0.2s;"
       onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"
       title="Fale conosco pelo WhatsApp">
        <svg style="width:28px;height:28px;fill:white" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>
    @endif

    @if(session('success'))
    <div id="successOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white dark:bg-stone-800 rounded-3xl shadow-2xl max-w-md w-full p-8 text-center animate-bounce-in">
            <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-stone-800 dark:text-stone-100 mb-2">Agendamento Confirmado!</h2>
            <p class="text-stone-500 dark:text-stone-400 mb-6">{{ session('success') }}</p>
            <a href="{{ route('public.booking') }}" class="inline-block w-full bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-md hover:shadow-lg">
                Fazer Novo Agendamento
            </a>
        </div>
    </div>
    <script>
        (function() {
            var colors = ['#f43f5e','#fb923c','#fbbf24','#34d399','#60a5fa','#a78bfa','#f472b6'];
            for (var i = 0; i < 60; i++) {
                var el = document.createElement('div');
                el.className = 'confetti-piece';
                el.style.left = Math.random() * 100 + '%';
                el.style.top = '-10px';
                el.style.background = colors[Math.floor(Math.random() * colors.length)];
                el.style.width = (Math.random() * 8 + 4) + 'px';
                el.style.height = (Math.random() * 8 + 4) + 'px';
                el.style.animationDuration = (Math.random() * 2 + 2) + 's';
                el.style.animationDelay = (Math.random() * 1.5) + 's';
                document.body.appendChild(el);
            }
        })();
    </script>
    @endif

    <div class="min-h-screen flex flex-col">
        <header class="pt-8 pb-4 px-4 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-4">
                <img src="{{ asset('images/barber-logo.png') }}?v={{ env('APP_VERSION', '1.0') }}" alt="Barbearia" class="w-20 h-20 object-contain">
            </div>
            <h1 class="text-2xl font-bold text-stone-800 dark:text-stone-100">Agende seu Horário</h1>
            <p class="text-stone-500 dark:text-stone-400 text-sm mt-1">Escolha o serviço, profissional e melhor horário para você</p>
        </header>

        <main class="flex-1 px-4 pb-12">
            <div class="max-w-xl mx-auto">
                <form id="bookingForm" method="POST" action="{{ route('public.booking.store') }}">
                    @csrf
                    <input type="hidden" name="customer_id" id="customer_id" value="">

                    <div class="bg-white/80 dark:bg-stone-800/80 backdrop-blur-md rounded-3xl shadow-xl border border-white/60 dark:border-stone-700/50 p-6 sm:p-8">

                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-1">
                                <div class="step-dot" data-step="1"><div class="w-8 h-8 rounded-full bg-brand-500 text-white flex items-center justify-center text-xs font-bold">1</div></div>
                                <div class="step-line" data-step="1"><div class="h-0.5 w-8 bg-brand-300"></div></div>
                                <div class="step-dot" data-step="2"><div class="w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-600 text-stone-500 dark:text-stone-300 flex items-center justify-center text-xs font-bold">2</div></div>
                                <div class="step-line" data-step="2"><div class="h-0.5 w-8 bg-stone-200 dark:bg-stone-600"></div></div>
                                <div class="step-dot" data-step="3"><div class="w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-600 text-stone-500 dark:text-stone-300 flex items-center justify-center text-xs font-bold">3</div></div>
                            </div>
                            <span id="stepLabel" class="text-xs font-medium text-stone-400">Cliente</span>
                        </div>

                        {{-- Error summary --}}
                        <div id="formErrorSummary" class="hidden mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                            <ul id="formErrorList" class="text-sm text-red-600 dark:text-red-400 space-y-1"></ul>
                        </div>

                        @if($errors->any())
                        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                            <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                                @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        {{-- STEP 1: Buscar ou Cadastrar Cliente --}}
                        <div id="step1" class="step-content step-enter">
                            <h2 class="text-lg font-semibold text-stone-800 dark:text-stone-100 mb-1">Identifique-se</h2>
                            <p class="text-sm text-stone-400 dark:text-stone-500 mb-5">Informe seu nome e CPF para continuar</p>

                            <div id="step1Error" class="hidden mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3">
                                <p id="step1ErrorText" class="text-sm text-red-600 dark:text-red-400"></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nome Completo</label>
                                    <input type="text" id="step1Name" class="w-full rounded-xl border-2 border-stone-200 dark:border-stone-600 bg-white/80 dark:bg-stone-700 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-3 text-sm transition-all dark:text-stone-200" placeholder="Digite seu nome">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">CPF</label>
                                    <input type="text" id="step1Cpf" class="w-full rounded-xl border-2 border-stone-200 dark:border-stone-600 bg-white/80 dark:bg-stone-700 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-3 text-sm transition-all dark:text-stone-200" placeholder="000.000.000-00">
                                </div>
                                <div id="step1PhoneGroup">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Telefone <span class="text-xs text-stone-400">(opcional)</span></label>
                                    <input type="text" id="step1Phone" class="w-full rounded-xl border-2 border-stone-200 dark:border-stone-600 bg-white/80 dark:bg-stone-700 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-3 text-sm transition-all dark:text-stone-200" placeholder="(11) 99999-8888">
                                </div>
                            </div>

                            <div id="step1Spinner" class="hidden mt-4 text-center">
                                <svg class="animate-spin w-6 h-6 text-brand-500 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            </div>

                            <div id="step1Welcome" class="hidden mt-6 p-6 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-2xl border border-emerald-200 dark:border-emerald-800 text-center">
                                <div class="w-16 h-16 rounded-full bg-emerald-200 dark:bg-emerald-800/40 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <h3 class="text-xl font-bold text-emerald-800 dark:text-emerald-200 mb-1">Bem-vindo(a), <span id="welcomeName"></span>!</h3>
                                <p class="text-sm text-emerald-600 dark:text-emerald-400" id="welcomeCpf"></p>
                            </div>

                            <button type="button" onclick="buscarCliente()" id="step1Btn" class="mt-6 w-full bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm">
                                Buscar
                            </button>

                            <button type="button" onclick="nextStep(2)" id="step1Next" class="mt-3 w-full bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm hidden">
                                Continuar
                            </button>
                        </div>

                        {{-- STEP 2: Selecionar Serviço + Profissional (agrupado) --}}
                        <div id="step2" class="step-content hidden">
                            <h2 class="text-lg font-semibold text-stone-800 dark:text-stone-100 mb-1">Escolha os Serviços e Profissionais</h2>
                            <p class="text-sm text-stone-400 dark:text-stone-500 mb-5">Selecione os serviços e para cada um escolha o profissional</p>

                            @php $grouped = collect($combos)->groupBy('service_id'); @endphp

                            <div class="space-y-3" id="servicesGrouped">
                                @foreach($grouped as $serviceId => $comboGroup)
                                @php $first = $comboGroup->first(); @endphp
                                <div class="bg-white dark:bg-stone-700 rounded-xl border-2 border-stone-100 dark:border-stone-600 p-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: {{ $first->color_hex }}22">
                                            <span class="w-4 h-4 rounded-full" style="background: {{ $first->color_hex }}"></span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-stone-800 dark:text-stone-100 text-sm">{{ $first->service_name }}</div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs text-stone-400 dark:text-stone-500">{{ $first->duration_min }} min</span>
                                                <span class="text-xs text-stone-300 dark:text-stone-600">•</span>
                                                <span class="text-xs font-medium text-brand-600 dark:text-brand-400">R$ {{ number_format($first->price, 2, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($comboGroup as $combo)
                                        <div class="attendant-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border-2 cursor-pointer transition-all bg-white dark:bg-stone-600 border-stone-200 dark:border-stone-500 text-stone-600 dark:text-stone-300 hover:border-brand-300 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20"
                                             data-service-id="{{ $combo->service_id }}" data-user-id="{{ $combo->user_id }}" onclick="toggleAttendant(this)">
                                            <span class="check-icon hidden w-3.5 h-3.5 rounded-full bg-brand-500 flex items-center justify-center shrink-0">
                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                            {{ $combo->user_name }}
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <p id="selectedCombosCount" class="text-xs text-stone-400 dark:text-stone-500 mt-3">Nenhum item selecionado</p>
                            <div id="comboIdsContainer"></div>
                            <div class="flex gap-3 mt-6">
                                <button type="button" onclick="prevStep(1)" class="flex-1 bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300 font-medium rounded-xl py-3 hover:bg-stone-200 dark:hover:bg-stone-600 transition-all duration-200 text-sm">Voltar</button>
                                <button type="button" onclick="nextStep(3)" disabled id="step2Next" class="flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm">
                                    Selecione ao menos um item
                                </button>
                            </div>
                        </div>

                        {{-- STEP 3: Data e Horario --}}
                        <div id="step3" class="step-content hidden">
                            <h2 class="text-lg font-semibold text-stone-800 dark:text-stone-100 mb-1">Escolha a Data e Horário</h2>
                            <p class="text-sm text-stone-400 dark:text-stone-500 mb-5">Selecione o melhor dia e horário disponível</p>

                            <div class="flex gap-2 mb-4 flex-wrap" id="quickDates"></div>

                            <div class="mb-4">
                                <input type="text" name="date" id="dateSelect" required placeholder="dd/mm/aaaa" inputmode="numeric" autocomplete="off" class="w-full rounded-xl border-2 border-stone-200 dark:border-stone-600 bg-white/80 dark:bg-stone-700 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-3 text-sm dark:text-stone-200">
                            </div>

                            <div id="timeSlotsContainer">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Horários Disponíveis</label>
                                <div id="loadingSlots" class="hidden">
                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                        @for($i=0;$i<8;$i++)
                                        <div class="skeleton h-10"></div>
                                        @endfor
                                    </div>
                                </div>
                                <input type="hidden" name="time" id="timeInput" value="">
                                <div id="timeSlotsGrid" class="time-grid gap-2">
                                    <div class="col-span-full text-center py-8 text-stone-400 dark:text-stone-500 text-sm">
                                        <svg class="w-10 h-10 mx-auto mb-2 text-stone-300 dark:text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Selecione uma data para ver os horários
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 p-4 bg-stone-50 dark:bg-stone-700/50 rounded-xl border border-stone-100 dark:border-stone-600" id="summaryBox">
                                <h3 class="text-xs font-semibold text-stone-400 dark:text-stone-500 uppercase tracking-wider mb-3">Resumo do Agendamento</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2"><span class="text-stone-400 dark:text-stone-500 w-20 shrink-0">Cliente:</span><span class="font-medium text-stone-800 dark:text-stone-100" id="resumoCliente">—</span></div>
                                    <div class="flex items-center gap-2"><span class="text-stone-400 dark:text-stone-500 w-20 shrink-0">Serviço(s):</span><span class="font-medium text-stone-800 dark:text-stone-100" id="resumoServico">—</span></div>
                                    <div class="flex items-center gap-2"><span class="text-stone-400 dark:text-stone-500 w-20 shrink-0">Data/Hora:</span><span class="font-medium text-stone-800 dark:text-stone-100" id="resumoData">—</span></div>
                                </div>
                            </div>

                            <div class="flex gap-3 mt-6">
                                <button type="button" onclick="prevStep(2)" class="flex-1 bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300 font-medium rounded-xl py-3 hover:bg-stone-200 dark:hover:bg-stone-600 transition-all duration-200 text-sm">Voltar</button>
                                <button type="submit" disabled id="step3Submit" class="flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm">
                                    Selecione um horário
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </main>

        <footer class="pb-8 px-4 text-center">
            <div class="max-w-xl mx-auto flex items-center justify-center gap-6 text-xs text-stone-400 dark:text-stone-500">
                <span>© {{ date('Y') }} Barbearia</span>
                <span class="w-1 h-1 rounded-full bg-stone-300 dark:bg-stone-600"></span>
                <span>Agenda Online</span>
            </div>
        </footer>
    </div>

    <script>
    var STORAGE_KEY = 'booking_state';
    var stepLabels = ['Cliente', 'Serviço e Profissional', 'Data e Horário'];
    var currentStep = 1;
    var selectedCustomerId = null;
    var selectedCombos = [];
    var selectedDate = null;
    var selectedTime = null;

    function saveState() {
        try {
            var state = JSON.stringify({
                customerId: selectedCustomerId,
                customerName: (document.getElementById('welcomeName') || {}).textContent || '',
                customerCpf: (document.getElementById('welcomeCpf') || {}).textContent || '',
                combos: selectedCombos,
                date: selectedDate,
                time: selectedTime,
                step: currentStep
            });
            sessionStorage.setItem(STORAGE_KEY, state);
        } catch(e) {}
    }

    function clearState() {
        try { sessionStorage.removeItem(STORAGE_KEY); } catch(e) {}
    }

    function updateSteps(step) {
        currentStep = step;
        document.querySelectorAll('.step-content').forEach(function(el) { el.classList.add('hidden'); });
        var el = document.getElementById('step' + step);
        el.classList.remove('hidden');
        el.classList.add('step-enter');
        document.getElementById('stepLabel').textContent = stepLabels[step - 1];

        document.querySelectorAll('.step-dot').forEach(function(dot, i) {
            var num = i + 1;
            var circle = dot.querySelector('div');
            var line = document.querySelector('.step-line[data-step="' + num + '"] div');
            if (num < step) {
                circle.className = 'w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold';
                circle.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
                if (line) line.className = 'h-0.5 w-8 bg-emerald-400';
            } else if (num === step) {
                circle.className = 'w-8 h-8 rounded-full bg-brand-500 text-white flex items-center justify-center text-xs font-bold';
                circle.textContent = num;
                if (line) line.className = 'h-0.5 w-8 bg-brand-300';
            } else {
                circle.className = 'w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-600 text-stone-500 dark:text-stone-300 flex items-center justify-center text-xs font-bold';
                circle.textContent = num;
                if (line) line.className = 'h-0.5 w-8 bg-stone-200 dark:bg-stone-600';
            }
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
        saveState();
    }

    function nextStep(step) { updateSteps(step); }
    function prevStep(step) { updateSteps(step); }

    // --- Step 1: Buscar cliente por Nome + CPF ---
    function showErrorMsg(id, msg) {
        var el = document.getElementById(id);
        if (el) {
            el.querySelector('p').textContent = msg;
            el.classList.remove('hidden');
        }
    }

    function buscarCliente() {
        var name = document.getElementById('step1Name').value.trim();
        var cpf = document.getElementById('step1Cpf').value.trim();
        var phone = document.getElementById('step1Phone').value.trim();
        var btn = document.getElementById('step1Btn');
        var spinner = document.getElementById('step1Spinner');
        var welcome = document.getElementById('step1Welcome');
        var nextBtn = document.getElementById('step1Next');

        document.getElementById('step1Error').classList.add('hidden');

        if (!name || name.length < 3) { showErrorMsg('step1Error', 'Informe o nome completo'); return; }
        if (!cpf || cpf.length < 3) { showErrorMsg('step1Error', 'Informe o CPF'); return; }

        btn.disabled = true;
        btn.textContent = 'Buscando...';
        spinner.classList.remove('hidden');
        welcome.classList.add('hidden');
        nextBtn.classList.add('hidden');

        fetch('/agendar/buscar-cliente?q=' + encodeURIComponent(cpf.replace(/\D/g, '')))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                spinner.classList.add('hidden');

                var found = null;
                if (data.customers && data.customers.length > 0) {
                    for (var i = 0; i < data.customers.length; i++) {
                        var cpfClean = cpf.replace(/\D/g, '');
                        var cpfDb = (data.customers[i].cpf || '').replace(/\D/g, '');
                        if (cpfClean === cpfDb) {
                            found = data.customers[i];
                            break;
                        }
                    }
                }

                if (found) {
                    showWelcome(found, name, cpf, btn);
                } else {
                    fetch('/agendar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                        body: 'action=register_customer&name=' + encodeURIComponent(name) + '&cpf=' + encodeURIComponent(cpf) + '&phone=' + encodeURIComponent(phone) + '&email='
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data2) {
                        if (data2.success) {
                            showWelcome(data2.customer, name, cpf, btn);
                        } else {
                            showErrorMsg('step1Error', data2.message || 'Erro ao cadastrar. Tente novamente.');
                            btn.disabled = false;
                            btn.textContent = 'Buscar';
                        }
                    })
                    .catch(function() {
                        showErrorMsg('step1Error', 'Erro ao cadastrar. Tente novamente.');
                        btn.disabled = false;
                        btn.textContent = 'Buscar';
                    });
                }
            })
            .catch(function() {
                spinner.classList.add('hidden');
                showErrorMsg('step1Error', 'Erro ao buscar cliente. Tente novamente.');
                btn.disabled = false;
                btn.textContent = 'Buscar';
            });
    }

    function showWelcome(customer, name, cpf, btn) {
        selectedCustomerId = customer.id;
        document.getElementById('customer_id').value = customer.id;
        document.getElementById('welcomeName').textContent = customer.name;
        document.getElementById('welcomeCpf').textContent = 'CPF: ' + (customer.cpf || cpf);
        document.getElementById('step1Welcome').classList.remove('hidden');
        var nextBtn = document.getElementById('step1Next');
        nextBtn.classList.remove('hidden');
        nextBtn.disabled = false;
        nextBtn.className = 'mt-3 w-full bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
        nextBtn.textContent = 'Continuar';
        btn.classList.add('hidden');
        saveState();
    }

    // --- Step 2: Attendant chip toggle (Serviço + Profissional) ---
    function toggleAttendant(el) {
        var serviceId = el.dataset.serviceId;
        var userId = el.dataset.userId;
        var key = serviceId + '_' + userId;

        // Deselect all other chips in the same service card
        var siblings = el.closest('.rounded-xl').querySelectorAll('.attendant-chip[data-service-id="' + serviceId + '"]');
        siblings.forEach(function(sib) {
            var sibKey = sib.dataset.serviceId + '_' + sib.dataset.userId;
            var sibIdx = selectedCombos.indexOf(sibKey);
            if (sibKey !== key && sibIdx !== -1) {
                selectedCombos.splice(sibIdx, 1);
                sib.classList.remove('border-brand-300', 'bg-brand-50/30', 'text-brand-700');
                sib.querySelector('.check-icon').classList.add('hidden');
            }
        });

        // Toggle clicked chip
        var idx = selectedCombos.indexOf(key);
        if (idx === -1) {
            selectedCombos.push(key);
            el.classList.add('border-brand-300', 'bg-brand-50/30', 'text-brand-700');
            el.querySelector('.check-icon').classList.remove('hidden');
        } else {
            selectedCombos.splice(idx, 1);
            el.classList.remove('border-brand-300', 'bg-brand-50/30', 'text-brand-700');
            el.querySelector('.check-icon').classList.add('hidden');
        }

        var container = document.getElementById('comboIdsContainer');
        container.innerHTML = '';
        selectedCombos.forEach(function(k, i) {
            var parts = k.split('_');
            var s = document.createElement('input');
            s.type = 'hidden';
            s.name = 'combos[' + i + '][service_id]';
            s.value = parts[0];
            container.appendChild(s);
            var u = document.createElement('input');
            u.type = 'hidden';
            u.name = 'combos[' + i + '][user_id]';
            u.value = parts[1];
            container.appendChild(u);
        });

        var count = selectedCombos.length;
        document.getElementById('selectedCombosCount').textContent = count === 0 ? 'Nenhum item selecionado' : count + ' item(ns) selecionado(s)';

        var btn = document.getElementById('step2Next');
        if (count > 0) {
            btn.disabled = false;
            btn.className = 'flex-1 bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
            btn.textContent = 'Continuar (' + count + ')';
        } else {
            btn.disabled = true;
            btn.className = 'flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm';
            btn.textContent = 'Selecione ao menos um item';
        }
        updateResumo();
        saveState();
        if (selectedDate && count > 0) loadSlots();
    }

    // --- Step 3: Date & Time ---
    function selectTime(el) {
        document.querySelectorAll('.time-btn').forEach(function(b) {
            b.classList.remove('selected', 'border-brand-400', 'bg-brand-50', 'text-brand-700', 'ring-2', 'ring-brand-300');
        });
        el.classList.add('selected', 'border-brand-400', 'bg-brand-50', 'text-brand-700', 'ring-2', 'ring-brand-300');
        selectedTime = el.dataset.value;
        document.getElementById('timeInput').value = selectedTime;
        updateResumo();
        saveState();
        var btn = document.getElementById('step3Submit');
        btn.disabled = false;
        btn.className = 'flex-1 bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
        btn.textContent = 'Confirmar Agendamento';
    }

    function loadSlots() {
        if (selectedCombos.length === 0) return;
        var dateInput = document.getElementById('dateSelect');
        var date = dateInput.dataset.iso || dateInput.value;
        if (!date) return;

        // Collect unique user_ids and service_ids from all combos
        var userIds = [];
        var serviceIds = [];
        selectedCombos.forEach(function(key) {
            var parts = key.split('_');
            if (userIds.indexOf(parts[1]) === -1) userIds.push(parts[1]);
            if (serviceIds.indexOf(parts[0]) === -1) serviceIds.push(parts[0]);
        });

        if (userIds.length === 0 || serviceIds.length === 0) return;

        var grid = document.getElementById('timeSlotsGrid');
        var loading = document.getElementById('loadingSlots');
        var btn = document.getElementById('step3Submit');

        selectedTime = null;
        document.getElementById('timeInput').value = '';
        btn.disabled = true;
        btn.className = 'flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm';
        btn.textContent = 'Selecione um horário';
        updateResumo();

        grid.innerHTML = '';
        loading.classList.remove('hidden');

        var params = 'date=' + encodeURIComponent(date);
        userIds.forEach(function(uid) { params += '&user_ids[]=' + encodeURIComponent(uid); });
        serviceIds.forEach(function(sid) { params += '&service_ids[]=' + encodeURIComponent(sid); });

        fetch('/agendar/slots?' + params)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                loading.classList.add('hidden');
                if (data.slots.length === 0) {
                    grid.innerHTML = '<div class="col-span-full text-center py-8 text-stone-400 dark:text-stone-500 text-sm"><svg class="w-10 h-10 mx-auto mb-2 text-stone-300 dark:text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Nenhum horário disponível nesta data</div>';
                } else {
                    data.slots.forEach(function(slot) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'time-btn bg-white dark:bg-stone-700 border-2 border-stone-200 dark:border-stone-600 rounded-xl py-2.5 text-sm font-medium text-stone-700 dark:text-stone-200 hover:border-brand-300';
                        b.dataset.value = slot.time;
                        b.textContent = slot.label;
                        b.onclick = function() { selectTime(b); };
                        grid.appendChild(b);
                    });
                }
            })
            .catch(function() {
                loading.classList.add('hidden');
                grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-400 text-sm">Erro ao carregar horários. Tente novamente.</div>';
            });
    }

    function updateResumo() {
        var comboLabels = [];
        selectedCombos.forEach(function(key) {
            var parts = key.split('_');
            var chip = document.querySelector('.attendant-chip[data-service-id="' + parts[0] + '"][data-user-id="' + parts[1] + '"]');
            if (chip) {
                var parent = chip.closest('.rounded-xl');
                if (parent) {
                    var nameEl = parent.querySelector('.font-semibold');
                    var userName = chip.textContent.trim();
                    if (nameEl) comboLabels.push(nameEl.textContent.trim() + ' (' + userName + ')');
                }
            }
        });

        var clientName = '—';
        var clientInfo = document.getElementById('welcomeName');
        if (clientInfo && clientInfo.textContent) clientName = clientInfo.textContent;

        var dateTime = '—';
        if (selectedDate && selectedTime) {
            var d = new Date(selectedDate + 'T12:00:00');
            var months = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
            var days = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
            dateTime = days[d.getDay()] + ', ' + d.getDate() + ' de ' + months[d.getMonth()] + ' às ' + selectedTime;
        }

        document.getElementById('resumoCliente').textContent = clientName;
        document.getElementById('resumoServico').textContent = comboLabels.length > 0 ? comboLabels.join(' + ') : '—';
        document.getElementById('resumoData').textContent = dateTime;
    }

    function restoreFromStorage() {
        var raw;
        try { raw = sessionStorage.getItem(STORAGE_KEY); } catch(e) {}
        if (!raw) return;
        var state;
        try { state = JSON.parse(raw); } catch(e) {}
        if (!state || !state.customerId) return;

        selectedCustomerId = state.customerId;
        document.getElementById('customer_id').value = state.customerId;
        document.getElementById('welcomeName').textContent = state.customerName || '';
        document.getElementById('welcomeCpf').textContent = state.customerCpf || '';
        document.getElementById('step1Welcome').classList.remove('hidden');
        document.getElementById('step1Btn').classList.add('hidden');
        var nextBtn = document.getElementById('step1Next');
        nextBtn.classList.remove('hidden');
        nextBtn.disabled = false;
        nextBtn.className = 'mt-3 w-full bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
        nextBtn.textContent = 'Continuar';

        if (state.combos && state.combos.length > 0) {
            selectedCombos = state.combos;
            selectedCombos.forEach(function(key) {
                var parts = key.split('_');
                var chip = document.querySelector('.attendant-chip[data-service-id="' + parts[0] + '"][data-user-id="' + parts[1] + '"]');
                if (chip) {
                    chip.classList.add('border-brand-300', 'bg-brand-50/30', 'text-brand-700');
                    chip.querySelector('.check-icon').classList.remove('hidden');
                }
            });
            renderCombosHidden();
            var count = selectedCombos.length;
            document.getElementById('selectedCombosCount').textContent = count + ' item(ns) selecionado(s)';
            var btn = document.getElementById('step2Next');
            btn.disabled = false;
            btn.className = 'flex-1 bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
            btn.textContent = 'Continuar (' + count + ')';
        }

        if (state.date) {
            selectedDate = state.date;
            var parts = state.date.split('-');
            if (parts.length === 3) {
                var display = parts[2] + '/' + parts[1] + '/' + parts[0];
                var dateInput = document.getElementById('dateSelect');
                dateInput.value = display;
                dateInput.dataset.iso = state.date;
            }
            setTimeout(function() {
                loadSlots();
                if (state.time) {
                    var timeBtns = document.querySelectorAll('.time-btn');
                    timeBtns.forEach(function(b) {
                        if (b.dataset.value === state.time) {
                            selectTime(b);
                        }
                    });
                }
            }, 100);
        }

        var targetStep = state.step || 1;
        if (targetStep > 1) {
            setTimeout(function() { updateSteps(targetStep); }, 50);
        }
    }

    function renderCombosHidden() {
        var container = document.getElementById('comboIdsContainer');
        container.innerHTML = '';
        selectedCombos.forEach(function(k, i) {
            var parts = k.split('_');
            var s = document.createElement('input');
            s.type = 'hidden';
            s.name = 'combos[' + i + '][service_id]';
            s.value = parts[0];
            container.appendChild(s);
            var u = document.createElement('input');
            u.type = 'hidden';
            u.name = 'combos[' + i + '][user_id]';
            u.value = parts[1];
            container.appendChild(u);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Quick date chips
        var dateInput = document.getElementById('dateSelect');
        var quickDates = document.getElementById('quickDates');
        var today = new Date();

        for (var i = 0; i < 7; i++) {
            var d = new Date(today);
            d.setDate(d.getDate() + i);
            var dayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            var label = i === 0 ? 'Hoje' : i === 1 ? 'Amanhã' : dayNames[d.getDay()];
            var y = d.getFullYear();
            var m = String(d.getMonth() + 1).padStart(2, '0');
            var day = String(d.getDate()).padStart(2, '0');
            var dateStr = y + '-' + m + '-' + day;

            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'px-3 py-1.5 rounded-lg text-xs font-medium border-2 border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-300 hover:border-brand-300 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all';
            chip.dataset.date = day + '/' + m + '/' + y;
            chip.dataset.iso = dateStr;
            chip.textContent = label + ' ' + day + '/' + m;
            chip.onclick = function() {
                document.querySelectorAll('#quickDates button').forEach(function(b) {
                    b.className = 'px-3 py-1.5 rounded-lg text-xs font-medium border-2 border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-300 hover:border-brand-300 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all';
                });
                this.className = 'px-3 py-1.5 rounded-lg text-xs font-medium border-2 border-brand-400 text-brand-700 bg-brand-50 dark:bg-brand-900/30 dark:text-brand-300 transition-all';
                dateInput.value = this.dataset.date;
                dateInput.dispatchEvent(new Event('change'));
            };
            quickDates.appendChild(chip);
        }

        function parseDateBr(str) {
            var parts = (str || '').split('/');
            if (parts.length === 3) return parts[2] + '-' + parts[1] + '-' + parts[0];
            return str;
        }

        dateInput.addEventListener('change', function() {
            if (this.value.length !== 10) return;
            var iso = parseDateBr(this.value);
            this.dataset.iso = iso;
            selectedDate = iso;
            document.querySelectorAll('#quickDates button').forEach(function(b) {
                if (b.dataset.date === this.value) {
                    b.className = 'px-3 py-1.5 rounded-lg text-xs font-medium border-2 border-brand-400 text-brand-700 bg-brand-50 dark:bg-brand-900/30 dark:text-brand-300 transition-all';
                } else {
                    b.className = 'px-3 py-1.5 rounded-lg text-xs font-medium border-2 border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-300 hover:border-brand-300 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all';
                }
            }.bind(this));
            updateResumo();
            saveState();
            loadSlots();
        });

        // Date mask dd/mm/aaaa
        dateInput.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '');
            if (v.length <= 2) this.value = v;
            else if (v.length <= 4) this.value = v.slice(0,2) + '/' + v.slice(2);
            else this.value = v.slice(0,2) + '/' + v.slice(2,4) + '/' + v.slice(4,8);
            if (v.length === 8) {
                var iso = v.slice(4,8) + '-' + v.slice(2,4) + '-' + v.slice(0,2);
                this.dataset.iso = iso;
                selectedDate = iso;
                updateResumo();
                saveState();
                loadSlots();
            } else {
                this.dataset.iso = '';
            }
        });

        // Convert to ISO before submit
        document.getElementById('bookingForm').addEventListener('submit', function() {
            var inp = document.getElementById('dateSelect');
            if (inp.dataset.iso) inp.value = inp.dataset.iso;
            var btn = document.getElementById('step3Submit');
            btn.disabled = true;
            btn.textContent = 'Confirmando...';
            clearState();
        });

        // CPF mask on Step 1
        document.getElementById('step1Cpf').addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '');
            if (v.length <= 3) this.value = v;
            else if (v.length <= 6) this.value = v.slice(0,3) + '.' + v.slice(3);
            else if (v.length <= 9) this.value = v.slice(0,3) + '.' + v.slice(3,6) + '.' + v.slice(6);
            else this.value = v.slice(0,3) + '.' + v.slice(3,6) + '.' + v.slice(6,9) + '-' + v.slice(9,11);
        });

        // Phone mask on Step 1
        document.getElementById('step1Phone').addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '');
            if (v.length <= 2) this.value = '(' + v;
            else if (v.length <= 7) this.value = '(' + v.slice(0,2) + ') ' + v.slice(2);
            else this.value = '(' + v.slice(0,2) + ') ' + v.slice(2,7) + '-' + v.slice(7,11);
        });

        // Enter key on Step 1 triggers search
        document.getElementById('step1Cpf').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); buscarCliente(); }
        });
        document.getElementById('step1Name').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); buscarCliente(); }
        });

        // Restore saved state after everything is initialized
        setTimeout(restoreFromStorage, 200);
    });
    </script>
</body>
</html>
