<div id="detailModal" class="hidden fixed inset-0 bg-brand-900/30 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-xl rounded-2xl bg-white/95 dark:bg-stone-800/95 dark:border-stone-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-brand-800 dark:text-brand-200">Detalhes do Agendamento</h3>
            <button onclick="closeDetailModal()" class="text-brand-400 hover:text-brand-600 dark:hover:text-brand-300 text-2xl leading-none">&times;</button>
        </div>

        <div id="detailView">
            <div class="space-y-3 bg-brand-50/50 dark:bg-stone-700/50 rounded-xl p-4">
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Cliente:</span> <span id="detail-customer" class="text-stone-800 dark:text-stone-200"></span></div>
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Profissional:</span> <span id="detail-user" class="text-stone-800 dark:text-stone-200"></span></div>
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Serviços:</span> <span id="detail-service" class="text-stone-800 dark:text-stone-200"></span></div>
                <div id="detail-products-row" class="hidden"><div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Produtos:</span> <span id="detail-products" class="text-stone-800 dark:text-stone-200"></span></div></div>
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Status:</span> <span id="detail-status" class="font-semibold"></span></div>
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Data/Hora:</span> <span id="detail-time" class="text-stone-800 dark:text-stone-200"></span></div>
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Valor:</span> <span id="detail-price" class="text-emerald-700 dark:text-emerald-400 font-semibold"></span></div>
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Telefone:</span> <span id="detail-phone" class="text-stone-800 dark:text-stone-200"></span></div>
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Pagamento:</span> <span id="detail-payment" class="text-stone-800 dark:text-stone-200"></span></div>
                <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Obs:</span> <span id="detail-notes" class="text-stone-800 dark:text-stone-200"></span></div>
                <div id="detail-recurring-row" class="hidden">
                    <div class="flex justify-between"><span class="font-medium text-brand-600 dark:text-brand-400">Recorrência:</span> <span id="detail-recurring" class="text-amber-700 dark:text-amber-400 font-semibold"></span></div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <div class="space-x-2">
                    <button id="btnComplete" onclick="completeAppointment()" class="btn-pastel-success text-sm px-3 py-2">Concluir</button>
                    <button id="btnCancel" onclick="cancelAppointment()" class="btn-pastel-danger text-sm px-3 py-2">Cancelar</button>
                    <button id="btnDeleteSeries" onclick="deleteSeries()" class="hidden btn-pastel-danger text-sm px-3 py-2">Deletar Série</button>
                </div>
                <div class="space-x-2">
                    <button onclick="showEditForm()" class="btn-pastel-primary text-sm px-3 py-2">Editar</button>
                    <button onclick="closeDetailModal()" class="btn-pastel-secondary text-sm px-3 py-2">Fechar</button>
                </div>
            </div>
        </div>

        <div id="detailEdit" class="hidden">
            <form id="editAppointmentForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="appointment_id" id="edit-id">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 dark:text-brand-300 mb-1">Cliente</label>
                    <div class="sel-wrap">
                        <div class="sel-trigger" data-target="edit-customer">
                            <span class="placeholder-text">Selecione um cliente...</span>
                            <span class="arrow">&#9660;</span>
                        </div>
                        <div class="sel-dropdown">
                            <input type="text" class="sel-search" placeholder="Buscar cliente..." autocomplete="off">
                            <div class="sel-options">
                                @foreach($customers as $c)
                                    <div class="sel-option" data-value="{{ $c->id }}">{{ $c->name }}</div>
                                @endforeach
                            </div>
                        </div>
                        <select name="customer_id" id="edit-customer" required class="hidden">
                            <option value="">Selecione...</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 dark:text-brand-300 mb-1">Profissional</label>
                    <div class="sel-wrap">
                        <div class="sel-trigger" data-target="edit-user">
                            <span class="placeholder-text">Selecione um profissional...</span>
                            <span class="arrow">&#9660;</span>
                        </div>
                        <div class="sel-dropdown">
                            <input type="text" class="sel-search" placeholder="Buscar profissional..." autocomplete="off">
                            <div class="sel-options">
                                @foreach($users as $u)
                                    <div class="sel-option" data-value="{{ $u->id }}">{{ $u->name }}</div>
                                @endforeach
                            </div>
                        </div>
                        <select name="user_id" id="edit-user" required class="hidden">
                            <option value="">Selecione...</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-brand-700 dark:text-brand-300">Início</label>
                        <input type="datetime-local" name="start" id="edit-start" required class="input-pastel">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-700 dark:text-brand-300">Fim</label>
                        <input type="datetime-local" name="end" id="edit-end" required class="input-pastel">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 dark:text-brand-300 mb-1">Serviços</label>
                    <div class="sel-wrap sel-multi" id="editServiceSelectWrap">
                        <div class="sel-trigger" data-target="edit-service_ids">
                            <span class="placeholder-text">Selecione os serviços...</span>
                            <span class="arrow">&#9660;</span>
                        </div>
                        <div class="sel-dropdown">
                            <input type="text" class="sel-search" placeholder="Buscar serviço..." autocomplete="off">
                            <div class="sel-options">
                                @foreach($services as $s)
                                <label class="sel-option sel-option-multi" data-value="{{ $s->id }}">
                                    <input type="checkbox" class="sel-checkbox" data-duration="{{ $s->duration_min }}">
                                    <span>{{ $s->name }} <span class="text-xs text-stone-400">({{ $s->duration_min }}min)</span></span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <select name="service_ids[]" multiple class="hidden">
                            @foreach($services as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p id="editServiceCount" class="text-xs text-stone-400 mt-1">Nenhum serviço selecionado</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 dark:text-brand-300 mb-1">Produtos para Venda</label>
                    <div class="sel-wrap sel-multi" id="editProductSelectWrap">
                        <div class="sel-trigger" data-target="edit-product_ids">
                            <span class="placeholder-text">Selecione produtos...</span>
                            <span class="arrow">&#9660;</span>
                        </div>
                        <div class="sel-dropdown">
                            <input type="text" class="sel-search" placeholder="Buscar produto..." autocomplete="off">
                            <div class="sel-options">
                                @foreach($products as $p)
                                <label class="sel-option sel-option-multi" data-value="{{ $p->id }}">
                                    <input type="checkbox" class="sel-checkbox" data-price="{{ $p->sale_price }}" data-stock="{{ $p->quantity }}">
                                    <span>{{ $p->name }} <span class="text-xs text-stone-400">(R$ {{ number_format($p->sale_price, 2, ',', '.') }})</span></span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <select name="product_ids[]" multiple class="hidden">
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="editProductQuantities" class="mt-2 space-y-2"></div>
                    <p id="editProductCount" class="text-xs text-stone-400 mt-1">Nenhum produto selecionado</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 dark:text-brand-300">Observações</label>
                    <textarea name="notes" id="edit-notes" rows="2" class="input-pastel"></textarea>
                </div>

                <div id="edit-recurring-section" class="hidden mb-4 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                    <h4 class="font-semibold text-sm text-amber-800 dark:text-amber-300 mb-2">Agendamento Recorrente</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-amber-700 dark:text-amber-400">Repetir</label>
                            <select name="recurring_frequency" id="edit-recurring-frequency" class="input-pastel text-sm">
                                <option value="">Não repetir</option>
                                <option value="daily">Diariamente</option>
                                <option value="weekly">Semanalmente</option>
                                <option value="biweekly">Quinzenalmente</option>
                                <option value="monthly">Mensalmente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-amber-700 dark:text-amber-400">Até</label>
                            <input type="date" name="recurring_until" id="edit-recurring-until" class="input-pastel text-sm">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 mt-2 text-xs text-amber-700 dark:text-amber-400">
                        <input type="checkbox" name="update_all_series" id="edit-update-all-series" value="1" class="rounded border-amber-300 text-amber-600">
                        Aplicar a todos da série
                    </label>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="showDetailView()" class="btn-pastel-secondary">Cancelar</button>
                    <button type="submit" class="btn-pastel-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentEventId = null;

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#detailEdit .sel-wrap:not(.sel-multi)').forEach(initSearchableSelect);
    document.querySelectorAll('#detailEdit .sel-multi').forEach(initSearchableMultiSelect);

    window.updateEditServiceSelection = function() {
        const checkboxes = document.querySelectorAll('#editServiceSelectWrap .sel-checkbox:checked');
        const count = checkboxes.length;
        const countEl = document.getElementById('editServiceCount');
        countEl.textContent = count === 0 ? 'Nenhum serviço selecionado' : count + ' serviço(s) selecionado(s)';

        let totalMinutes = 0;
        checkboxes.forEach(function(cb) {
            totalMinutes += parseInt(cb.dataset.duration) || 0;
        });

        const startVal = document.getElementById('edit-start').value;
        if (startVal && totalMinutes > 0) {
            const start = new Date(startVal);
            start.setMinutes(start.getMinutes() + totalMinutes);
            document.getElementById('edit-end').value = start.toISOString().slice(0, 16);
        } else if (startVal) {
            document.getElementById('edit-end').value = startVal;
        }
    };

    window.updateEditProductSelection = function() {
        const checkboxes = document.querySelectorAll('#editProductSelectWrap .sel-checkbox:checked');
        const count = checkboxes.length;
        const countEl = document.getElementById('editProductCount');
        const qtyContainer = document.getElementById('editProductQuantities');

        if (count === 0) {
            countEl.textContent = 'Nenhum produto selecionado';
            qtyContainer.innerHTML = '';
            return;
        }

        countEl.textContent = count + ' produto(s) selecionado(s)';
        qtyContainer.innerHTML = '';

        checkboxes.forEach(function(cb) {
            const opt = cb.closest('.sel-option-multi');
            const productId = opt.dataset.value;
            const productName = opt.querySelector('span').textContent.trim().split(' (')[0];
            const price = parseFloat(cb.dataset.price) || 0;

            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 text-sm';
            row.innerHTML = '<span class="flex-1 text-stone-700 dark:text-stone-300">' + productName + '</span>' +
                '<span class="text-stone-500">Qtd:</span>' +
                '<input type="number" name="product_quantities[]" value="1" min="1" class="w-16 input-pastel text-sm text-center" data-product-id="' + productId + '">' +
                '<span class="text-stone-500 w-20 text-right">R$ ' + price.toFixed(2).replace('.', ',') + '</span>';
            qtyContainer.appendChild(row);
        });
    };

    const editStartEl = document.getElementById('edit-start');
    if (editStartEl) {
        editStartEl.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#editServiceSelectWrap .sel-checkbox:checked');
            let maxDuration = 0;
            checkboxes.forEach(function(cb) {
                const d = parseInt(cb.dataset.duration) || 0;
                if (d > maxDuration) maxDuration = d;
            });
            if (this.value && maxDuration > 0) {
                const start = new Date(this.value);
                start.setMinutes(start.getMinutes() + maxDuration);
                document.getElementById('edit-end').value = start.toISOString().slice(0, 16);
            }
        });
    }

    const editForm = document.getElementById('editAppointmentForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const serviceCheckboxes = document.querySelectorAll('#editServiceSelectWrap .sel-checkbox:checked');
            const serviceIds = Array.from(serviceCheckboxes).map(function(cb) { return cb.closest('.sel-option-multi').dataset.value; });

            const productCheckboxes = document.querySelectorAll('#editProductSelectWrap .sel-checkbox:checked');
            const productIds = Array.from(productCheckboxes).map(function(cb) { return cb.closest('.sel-option-multi').dataset.value; });
            const productQtyInputs = document.querySelectorAll('#editProductQuantities input[name="product_quantities[]"]');
            const productQuantities = Array.from(productQtyInputs).map(function(input) { return parseInt(input.value) || 1; });

            const data = {
                customer_id: document.getElementById('edit-customer').value,
                user_id: document.getElementById('edit-user').value,
                service_ids: serviceIds,
                product_ids: productIds,
                product_quantities: productQuantities,
                start: document.getElementById('edit-start').value,
                end: document.getElementById('edit-end').value,
                notes: document.getElementById('edit-notes').value,
            };

            var recurringFreq = document.getElementById('edit-recurring-frequency');
            var recurringUntil = document.getElementById('edit-recurring-until');
            if (recurringFreq && recurringFreq.value) {
                data.recurring_frequency = recurringFreq.value;
                data.recurring_until = recurringUntil.value;
            }
            var updateAllCheck = document.getElementById('edit-update-all-series');
            if (updateAllCheck && updateAllCheck.checked) {
                data.update_all_series = true;
            }

            fetch('/admin/appointments/' + currentEventId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            }).then(r => r.json()).then(function(resp) {
                if (resp.success) {
                    closeDetailModal();
                    calendar.refetchEvents();
                }
            });
        });
    }
});

