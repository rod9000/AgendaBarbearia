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
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-brand-400 to-brand-600 rounded-2xl shadow-lg mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-stone-800 dark:text-stone-100">Agende seu Horário</h1>
            <p class="text-stone-500 dark:text-stone-400 text-sm mt-1">Escolha o profissional, serviço e melhor horário para você</p>
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
                                <div class="step-line" data-step="3"><div class="h-0.5 w-8 bg-stone-200 dark:bg-stone-600"></div></div>
                                <div class="step-dot" data-step="4"><div class="w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-600 text-stone-500 dark:text-stone-300 flex items-center justify-center text-xs font-bold">4</div></div>
                            </div>
                            <span id="stepLabel" class="text-xs font-medium text-stone-400">Cliente</span>
                        </div>

                        {{-- STEP 1: Buscar ou Cadastrar Cliente --}}
                        <div id="step1" class="step-content step-enter">
                            <h2 class="text-lg font-semibold text-stone-800 dark:text-stone-100 mb-1">Identifique-se</h2>
                            <p class="text-sm text-stone-400 dark:text-stone-500 mb-5">Informe seu nome e CPF para continuar</p>

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

                        {{-- STEP 2: Selecionar Atendente --}}
                        <div id="step2" class="step-content hidden">
                            <h2 class="text-lg font-semibold text-stone-800 dark:text-stone-100 mb-1">Escolha o Barbeiro</h2>
                            <p class="text-sm text-stone-400 dark:text-stone-500 mb-5">Quem você gostaria de atender você?</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="professionalsGrid">
                                @foreach($users as $u)
                                <div class="professional-card card-hover relative bg-white dark:bg-stone-700 rounded-xl border-2 border-stone-100 dark:border-stone-600 p-4 cursor-pointer" data-value="{{ $u->id }}" onclick="selectProfessional(this)">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-brand-100 to-brand-200 dark:from-brand-700 dark:to-brand-800 flex items-center justify-center text-brand-600 dark:text-brand-300 font-bold text-lg shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-stone-800 dark:text-stone-100 text-sm">{{ $u->name }}</div>
                                            <div class="text-xs text-stone-400 dark:text-stone-500">Barbeiro</div>
                                        </div>
                                    </div>
                                    <div class="check-icon hidden absolute top-3 right-3 w-5 h-5 bg-brand-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="user_id" id="user_id" value="">
                            <div class="flex gap-3 mt-6">
                                <button type="button" onclick="prevStep(1)" class="flex-1 bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300 font-medium rounded-xl py-3 hover:bg-stone-200 dark:hover:bg-stone-600 transition-all duration-200 text-sm">Voltar</button>
                                <button type="button" onclick="nextStep(3)" disabled id="step2Next" class="flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm">
                                    Selecione uma atendente
                                </button>
                            </div>
                        </div>

                        {{-- STEP 3: Selecionar Servico --}}
                        <div id="step3" class="step-content hidden">
                            <h2 class="text-lg font-semibold text-stone-800 dark:text-stone-100 mb-1">Escolha os Serviços</h2>
                            <p class="text-sm text-stone-400 dark:text-stone-500 mb-5">Selecione um ou mais procedimentos</p>
                            <div class="grid grid-cols-1 gap-3" id="servicesGrid">
                                @foreach($services as $s)
                                <div class="service-card card-hover relative bg-white dark:bg-stone-700 rounded-xl border-2 border-stone-100 dark:border-stone-600 p-4 cursor-pointer" data-value="{{ $s->id }}" onclick="toggleService(this)">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: {{ $s->color_hex }}22">
                                                <span class="w-4 h-4 rounded-full" style="background: {{ $s->color_hex }}"></span>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-stone-800 dark:text-stone-100 text-sm">{{ $s->name }}</div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs text-stone-400 dark:text-stone-500">{{ $s->duration_min }} min</span>
                                                    <span class="text-xs text-stone-300 dark:text-stone-600">•</span>
                                                    <span class="text-xs font-medium text-brand-600 dark:text-brand-400">R$ {{ number_format($s->price, 2, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check-icon hidden w-6 h-6 bg-brand-500 rounded-full flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <p id="selectedServicesCount" class="text-xs text-stone-400 dark:text-stone-500 mt-2">Nenhum serviço selecionado</p>
                            <div id="serviceIdsContainer"></div>
                            <div class="flex gap-3 mt-6">
                                <button type="button" onclick="prevStep(2)" class="flex-1 bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300 font-medium rounded-xl py-3 hover:bg-stone-200 dark:hover:bg-stone-600 transition-all duration-200 text-sm">Voltar</button>
                                <button type="button" onclick="nextStep(4)" disabled id="step3Next" class="flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm">
                                    Selecione ao menos um serviço
                                </button>
                            </div>
                        </div>

                        {{-- STEP 4: Data e Horario --}}
                        <div id="step4" class="step-content hidden">
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
                                    <div class="flex items-center gap-2"><span class="text-stone-400 dark:text-stone-500 w-20 shrink-0">Barbeiro:</span><span class="font-medium text-stone-800 dark:text-stone-100" id="resumoAtendente">—</span></div>
                                    <div class="flex items-center gap-2"><span class="text-stone-400 dark:text-stone-500 w-20 shrink-0">Serviço:</span><span class="font-medium text-stone-800 dark:text-stone-100" id="resumoServico">—</span></div>
                                    <div class="flex items-center gap-2"><span class="text-stone-400 dark:text-stone-500 w-20 shrink-0">Data/Hora:</span><span class="font-medium text-stone-800 dark:text-stone-100" id="resumoData">—</span></div>
                                </div>
                            </div>

                            <div class="flex gap-3 mt-6">
                                <button type="button" onclick="prevStep(3)" class="flex-1 bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300 font-medium rounded-xl py-3 hover:bg-stone-200 dark:hover:bg-stone-600 transition-all duration-200 text-sm">Voltar</button>
                                <button type="submit" disabled id="step4Submit" class="flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm">
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
    var stepLabels = ['Cliente', 'Barbeiro', 'Serviço', 'Data e Horário'];
    var currentStep = 1;
    var selectedCustomerId = null;
    var selectedProfessional = null;
    var selectedServices = [];
    var selectedDate = null;
    var selectedTime = null;


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
    }

    function nextStep(step) { updateSteps(step); }
    function prevStep(step) { updateSteps(step); }

    // --- Step 1: Buscar cliente por Nome + CPF ---
    function buscarCliente() {
        var name = document.getElementById('step1Name').value.trim();
        var cpf = document.getElementById('step1Cpf').value.trim();
        var phone = document.getElementById('step1Phone').value.trim();
        var btn = document.getElementById('step1Btn');
        var spinner = document.getElementById('step1Spinner');
        var welcome = document.getElementById('step1Welcome');
        var nextBtn = document.getElementById('step1Next');

        if (!name || name.length < 3) { alert('Informe o nome completo'); return; }
        if (!cpf || cpf.length < 3) { alert('Informe o CPF'); return; }

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
                            alert(data2.message || 'Erro ao cadastrar. Tente novamente.');
                            btn.disabled = false;
                            btn.textContent = 'Buscar';
                        }
                    })
                    .catch(function() {
                        alert('Erro ao cadastrar. Tente novamente.');
                        btn.disabled = false;
                        btn.textContent = 'Buscar';
                    });
                }
            })
            .catch(function() {
                spinner.classList.add('hidden');
                alert('Erro ao buscar cliente. Tente novamente.');
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
    }

    // --- Step 2: Professional ---
    function selectProfessional(el) {
        document.querySelectorAll('.professional-card').forEach(function(c) {
            c.classList.remove('selected', 'border-brand-300', 'bg-brand-50/30');
            c.querySelector('.check-icon').classList.add('hidden');
        });
        el.classList.add('selected', 'border-brand-300', 'bg-brand-50/30');
        el.querySelector('.check-icon').classList.remove('hidden');
        selectedProfessional = el.dataset.value;
        document.getElementById('user_id').value = selectedProfessional;
        var btn = document.getElementById('step2Next');
        btn.disabled = false;
        btn.className = 'flex-1 bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
        btn.textContent = 'Continuar';
        updateResumo();
    }

    // --- Step 3: Service (multi-select) ---
    function toggleService(el) {
        var value = el.dataset.value;
        var idx = selectedServices.indexOf(value);
        if (idx === -1) {
            selectedServices.push(value);
            el.classList.add('selected', 'border-brand-300', 'bg-brand-50/30');
            el.querySelector('.check-icon').classList.remove('hidden');
        } else {
            selectedServices.splice(idx, 1);
            el.classList.remove('selected', 'border-brand-300', 'bg-brand-50/30');
            el.querySelector('.check-icon').classList.add('hidden');
        }

        var container = document.getElementById('serviceIdsContainer');
        container.innerHTML = '';
        selectedServices.forEach(function(sid) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'service_ids[]';
            input.value = sid;
            container.appendChild(input);
        });

        var count = selectedServices.length;
        document.getElementById('selectedServicesCount').textContent = count === 0 ? 'Nenhum serviço selecionado' : count + ' serviço(s) selecionado(s)';

        var btn = document.getElementById('step3Next');
        if (count > 0) {
            btn.disabled = false;
            btn.className = 'flex-1 bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
            btn.textContent = 'Continuar (' + count + ')';
        } else {
            btn.disabled = true;
            btn.className = 'flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm';
            btn.textContent = 'Selecione ao menos um serviço';
        }
        updateResumo();
        if (selectedDate && count > 0) loadSlots();
    }

    // --- Step 4: Date & Time ---
    function selectTime(el) {
        document.querySelectorAll('.time-btn').forEach(function(b) {
            b.classList.remove('selected', 'border-brand-400', 'bg-brand-50', 'text-brand-700', 'ring-2', 'ring-brand-300');
        });
        el.classList.add('selected', 'border-brand-400', 'bg-brand-50', 'text-brand-700', 'ring-2', 'ring-brand-300');
        selectedTime = el.dataset.value;
        document.getElementById('timeInput').value = selectedTime;
        updateResumo();
        var btn = document.getElementById('step4Submit');
        btn.disabled = false;
        btn.className = 'flex-1 bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
        btn.textContent = 'Confirmar Agendamento';
    }

    function loadSlots() {
        var userId = document.getElementById('user_id').value;
        var serviceId = selectedServices.length > 0 ? selectedServices[0] : '';
        var dateInput = document.getElementById('dateSelect');
        var date = dateInput.dataset.iso || dateInput.value;

        if (!userId || !serviceId || !date) return;

        var grid = document.getElementById('timeSlotsGrid');
        var loading = document.getElementById('loadingSlots');
        var btn = document.getElementById('step4Submit');

        selectedTime = null;
        document.getElementById('timeInput').value = '';
        btn.disabled = true;
        btn.className = 'flex-1 bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm';
        btn.textContent = 'Selecione um horário';
        updateResumo();

        grid.innerHTML = '';
        loading.classList.remove('hidden');

        fetch('/agendar/slots?user_id=' + userId + '&service_id=' + serviceId + '&date=' + date)
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
        var profName = '—';
        var profSelected = document.querySelector('.professional-card.selected');
        if (profSelected) profName = profSelected.querySelector('.font-semibold').textContent;

        var servName = '—';
        var selectedCards = document.querySelectorAll('.service-card.selected');
        if (selectedCards.length > 0) {
            servName = Array.from(selectedCards).map(function(c) {
                return c.querySelector('.font-semibold').textContent;
            }).join(' + ');
        }

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
        document.getElementById('resumoAtendente').textContent = profName;
        document.getElementById('resumoServico').textContent = servName;
        document.getElementById('resumoData').textContent = dateTime;
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
                loadSlots();
            } else {
                this.dataset.iso = '';
            }
        });

        // Convert to ISO before submit
        document.getElementById('bookingForm').addEventListener('submit', function() {
            var inp = document.getElementById('dateSelect');
            if (inp.dataset.iso) inp.value = inp.dataset.iso;
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
    });
    </script>
</body>
</html>
