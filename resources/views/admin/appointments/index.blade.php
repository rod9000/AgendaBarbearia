@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Agenda</h2>
        <button onclick="document.getElementById('newAppointmentModal').classList.remove('hidden'); document.querySelectorAll('#newAppointmentModal .sel-wrap:not(.sel-multi)').forEach(function(w) { w.querySelector('select').value = ''; var st = w.querySelector('.selected-text'); if (st) { st.remove(); } var pt = w.querySelector('.placeholder-text'); if (pt) pt.style.display = ''; }); document.querySelectorAll('#newAppointmentModal .sel-multi').forEach(function(w) { w.querySelectorAll('.sel-checkbox').forEach(function(cb) { cb.checked = false; cb.closest('.sel-option-multi').classList.remove('selected'); }); if (typeof w.__syncSelect === 'function') w.__syncSelect(); });" class="btn-pastel-primary">
            + Novo Agendamento
        </button>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <label class="text-sm font-medium text-brand-700">Profissional:</label>
            <select id="userFilter" class="input-pastel min-w-[200px]">
                @if(auth()->user()->isAdmin())
                <option value="">Todos os profissionais</option>
                @endif
                @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="bg-white dark:bg-stone-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
            <div id="calendar"></div>
        </div>
    </div>
</div>

@include('admin.appointments.modal')

@include('admin.appointments.detail_modal')
@endsection

@push('styles')

