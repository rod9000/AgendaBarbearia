@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-brand-800 leading-tight">Nova Venda</h2>
        <a href="{{ route('admin.sales.index') }}" class="btn-pastel-secondary">Voltar</a>
    </div>
@endsection

@push('styles')
<style>
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
    display: none; position: fixed; z-index: 99999; min-width: 260px;
    background: #fff; border: 1px solid #BCCCDC;
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
</style>
@endpush

@push('scripts')
<script>
window.initSearchableSelect = function(wrap) {
    const trigger = wrap.querySelector('.sel-trigger');
    const dropdown = wrap.querySelector('.sel-dropdown');
    const search = wrap.querySelector('.sel-search');
    const options = wrap.querySelectorAll('.sel-option');
    const select = wrap.querySelector('select');
    const placeholderText = trigger.querySelector('.placeholder-text');

    function filterOptions(term) {
        const lower = term.toLowerCase();
        let visible = 0;
        options.forEach(function(opt) {
            const match = opt.textContent.toLowerCase().includes(lower);
            opt.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const noResults = wrap.querySelector('.sel-no-results');
        if (visible === 0) {
            if (!noResults) {
                const el = document.createElement('div');
                el.className = 'sel-no-results';
                el.textContent = 'Nenhum resultado encontrado';
                wrap.querySelector('.sel-options').appendChild(el);
            }
        } else {
            if (noResults) noResults.remove();
        }
    }

    function positionDropdown() {
        const rect = trigger.getBoundingClientRect();
        dropdown.style.top = (rect.bottom + 4) + 'px';
        dropdown.style.left = rect.left + 'px';
        dropdown.style.width = rect.width + 'px';
    }

    function closeDropdown() {
        dropdown.classList.remove('open');
        trigger.classList.remove('open');
    }

    function openDropdown() {
        positionDropdown();
        dropdown.classList.add('open');
        trigger.classList.add('open');
        search.value = '';
        search.focus();
        filterOptions('');
    }

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = dropdown.classList.contains('open');
        document.querySelectorAll('.sel-dropdown.open').forEach(function(d) {
            if (d !== dropdown) d.classList.remove('open');
        });
        document.querySelectorAll('.sel-trigger.open').forEach(function(t) {
            if (t !== trigger) t.classList.remove('open');
        });
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    search.addEventListener('input', function() {
        filterOptions(this.value);
    });

    function getOrCreateSelectedText() {
        let el = trigger.querySelector('.selected-text');
        if (!el) {
            el = document.createElement('span');
            el.className = 'selected-text';
            trigger.insertBefore(el, placeholderText);
        }
        return el;
    }

    options.forEach(function(opt) {
        opt.addEventListener('click', function() {
            const value = this.dataset.value;
            const text = this.textContent;
            select.value = value;
            select.dispatchEvent(new Event('change'));
            const st = getOrCreateSelectedText();
            st.textContent = text;
            placeholderText.style.display = 'none';
            closeDropdown();
            trigger.dispatchEvent(new Event('change'));
        });
    });

    document.addEventListener('scroll', function() {
        if (dropdown.classList.contains('open')) {
            positionDropdown();
        }
    }, true);

    document.addEventListener('click', function(e) {
        if (!wrap.contains(e.target)) {
            closeDropdown();
        }
    });
};
</script>
@endpush

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if($errors->any())
            <div class="bg-rose-100 border border-rose-300 text-rose-700 px-4 py-3 rounded-xl relative mb-4">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.sales.store') }}" id="saleForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="card-pastel">
                        <div class="p-4 border-b border-brand-100 dark:border-stone-700">
                            <h3 class="font-semibold text-brand-800 dark:text-brand-200">Cliente</h3>
                        </div>
                        <div class="p-4">
                            <div class="sel-wrap" data-required="true">
                                <div class="sel-trigger" data-target="customer_id">
                                    <span class="placeholder-text">Selecione um cliente...</span>
                                    <span class="arrow">&#9660;</span>
                                </div>
                                <div class="sel-dropdown">
                                    <input type="text" class="sel-search" placeholder="Buscar cliente..." autocomplete="off">
                                    <div class="sel-options">
                                        @foreach($customers as $c)
                                            <div class="sel-option" data-value="{{ $c->id }}">{{ $c->name }} - {{ $c->phone }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                <select name="customer_id" required class="hidden">
                                    <option value="">Selecione...</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} - {{ $c->phone }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-pastel">
                        <div class="p-4 border-b border-brand-100 dark:border-stone-700">
                            <h3 class="font-semibold text-brand-800 dark:text-brand-200">Produtos</h3>
                        </div>
                        <div class="p-4">
                            <input type="text" id="productSearch" placeholder="Buscar produtos..." class="input-pastel w-full mb-4">

                            @if($products->isEmpty())
                                <p class="text-brand-400 text-center py-8">Nenhum produto com estoque disponível.</p>
                            @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" id="productGrid">
                                @foreach($products as $p)
                                <div class="product-card rounded-xl border border-brand-100 dark:border-stone-700 p-3 text-center cursor-pointer transition-all duration-150 hover:shadow-md hover:border-brand-300 dark:hover:border-brand-600 @if($p->isOutOfStock()) opacity-40 pointer-events-none @endif"
                                     data-name="{{ strtolower($p->name) }}" data-brand="{{ strtolower($p->brand ?? '') }}"
                                     data-id="{{ $p->id }}" data-price="{{ $p->sale_price }}" data-stock="{{ $p->quantity }}">
                                    <div class="text-sm font-semibold text-gray-800 dark:text-stone-200 truncate">{{ $p->name }}</div>
                                    <div class="text-xs text-stone-500 dark:text-stone-400 truncate">{{ $p->brand ?? '—' }}</div>
                                    <div class="text-base font-bold text-brand-700 dark:text-brand-300 mt-1">
                                        R$ {{ number_format($p->sale_price ?? 0, 2, ',', '.') }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium
                                            @if($p->isOutOfStock()) bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400
                                            @elseif($p->isLowStock()) bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                            @else bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                            @endif">
                                            {{ $p->quantity }} {{ $p->quantity == 1 ? 'un' : 'uns' }}
                                        </span>
                                    </div>
                                    <button type="button" class="mt-2 w-full text-xs btn-pastel-primary py-1.5 add-to-cart-btn"
                                            data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->sale_price }}" data-stock="{{ $p->quantity }}">
                                        + Adicionar
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="card-pastel">
                        <div class="p-4 border-b border-brand-100 dark:border-stone-700">
                            <h3 class="font-semibold text-brand-800 dark:text-brand-200">Carrinho</h3>
                        </div>
                        <div class="p-4">
                            <div id="cartItems" class="space-y-3 min-h-[200px]">
                                <p class="text-brand-400 text-center py-8" id="cartEmpty">Nenhum item adicionado.</p>
                            </div>

                            <hr class="my-3 border-brand-100 dark:border-stone-700">

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between text-stone-600 dark:text-stone-400">
                                    <span>Subtotal</span>
                                    <span id="cartSubtotal">R$ 0,00</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-stone-600 dark:text-stone-400">Desconto</span>
                                    <input type="number" name="discount" id="discountInput" value="0" step="0.01" min="0" class="input-pastel w-24 text-right text-sm">
                                </div>
                                <div class="flex justify-between font-semibold text-base text-brand-800 dark:text-brand-200">
                                    <span>Total</span>
                                    <span id="cartTotal">R$ 0,00</span>
                                </div>
                            </div>

                            <hr class="my-3 border-brand-100 dark:border-stone-700">

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">Forma de Pagamento</label>
                                    <select name="payment_method" required class="input-pastel w-full">
                                        <option value="dinheiro">Dinheiro</option>
                                        <option value="pix">Pix</option>
                                        <option value="debito">Cartão de Débito</option>
                                        <option value="credito">Cartão de Crédito</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">Observações</label>
                                    <textarea name="notes" rows="2" class="input-pastel w-full" placeholder="Opcional..."></textarea>
                                </div>
                                <button type="submit" class="btn-pastel-primary w-full justify-center" id="finalizeBtn">
                                    Finalizar Venda
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sel-wrap:not(.sel-multi)').forEach(function(w) { initSearchableSelect(w); });

    const cart = {};
    const cartItems = document.getElementById('cartItems');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTotal = document.getElementById('cartTotal');
    const discountInput = document.getElementById('discountInput');
    const productSearch = document.getElementById('productSearch');

    document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const stock = parseInt(this.dataset.stock);

            if (cart[id]) {
                if (cart[id].quantity < stock) {
                    cart[id].quantity++;
                } else {
                    alert('Estoque insuficiente!');
                    return;
                }
            } else {
                cart[id] = { id: id, name: name, price: price, quantity: 1, stock: stock };
            }
            renderCart();
        });
    });

    productSearch.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(function(card) {
            const name = card.dataset.name;
            const brand = card.dataset.brand;
            card.style.display = (name.includes(q) || brand.includes(q)) ? '' : 'none';
        });
    });

    discountInput.addEventListener('input', renderCart);

    function renderCart() {
        const keys = Object.keys(cart);
        if (keys.length === 0) {
            cartItems.innerHTML = '<p class="text-brand-400 text-center py-8">Nenhum item adicionado.</p>';
            cartSubtotal.textContent = 'R$ 0,00';
            cartTotal.textContent = 'R$ 0,00';
            return;
        }

        let html = '';
        let subtotal = 0;

        keys.forEach(function(key) {
            const item = cart[key];
            const total = item.price * item.quantity;
            subtotal += total;
            html += '<div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-brand-50/50 dark:bg-stone-800">';
            html += '<div class="flex-1 min-w-0">';
            html += '<div class="text-sm font-medium text-gray-800 dark:text-stone-200 truncate">' + item.name + '</div>';
            html += '<div class="text-xs text-stone-500">R$ ' + item.price.toFixed(2).replace('.', ',') + '</div>';
            html += '</div>';
            html += '<div class="flex items-center gap-1">';
            html += '<button type="button" class="qty-minus w-6 h-6 rounded-full bg-brand-100 dark:bg-stone-700 text-brand-600 dark:text-brand-400 text-xs font-bold flex items-center justify-center hover:bg-brand-200" data-id="' + key + '">−</button>';
            html += '<span class="w-6 text-center text-sm font-medium text-gray-800 dark:text-stone-200">' + item.quantity + '</span>';
            html += '<button type="button" class="qty-plus w-6 h-6 rounded-full bg-brand-100 dark:bg-stone-700 text-brand-600 dark:text-brand-400 text-xs font-bold flex items-center justify-center hover:bg-brand-200" data-id="' + key + '">+</button>';
            html += '</div>';
            html += '<div class="text-sm font-semibold text-brand-700 dark:text-brand-300 w-16 text-right">R$ ' + total.toFixed(2).replace('.', ',') + '</div>';
            html += '<button type="button" class="remove-item text-rose-400 hover:text-rose-600 p-1" data-id="' + key + '">';
            html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
            html += '</button>';
            html += '</div>';

            html += '<input type="hidden" name="items[' + key + '][product_id]" value="' + item.id + '">';
            html += '<input type="hidden" name="items[' + key + '][quantity]" value="' + item.quantity + '">';
            html += '<input type="hidden" name="items[' + key + '][unit_price]" value="' + item.price + '">';
        });

        cartItems.innerHTML = html;

        const discount = parseFloat(discountInput.value) || 0;
        const total = Math.max(0, subtotal - discount);

        cartSubtotal.textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
        cartTotal.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');

        document.querySelectorAll('.qty-minus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (cart[id]) {
                    if (cart[id].quantity > 1) {
                        cart[id].quantity--;
                    } else {
                        delete cart[id];
                    }
                    renderCart();
                }
            });
        });

        document.querySelectorAll('.qty-plus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (cart[id]) {
                    if (cart[id].quantity < cart[id].stock) {
                        cart[id].quantity++;
                        renderCart();
                    } else {
                        alert('Estoque insuficiente!');
                    }
                }
            });
        });

        document.querySelectorAll('.remove-item').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                delete cart[id];
                renderCart();
            });
        });
    }

    document.getElementById('saleForm').addEventListener('submit', function(e) {
        if (Object.keys(cart).length === 0) {
            e.preventDefault();
            alert('Adicione pelo menos um produto ao carrinho.');
            return;
        }
        document.getElementById('finalizeBtn').disabled = true;
        document.getElementById('finalizeBtn').textContent = 'Finalizando...';
    });
});
</script>
@endpush
