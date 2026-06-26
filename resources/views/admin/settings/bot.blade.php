@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Bot WhatsApp</h2>
        <a href="{{ route('admin.settings.evolution') }}" class="btn-pastel-secondary">Evolution API</a>
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

        {{-- Status do Bot --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Status do Bot</h3>
            <div class="flex items-center gap-3 mb-4">
                @if($company->bot_enabled)
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="text-emerald-600 font-medium">Ativo</span>
                @else
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                    <span class="text-red-500 font-medium">Inativo</span>
                @endif
            </div>

            <h4 class="text-sm font-medium text-stone-600 mb-2">Estados da Conversa</h4>
            <p class="text-xs text-stone-400 mb-3">Fluxo de atendimento do bot no WhatsApp.</p>

            @php
                $states = [
                    'initial' => ['label' => 'Menu Inicial', 'color' => 'bg-stone-500', 'desc' => 'Aguardando opção do menu (1-9)'],
                    'choosing_service' => ['label' => 'Escolhendo Serviço', 'color' => 'bg-blue-500', 'desc' => 'Cliente seleciona o serviço'],
                    'choosing_professional' => ['label' => 'Escolhendo Profissional', 'color' => 'bg-blue-500', 'desc' => 'Cliente seleciona o barbeiro'],
                    'choosing_date' => ['label' => 'Escolhendo Data', 'color' => 'bg-blue-500', 'desc' => 'Cliente informa a data (DD/MM)'],
                    'choosing_time' => ['label' => 'Escolhendo Horário', 'color' => 'bg-blue-500', 'desc' => 'Cliente seleciona o horário disponível'],
                    'confirming' => ['label' => 'Confirmando', 'color' => 'bg-amber-500', 'desc' => 'Resumo do agendamento - confirmar ou cancelar'],
                    'consulting_appointments' => ['label' => 'Consultando Agendamentos', 'color' => 'bg-purple-500', 'desc' => 'Lista de agendamentos do cliente'],
                    'cancelling' => ['label' => 'Cancelando', 'color' => 'bg-red-500', 'desc' => 'Cliente escolhe qual agendamento cancelar'],
                ];

                $activeStates = \App\Models\Conversation::where('company_id', $company->id)
                    ->where('state', '!=', 'initial')
                    ->where('last_message_at', '>=', now()->subMinutes($company->bot_response_delay_minutes ?? 60))
                    ->groupBy('state')
                    ->selectRaw('state, count(*) as total')
                    ->pluck('total', 'state')
                    ->toArray();
            @endphp

            <div class="space-y-2">
                @foreach($states as $key => $state)
                    <div class="flex items-center gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <span class="w-3 h-3 rounded-full {{ $state['color'] }} inline-block shrink-0"></span>
                        <div class="flex-1">
                            <p class="font-medium text-stone-800 dark:text-stone-200 text-sm">{{ $state['label'] }}</p>
                            <p class="text-xs text-stone-500">{{ $state['desc'] }}</p>
                        </div>
                        @if(isset($activeStates[$key]) && $activeStates[$key] > 0)
                            <span class="px-2 py-0.5 bg-brand-100 text-brand-700 text-xs rounded-full font-medium">
                                {{ $activeStates[$key] }} {{ Str::plural('cliente', $activeStates[$key]) }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Configurações --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Configurações do Bot</h3>
            <form method="POST" action="{{ route('admin.settings.bot.store') }}">
                @csrf
                <div class="space-y-6">

                    {{-- Toggle Bot --}}
                    <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <div>
                            <p class="font-medium text-stone-700 dark:text-stone-300">Habilitar Bot</p>
                            <p class="text-sm text-stone-500 dark:text-stone-400">Ativa o atendimento automático via WhatsApp</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="bot_enabled" value="1" {{ $company->bot_enabled ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-stone-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    {{-- Tempo de Resposta --}}
                    <div>
                        <label class="label">Timeout da Sessão (minutos)</label>
                        <p class="text-xs text-stone-500 mb-2">Tempo sem atividade para o bot resetar a conversa e voltar ao menu inicial.</p>
                        <div class="flex items-center gap-3">
                            <input type="number" name="bot_response_delay_minutes" value="{{ old('bot_response_delay_minutes', $company->bot_response_delay_minutes ?? 60) }}" min="0" max="1440" class="input-pastel w-32" required>
                            <span class="text-sm text-stone-500">minutos</span>
                        </div>
                        <p class="text-xs text-stone-400 mt-1">0 = sem timeout | 60 = 1 hora | 1440 = 24 horas</p>
                        @error('bot_response_delay_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Toggle Horário Comercial --}}
                    <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <div>
                            <p class="font-medium text-stone-700 dark:text-stone-300">Respeitar Horário Comercial</p>
                            <p class="text-sm text-stone-500 dark:text-stone-400">Fora do horário, responde apenas com a mensagem de "fora do horário"</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="bot_off_hours_enabled" value="1" {{ ($company->bot_off_hours_enabled ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-stone-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    {{-- Mensagem do Cabeçalho da Conversa --}}
                    <div>
                        <label class="label">Mensagem do Cabeçalho da Conversa</label>
                        <p class="text-xs text-stone-500 mb-2">Cabeçalho exibido quando o cliente inicia a conversa. Deixe vazio para usar o padrão.</p>
                        <textarea name="welcome_message" rows="3" class="input-pastel" placeholder="Olá! Bem-vindo(a) à barbearia! 💈&#10;&#10;Como posso te ajudar?">{{ old('welcome_message', $company->welcome_message) }}</textarea>
                        @error('welcome_message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Mensagem Fora do Horário --}}
                    <div>
                        <label class="label">Mensagem Fora do Horário</label>
                        <p class="text-xs text-stone-500 mb-2">Mensagem enviada quando o cliente envia msg fora do horário de funcionamento.</p>
                        <textarea name="off_hours_message" rows="4" class="input-pastel" placeholder="Olá! No momento estamos fora do horário de atendimento.&#10;Funcionamos de segunda a sábado, das 09:00 às 19:00.&#10;Deixe sua mensagem que retornamos no próximo horário!">{{ old('off_hours_message', $company->off_hours_message) }}</textarea>
                        @error('off_hours_message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-pastel-primary">Salvar Configurações</button>
                </div>
            </form>
        </div>

        {{-- Horários de Funcionamento --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Horários de Funcionamento</h3>
            <p class="text-sm text-stone-500 mb-4">Configure os horários que o bot vai respeitar para mensagens fora do horário.</p>
            @php
                $days = [1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado'];
            @endphp
            <div class="space-y-2">
                @foreach($days as $dayNum => $dayName)
                    @php
                        $wh = \App\Models\WorkingHour::where('day_of_week', $dayNum)->where('active', true)->first();
                    @endphp
                    <div class="flex items-center gap-4 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <span class="w-24 font-medium text-stone-700 dark:text-stone-300 text-sm">{{ $dayName }}</span>
                        @if($wh)
                            <span class="text-emerald-600 font-medium text-sm">{{ substr($wh->start_time, 0, 5) }} às {{ substr($wh->end_time, 0, 5) }}</span>
                        @else
                            <span class="text-stone-400 text-sm">Fechado</span>
                        @endif
                    </div>
                @endforeach
                <div class="flex items-center gap-4 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <span class="w-24 font-medium text-stone-700 dark:text-stone-300 text-sm">Domingo</span>
                    <span class="text-stone-400 text-sm">Fechado</span>
                </div>
            </div>
            <p class="text-xs text-stone-400 mt-3">Para alterar os horários, acesse <a href="{{ route('admin.settings.working-hours') }}" class="text-brand-600 hover:underline">Configurações → Horários</a></p>
        </div>

        {{-- Menu do Bot --}}
        <div class="card-pastel">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-brand-700">Menu do Bot</h3>
                <button onclick="document.getElementById('addMenuModal').classList.remove('hidden')" class="btn-pastel-primary text-sm">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Adicionar
                </button>
            </div>
            <p class="text-sm text-stone-500 mb-4">Configure as opções que aparecem no menu de boas-vindas do bot.</p>

            @php
                $menuItems = \App\Models\BotMenuItem::where('company_id', $company->id)->orderBy('sort_order')->get();
                $actionTypes = \App\Models\BotMenuItem::getActionTypes();
            @endphp

            @if($menuItems->isEmpty())
                <p class="text-stone-400 text-sm">Nenhum item configurado.</p>
            @else
                <div class="space-y-2">
                    @foreach($menuItems as $item)
                        <div class="flex items-center gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl {{ !$item->is_active ? 'opacity-50' : '' }}">
                            <span class="w-7 h-7 rounded-full bg-brand-500 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                {{ $item->menu_number }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-stone-800 dark:text-stone-200 text-sm">{{ $item->label }}</p>
                                <p class="text-xs text-stone-500">{{ $item->getActionLabel() }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-500' }}">
                                    {{ $item->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                                <button onclick="editMenuItem({{ $item->id }}, '{{ addslashes($item->label) }}', '{{ $item->action }}', '{{ addslashes($item->response_text ?? '') }}', {{ $item->is_active ? 'true' : 'false' }})" class="text-stone-400 hover:text-brand-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.bot-menu.destroy', $item) }}" class="inline" onsubmit="return confirm('Remover?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-stone-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Como funciona --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Como Funciona</h3>
            <div class="space-y-3 text-sm text-stone-600 dark:text-stone-400">
                <p>Quando um cliente envia uma mensagem para o número do WhatsApp conectado:</p>
                <ol class="list-decimal list-inside space-y-2">
                    <li>O bot recebe a mensagem automaticamente</li>
                    <li>Apresenta o menu de opções</li>
                    <li>O cliente escolhe o que deseja (agendar, consultar, cancelar, etc.)</li>
                    <li>O bot guia o cliente passo a passo</li>
                    <li>O agendamento é criado automaticamente no sistema</li>
                    <li>O cliente recebe a confirmação pelo WhatsApp</li>
                </ol>
                <p class="mt-4 font-medium">Comandos especiais:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>0</strong> ou <strong>voltar</strong> — Volta ao menu principal</li>
                    <li><strong>menu</strong> — Exibe o menu de opções</li>
                </ul>
            </div>
        </div>

        {{-- Pré-visualização --}}
        <div class="card-pastel">
            <h3 class="font-semibold text-brand-700 mb-4">Pré-visualização</h3>
            <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                        B
                    </div>
                    <div class="bg-white dark:bg-stone-800 rounded-2xl rounded-tl-none p-4 max-w-sm shadow-sm">
                        <p class="text-sm">{!! nl2br(e($company->getDefaultWelcomeMessage())) !!}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Adicionar Menu Item --}}
<div id="addMenuModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 dark:bg-stone-900 bg-opacity-75" onclick="document.getElementById('addMenuModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-stone-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-brand-700 mb-4">Adicionar Item do Menu</h3>
                <form method="POST" action="{{ route('admin.bot-menu.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div>
                                <label class="label">Número</label>
                                <select name="menu_number" required class="input-pastel w-20">
                                    @for($i = 1; $i <= 9; $i++)
                                        @if(!in_array($i, $menuItems->pluck('menu_number')->toArray()))
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endif
                                    @endfor
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="label">Label</label>
                                <input type="text" name="label" required maxlength="100" placeholder="Ex: Agendar horário" class="input-pastel">
                            </div>
                        </div>
                        <div>
                            <label class="label">Ação</label>
                            <select name="action" id="addAction" required class="input-pastel" onchange="toggleAddCustom()">
                                @foreach($actionTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="addCustomText" class="hidden">
                            <label class="label">Texto da Resposta</label>
                            <textarea name="response_text" rows="3" maxlength="1000" placeholder="Texto que o bot vai enviar..." class="input-pastel"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('addMenuModal').classList.add('hidden')" class="btn-pastel-secondary">Cancelar</button>
                        <button type="submit" class="btn-pastel-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Editar Menu Item --}}
<div id="editMenuModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 dark:bg-stone-900 bg-opacity-75" onclick="document.getElementById('editMenuModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-stone-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-brand-700 mb-4">Editar Item do Menu</h3>
                <form id="editMenuForm" method="POST">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="label">Label</label>
                            <input type="text" name="label" id="editMenuLabel" required maxlength="100" class="input-pastel">
                        </div>
                        <div>
                            <label class="label">Ação</label>
                            <select name="action" id="editMenuAction" required class="input-pastel" onchange="toggleEditCustom()">
                                @foreach($actionTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="editCustomText" class="hidden">
                            <label class="label">Texto da Resposta</label>
                            <textarea name="response_text" id="editMenuResponseText" rows="3" maxlength="1000" class="input-pastel"></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="editMenuIsActive" value="1" class="rounded">
                            <label class="text-sm text-stone-700">Ativo</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('editMenuModal').classList.add('hidden')" class="btn-pastel-secondary">Cancelar</button>
                        <button type="submit" class="btn-pastel-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editMenuItem(id, label, action, responseText, isActive) {
    document.getElementById('editMenuForm').action = '{{ url("admin/bot-menu") }}/' + id;
    document.getElementById('editMenuLabel').value = label;
    document.getElementById('editMenuAction').value = action;
    document.getElementById('editMenuResponseText').value = responseText;
    document.getElementById('editMenuIsActive').checked = isActive;
    toggleEditCustom();
    document.getElementById('editMenuModal').classList.remove('hidden');
}

function toggleAddCustom() {
    var action = document.getElementById('addAction').value;
    document.getElementById('addCustomText').classList.toggle('hidden', action !== 'custom');
}

function toggleEditCustom() {
    var action = document.getElementById('editMenuAction').value;
    document.getElementById('editCustomText').classList.toggle('hidden', action !== 'custom');
}
</script>
@endpush
