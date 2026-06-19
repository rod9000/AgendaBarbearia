<!DOCTYPE html>
<html lang="pt-BR" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="() => { if (dark) { document.documentElement.classList.add('dark'); } $watch('dark', val => { localStorage.setItem('dark', val); document.documentElement.classList.toggle('dark', val); }); }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reagendar Horário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') . '?nocache=' . env('APP_VERSION', '1.0') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { scrollbar-width: thin; scrollbar-color: #627D98 transparent; }
        input, select { transition: all 0.2s; }
        .card-hover { transition: all 0.25s ease; }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -8px rgba(0,0,0,0.1); }
        .card-hover.selected { transform: translateY(-3px); box-shadow: 0 0 0 2px #486585, 0 12px 25px -8px rgba(36,59,83,0.2); }
        .time-btn { transition: all 0.2s ease; }
        .time-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(36,59,83,0.2); }
        .time-btn.selected { transform: translateY(-2px); box-shadow: 0 0 0 2px #486585, 0 4px 12px rgba(36,59,83,0.15); }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up { animation: fadeUp 0.4s ease-out; }
        .skeleton { background: linear-gradient(90deg, #D9E2EC 25%, #BCCCDC 50%, #D9E2EC 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 8px; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .time-grid { display: grid; gap: 0.5rem; }
        @media (max-width: 640px) { .time-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 641px) { .time-grid { grid-template-columns: repeat(4, 1fr); } }
    </style>
</head>
<body class="bg-gradient-to-br from-stone-50 via-brand-50 to-stone-100 min-h-screen font-sans text-stone-800 dark:from-stone-900 dark:via-stone-900 dark:to-stone-800 dark:text-stone-200">

    <div class="min-h-screen flex flex-col">
        <header class="pt-8 pb-4 px-4 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl shadow-lg mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-stone-800 dark:text-stone-100">Reagendar Horário</h1>
            <p class="text-stone-500 dark:text-stone-400 text-sm mt-1">Escolha uma nova data e horário para seu atendimento</p>
        </header>

        <main class="flex-1 px-4 pb-12">
            <div class="max-w-xl mx-auto">

                {{-- Current appointment info --}}
                <div class="bg-white/80 dark:bg-stone-800/80 backdrop-blur-md rounded-3xl shadow-xl border border-white/60 dark:border-stone-700/50 p-6 mb-6">
                    <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-3">Agendamento Atual</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-stone-400 w-20 shrink-0">Cliente:</span>
                            <span class="font-medium text-stone-800 dark:text-stone-100">{{ $appointment->customer->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-stone-400 w-20 shrink-0">Profissional:</span>
                            <span class="font-medium text-stone-800 dark:text-stone-100">{{ $appointment->user->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-stone-400 w-20 shrink-0">Serviço(s):</span>
                            <span class="font-medium text-stone-800 dark:text-stone-100">{{ $appointment->services->pluck('name')->implode(', ') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-stone-400 w-20 shrink-0">Data/Hora:</span>
                            <span class="font-medium text-stone-800 dark:text-stone-100">{{ $appointment->start->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Reschedule form --}}
                <div class="bg-white/80 dark:bg-stone-800/80 backdrop-blur-md rounded-3xl shadow-xl border border-white/60 dark:border-stone-700/50 p-6 sm:p-8 fade-up">
                    <div id="successMsg" class="hidden bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl mb-6 text-sm text-center"></div>
                    <div id="errorMsg" class="hidden bg-rose-100 dark:bg-rose-900/30 border border-rose-300 dark:border-rose-700 text-rose-700 dark:text-rose-300 px-4 py-3 rounded-xl mb-6 text-sm text-center"></div>

                    <form id="rescheduleForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ $appointment->confirmation_token }}">

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Profissional</label>
                                <select name="user_id" id="resUser" class="w-full rounded-xl border-2 border-stone-200 dark:border-stone-600 bg-white/80 dark:bg-stone-700 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-3 text-sm dark:text-stone-200">
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" @selected($u->id == $appointment->user_id)>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Serviços</label>
                                <div class="grid grid-cols-1 gap-2" id="servicesGrid">
                                    @foreach($services as $s)
                                    <div class="service-card card-hover relative bg-white dark:bg-stone-700 rounded-xl border-2 border-stone-100 dark:border-stone-600 p-3 cursor-pointer {{ $appointment->services->contains($s->id) ? 'selected border-brand-300 bg-brand-50/30' : '' }}" data-value="{{ $s->id }}" onclick="toggleService(this)">
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
                                            <div class="check-icon {{ $appointment->services->contains($s->id) ? '' : 'hidden' }} w-6 h-6 bg-brand-500 rounded-full flex items-center justify-center shrink-0">
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div id="serviceIdsContainer">
                                    @foreach($appointment->services as $s)
                                    <input type="hidden" name="service_ids[]" value="{{ $s->id }}">
                                    @endforeach
                                </div>
                                <p id="selectedServicesCount" class="text-xs text-stone-400 dark:text-stone-500 mt-2">{{ $appointment->services->count() }} serviço(s) selecionado(s)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Nova Data</label>
                                <div class="flex gap-2 mb-3 flex-wrap" id="quickDates"></div>
                                <input type="text" id="dateSelect" required placeholder="dd/mm/aaaa" inputmode="numeric" autocomplete="off" class="w-full rounded-xl border-2 border-stone-200 dark:border-stone-600 bg-white/80 dark:bg-stone-700 shadow-sm focus:border-brand-400 focus:ring focus:ring-brand-200/30 p-3 text-sm dark:text-stone-200">
                            </div>

                            <div>
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

                            <button type="submit" id="submitBtn" disabled class="w-full bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm">
                                Selecione um horário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <footer class="pb-8 px-4 text-center">
            <div class="max-w-xl mx-auto flex items-center justify-center gap-6 text-xs text-stone-400 dark:text-stone-500">
                <span>&copy; {{ date('Y') }} Barbearia</span>
                <span class="w-1 h-1 rounded-full bg-stone-300 dark:bg-stone-600"></span>
                <span>Reagendamento Online</span>
            </div>
        </footer>
    </div>

    <script>
    var selectedServices = [];
    var selectedDate = null;
    var selectedTime = null;

    // Initialize selected services
    document.querySelectorAll('.service-card.selected').forEach(function(el) {
        selectedServices.push(el.dataset.value);
    });

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
        updateSubmitBtn();
        if (selectedDate && count > 0) loadSlots();
    }

    function selectTime(el) {
        document.querySelectorAll('.time-btn').forEach(function(b) {
            b.classList.remove('selected', 'border-brand-400', 'bg-brand-50', 'text-brand-700', 'ring-2', 'ring-brand-300');
        });
        el.classList.add('selected', 'border-brand-400', 'bg-brand-50', 'text-brand-700', 'ring-2', 'ring-brand-300');
        selectedTime = el.dataset.value;
        document.getElementById('timeInput').value = selectedTime;
        updateSubmitBtn();
    }

    function updateSubmitBtn() {
        var btn = document.getElementById('submitBtn');
        if (selectedServices.length > 0 && selectedDate && selectedTime) {
            btn.disabled = false;
            btn.className = 'w-full bg-gradient-to-r from-brand-400 to-brand-600 text-white font-semibold rounded-xl py-3 hover:from-brand-500 hover:to-brand-700 transition-all duration-200 shadow-sm text-sm cursor-pointer';
            btn.textContent = 'Confirmar Reagendamento';
        } else {
            btn.disabled = true;
            btn.className = 'w-full bg-stone-200 dark:bg-stone-600 text-stone-400 dark:text-stone-500 font-semibold rounded-xl py-3 cursor-not-allowed transition-all duration-200 text-sm';
            btn.textContent = selectedServices.length === 0 ? 'Selecione ao menos um serviço' : (!selectedDate ? 'Selecione uma data' : 'Selecione um horário');
        }
    }

    function loadSlots() {
        var userId = document.getElementById('resUser').value;
        var serviceId = selectedServices.length > 0 ? selectedServices[0] : '';
        var dateInput = document.getElementById('dateSelect');
        var date = dateInput.dataset.iso || dateInput.value;

        if (!userId || !serviceId || !date) return;

        var grid = document.getElementById('timeSlotsGrid');
        var loading = document.getElementById('loadingSlots');

        selectedTime = null;
        document.getElementById('timeInput').value = '';
        updateSubmitBtn();

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

    document.getElementById('resUser').addEventListener('change', function() {
        if (selectedDate && selectedServices.length > 0) loadSlots();
    });

    document.addEventListener('DOMContentLoaded', function() {
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
            updateSubmitBtn();
            loadSlots();
        });

        dateInput.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '');
            if (v.length <= 2) this.value = v;
            else if (v.length <= 4) this.value = v.slice(0,2) + '/' + v.slice(2);
            else this.value = v.slice(0,2) + '/' + v.slice(2,4) + '/' + v.slice(4,8);
            if (v.length === 8) {
                var iso = v.slice(4,8) + '-' + v.slice(2,4) + '-' + v.slice(0,2);
                this.dataset.iso = iso;
                selectedDate = iso;
                updateSubmitBtn();
                loadSlots();
            } else {
                this.dataset.iso = '';
            }
        });
    });

    document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!selectedServices.length || !selectedDate || !selectedTime) return;

        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Reagendando...';

        var token = document.querySelector('input[name="token"]').value;
        var formData = new FormData();
        formData.append('user_id', document.getElementById('resUser').value);
        formData.append('date', selectedDate);
        formData.append('time', selectedTime);
        selectedServices.forEach(function(sid) {
            formData.append('service_ids[]', sid);
        });

        document.getElementById('errorMsg').classList.add('hidden');
        document.getElementById('successMsg').classList.add('hidden');

        fetch('/reagendar/' + token, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('successMsg').textContent = 'Horário reagendado com sucesso! Em breve você receberá uma confirmação no WhatsApp.';
                document.getElementById('successMsg').classList.remove('hidden');
                btn.textContent = 'Reagendado!';
                btn.className = 'w-full bg-emerald-500 text-white font-semibold rounded-xl py-3 text-sm';
            } else {
                document.getElementById('errorMsg').textContent = data.message || 'Erro ao reagendar. Tente novamente.';
                document.getElementById('errorMsg').classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Confirmar Reagendamento';
            }
        })
        .catch(function() {
            document.getElementById('errorMsg').textContent = 'Erro ao conectar. Tente novamente.';
            document.getElementById('errorMsg').classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Confirmar Reagendamento';
        });
    });
    </script>
</body>
</html>