<style>
    .fc-event { cursor: pointer; }
    .fc-event-title { font-weight: 500; }
    .sel-wrap { position: relative; }
    .sel-trigger {
        display: flex; align-items: center; justify-content: space-between;
        width: 100%; padding: 10px 14px; border: 1px solid #BCCCDC;
        border-radius: 10px; background: #F0F4F8; cursor: pointer;
        font-size: 14px; color: #78716c; text-align: left; gap: 8px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .sel-trigger:hover { border-color: #486585; }
    .sel-trigger.open { border-color: #486585; box-shadow: 0 0 0 3px #D9E2EC; }
    .sel-trigger .arrow { font-size: 12px; color: #BCCCDC; transition: transform 0.2s; }
    .sel-trigger.open .arrow { transform: rotate(180deg); }
    .sel-trigger .selected-text { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #444; }
    .sel-trigger .placeholder-text { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #a8a29e; }
    .sel-dropdown {
        display: none; position: absolute; z-index: 200; top: calc(100% + 4px);
        left: 0; right: 0; background: #fff; border: 1px solid #BCCCDC;
        border-radius: 10px; box-shadow: 0 8px 24px rgba(16,42,67,0.12);
        overflow: hidden;
    }
    .sel-dropdown.open { display: block; }
    .sel-search {
        width: 100%; padding: 10px 12px; border: none; border-bottom: 1px solid #D9E2EC;
        font-size: 14px; outline: none; box-sizing: border-box; background: #fff;
    }
    .sel-search:focus { background: #F0F4F8; }
    .sel-options { max-height: 220px; overflow-y: auto; }
    .sel-option {
        padding: 10px 14px; cursor: pointer; font-size: 14px; color: #444;
        transition: background 0.15s;
    }
    .sel-option:hover { background: #D9E2EC; }
    .sel-option.selected { background: #D9E2EC; color: #334E68; font-weight: 600; }
    .sel-no-results { padding: 14px; text-align: center; color: #a8a29e; font-size: 14px; }
    .sel-option-multi {
        display: flex !important;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        cursor: pointer;
        font-size: 14px;
        color: #444;
        transition: background 0.15s;
    }
    .sel-option-multi:hover { background: #D9E2EC; }
    .sel-option-multi.selected { background: #D9E2EC; color: #334E68; font-weight: 600; }
    .sel-checkbox {
        width: 16px;
        height: 16px;
        accent-color: #486585;
        flex-shrink: 0;
    }

    /* Dark mode */
    .dark .sel-trigger { background: #44403c; border-color: #57534e; color: #d6d3d1; }
    .dark .sel-trigger:hover { border-color: #7B8564; }
    .dark .sel-trigger.open { border-color: #486585; box-shadow: 0 0 0 3px rgba(72,101,133,0.2); }
    .dark .sel-trigger .arrow { color: #78716c; }
    .dark .sel-trigger .selected-text { color: #e7e5e4; }
    .dark .sel-trigger .placeholder-text { color: #78716c; }
    .dark .sel-dropdown { background: #292524; border-color: #57534e; box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
    .dark .sel-search { background: #292524; color: #d6d3d1; border-bottom-color: #44403c; }
    .dark .sel-search:focus { background: #1c1917; }
    .dark .sel-option { color: #d6d3d1; }
    .dark .sel-option:hover { background: #44403c; }
    .dark .sel-option.selected { background: rgba(72,101,133,0.2); color: #829AB1; }
    .dark .sel-option-multi { color: #d6d3d1; }
    .dark .sel-option-multi:hover { background: #44403c; }
    .dark .sel-option-multi.selected { background: rgba(72,101,133,0.2); color: #829AB1; }

    /* FullCalendar dark mode */
    .dark .fc { background: #1c1917; color: #d6d3d1; }
    .dark .fc-toolbar-title { color: #e7e5e4; }
    .dark .fc-button { background: #44403c; border-color: #57534e; color: #d6d3d1; }
    .dark .fc-button:hover { background: #57534e; }
    .dark .fc-button-active { background: #486585 !important; border-color: #486585 !important; color: #fff !important; }
    .dark .fc-daygrid-day { background: #292524; }
    .dark .fc-daygrid-day:hover { background: #44403c; }
    .dark .fc-daygrid-day-number { color: #d6d3d1; }
    .dark .fc-col-header-cell { background: #292524; color: #a8a29e; border-color: #44403c; }
    .dark .fc-timegrid-slot { border-color: #44403c; }
    .dark .fc-timegrid-slot-label { color: #78716c; }
    .dark .fc-scrollgrid { border-color: #44403c; }
    .dark .fc-theme-standard td, .dark .fc-theme-standard th { border-color: #44403c; }
    .dark .fc-event { border-color: rgba(72,101,133,0.5); }
    .dark .fc-day-today { background: rgba(72,101,133,0.1) !important; }
    .dark .fc-timegrid-now-indicator-line { border-color: #ef4444; }
    .dark .fc-timegrid-now-indicator-arrow { border-color: #ef4444; }
</style>
@endpush

@push('scripts')
<script src='{{ asset("vendor/fullcalendar/index.global.min.js") }}'></script>
<script src='{{ asset("vendor/fullcalendar/pt-br.global.min.js") }}'></script>
<script>
const workingHoursData = @json($workingHours);
const defaultSlotMin = '07:00:00';
const defaultSlotMax = '20:00:00';

function getSlotLimits(userId) {
    if (!userId) return { min: defaultSlotMin, max: defaultSlotMax };
    const today = new Date();
    const dayOfWeek = today.getDay();
    const userHours = workingHoursData[userId];
    if (!userHours) return { min: '08:00:00', max: '18:00:00' };
    const dayBlocks = userHours.filter(function(h) { return h.day_of_week === dayOfWeek && h.active; });
    if (dayBlocks.length === 0) return { min: '08:00:00', max: '18:00:00' };
    var min = dayBlocks[0].start_time;
    var max = dayBlocks[0].end_time;
    for (var i = 1; i < dayBlocks.length; i++) {
        if (dayBlocks[i].start_time < min) min = dayBlocks[i].start_time;
        if (dayBlocks[i].end_time > max) max = dayBlocks[i].end_time;
    }
    return { min: min, max: max };
}

function updateSlotLimits(userId) {
    const limits = getSlotLimits(userId);
    calendar.setOption('slotMinTime', limits.min + ':00');
    calendar.setOption('slotMaxTime', limits.max + ':00');
}

document.addEventListener('DOMContentLoaded', function() {
    window.calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: '{{ auth()->user()->default_appointment_view ?? 'dayGridMonth' }}',
        locale: 'pt-br',
        firstDay: 0,
        titleFormat: { year: 'numeric', month: 'long' },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        slotMinTime: defaultSlotMin,
        slotMaxTime: defaultSlotMax,
        allDaySlot: false,
        editable: true,
        selectable: true,
        events: {
            url: '/admin/appointments/calendar-data',
            extraParams: function() {
                return {
                    user_id: document.getElementById('userFilter').value
                };
            }
        },
        select: function(info) {
            document.getElementById('start').value = info.startStr.slice(0, 16);
            document.getElementById('end').value = info.endStr.slice(0, 16);
            document.getElementById('newAppointmentModal').classList.remove('hidden');
        },
        eventClick: function(info) {
            window.currentEventId = info.event.id;
            const props = info.event.extendedProps;
            document.getElementById('detail-customer').textContent = props.customer;
            document.getElementById('detail-service').textContent = props.service;

            var productsRow = document.getElementById('detail-products-row');
            var productsEl = document.getElementById('detail-products');
            if (props.products_price && props.products_price > 0) {
                productsRow.classList.remove('hidden');
                productsEl.textContent = 'R$ ' + parseFloat(props.products_price).toFixed(2).replace('.', ',');
            } else {
                productsRow.classList.add('hidden');
            }

            const statusMap = { scheduled: 'Agendado', confirmed: 'Confirmado', in_progress: 'Em Andamento', completed: 'Concluído', cancelled: 'Cancelado', no_show: 'Não Compareceu' };
            document.getElementById('detail-status').textContent = statusMap[props.status] || props.status;
            document.getElementById('detail-user').textContent = props.user;
            document.getElementById('detail-time').textContent = info.event.start.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            document.getElementById('detail-price').textContent = 'R$ ' + parseFloat(props.price).toFixed(2).replace('.', ',');
            document.getElementById('detail-phone').textContent = props.phone;
            const methodLabels = { dinheiro: 'Dinheiro', cartao: 'Cartão', pix: 'PIX' };
            if (props.payment) {
                if (typeof props.payment === 'object') {
                    document.getElementById('detail-payment').textContent = (methodLabels[props.payment.method] || props.payment.method) + ' - R$ ' + parseFloat(props.payment.amount).toFixed(2).replace('.', ',');
                } else {
                    document.getElementById('detail-payment').textContent = 'Pago';
                }
            } else {
                document.getElementById('detail-payment').textContent = props.status === 'completed' ? 'Aguardando' : '—';
            }
            document.getElementById('detail-notes').textContent = props.notes || '—';

            // Recurring info
            var recurringRow = document.getElementById('detail-recurring-row');
            var recurringEl = document.getElementById('detail-recurring');
            var freqLabels = { daily: 'Diariamente', weekly: 'Semanalmente', biweekly: 'Quinzenalmente', monthly: 'Mensalmente' };
            var editRecurringSection = document.getElementById('edit-recurring-section');
            var editRecurringFreq = document.getElementById('edit-recurring-frequency');
            var editRecurringUntil = document.getElementById('edit-recurring-until');
            var updateAllCheck = document.getElementById('edit-update-all-series');
            var btnDeleteSeries = document.getElementById('btnDeleteSeries');
            if (props.recurring && props.recurring.frequency) {
                recurringRow.classList.remove('hidden');
                recurringEl.textContent = freqLabels[props.recurring.frequency] || props.recurring.frequency + ' até ' + props.recurring.until;
                editRecurringSection.classList.remove('hidden');
                editRecurringFreq.value = props.recurring.frequency;
                editRecurringUntil.value = props.recurring.until;
                updateAllCheck.disabled = false;
                updateAllCheck.checked = false;
                btnDeleteSeries.classList.remove('hidden');
            } else if (props.recurring && props.recurring.parent_id) {
                recurringRow.classList.remove('hidden');
                recurringEl.textContent = 'Parte de série recorrente';
                editRecurringSection.classList.add('hidden');
                btnDeleteSeries.classList.add('hidden');
            } else {
                recurringRow.classList.add('hidden');
                editRecurringSection.classList.add('hidden');
                editRecurringFreq.value = '';
                editRecurringUntil.value = '';
                btnDeleteSeries.classList.add('hidden');
            }

            document.getElementById('btnComplete').style.display = (props.status === 'completed' || props.status === 'cancelled' || props.status === 'no_show') ? 'none' : 'inline-block';
            document.getElementById('btnCancel').style.display = (props.status === 'completed' || props.status === 'cancelled' || props.status === 'no_show') ? 'none' : 'inline-block';

            window.setSearchableValue(document.querySelector('#edit-customer').closest('.sel-wrap'), props.customer_id || '');
            window.setSearchableValue(document.querySelector('#edit-user').closest('.sel-wrap'), props.user_id || '');

            const selectedIds = props.service_ids || [];
            const editWrap = document.getElementById('editServiceSelectWrap');
            if (editWrap && typeof window.setSearchableMultiValue === 'function') {
                window.setSearchableMultiValue(editWrap, selectedIds);
            }
            if (typeof window.updateEditServiceSelection === 'function') {
                window.updateEditServiceSelection();
            }

            const selectedProductIds = props.product_ids || [];
            const editProductWrap = document.getElementById('editProductSelectWrap');
            if (editProductWrap && typeof window.setSearchableMultiValue === 'function') {
                window.setSearchableMultiValue(editProductWrap, selectedProductIds);
            }
            if (typeof window.updateEditProductSelection === 'function') {
                window.updateEditProductSelection();
            }

            document.getElementById('edit-start').value = info.event.start.toISOString().slice(0, 16);
            document.getElementById('edit-end').value = info.event.end.toISOString().slice(0, 16);
            document.getElementById('edit-notes').value = props.notes || '';

            document.getElementById('detailModal').classList.remove('hidden');
        },
        eventDrop: function(info) {
            fetch('/admin/appointments/' + info.event.id + '/reschedule', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    start: info.event.start.toISOString(),
                    end: info.event.end.toISOString()
                })
            }).catch(function() {
                info.revert();
            });
        }
    });
    calendar.render();

    // Filtro por profissional
    var initialUserId = document.getElementById('userFilter').value;
    if (initialUserId) updateSlotLimits(initialUserId);

    document.getElementById('userFilter').addEventListener('change', function() {
        updateSlotLimits(this.value);
        calendar.refetchEvents();
    });

    // Recalcular slotMinTime/slotMaxTime ao navegar (mudança de dia)
    calendar.on('datesSet', function() {
        var userId = document.getElementById('userFilter').value;
        if (userId) updateSlotLimits(userId);
    });

    // Atualização em Tempo Real (Polling a cada 30 segundos)
    setInterval(() => {
        calendar.refetchEvents();
    }, 30000);

    // Escuta eventos via Laravel Echo (WebSockets)
    if (window.Echo) {
        window.Echo.channel('appointments')
            .listen('.AppointmentChanged', () => {
                calendar.refetchEvents();
            });
    }
});
</script>
@endpush