window.closeDetailModal = function() {
    document.getElementById('detailModal').classList.add('hidden');
    showDetailView();
};

window.showDetailView = function() {
    document.getElementById('detailView').classList.remove('hidden');
    document.getElementById('detailEdit').classList.add('hidden');
};

window.showEditForm = function() {
    document.getElementById('detailView').classList.add('hidden');
    document.getElementById('detailEdit').classList.remove('hidden');
};

window.completeAppointment = function() {
    if (!confirm('Marcar este atendimento como concluído?')) return;
    updateAppointmentStatus('completed');
};

window.cancelAppointment = function() {
    if (!confirm('Cancelar este atendimento?')) return;
    updateAppointmentStatus('cancelled');
};

window.deleteSeries = function() {
    if (!confirm('Deletar toda a série de agendamentos recorrentes?')) return;
    if (!window.currentEventId) return;
    fetch('/admin/appointments/' + window.currentEventId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ delete_all_series: true })
    }).then(function(r) { return r.json(); }).then(function(resp) {
        if (resp.success) {
            closeDetailModal();
            calendar.refetchEvents();
        }
    });
};

window.updateAppointmentStatus = function(status) {
    if (!window.currentEventId) {
        alert('Erro: ID do agendamento não definido');
        return;
    }
    const url = '/admin/appointments/' + window.currentEventId;
    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: status })
    }).then(function(r) {
        if (!r.ok) throw new Error('Erro ao atualizar: ' + r.status);
        return r.json();
    }).then(function(resp) {
        if (resp.success) {
            window.closeDetailModal();
            if (window.calendar) {
                window.calendar.refetchEvents();
            }
        }
    }).catch(function(err) {
        alert(err.message);
    });
};
</script>
@endpush
