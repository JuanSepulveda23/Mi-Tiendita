<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#16a34a">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <title>🛒 Modo Caja - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* POS Fullscreen Styles */
        body { overflow: hidden; height: 100vh; }
        .pos-container { height: 100vh; display: flex; flex-direction: column; }
        .pos-main { flex: 1; overflow: hidden; display: flex; }
        .pos-products { flex: 1; overflow-y: auto; padding: 1rem; }
        .pos-cart { width: 380px; display: flex; flex-direction: column; border-left: 2px solid #e5e7eb; background: #f9fafb; }
        .pos-cart-items { flex: 1; overflow-y: auto; padding: 1rem; }
        .pos-cart-footer { border-top: 2px solid #e5e7eb; padding: 1rem; background: white; }

        .pos-product-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 1rem; border-radius: 0.75rem; border: 2px solid #e5e7eb;
            background: white; cursor: pointer; transition: all 0.15s;
            min-height: 100px; text-align: center;
        }
        .pos-product-btn:hover { border-color: #3b82f6; box-shadow: 0 4px 12px rgba(59,130,246,0.15); transform: translateY(-1px); }
        .pos-product-btn:active { transform: scale(0.97); }
        .pos-product-btn.frequent { border-color: #10b981; background: #ecfdf5; }
        .pos-product-btn.frequent:hover { border-color: #059669; }

        .pos-cart-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem; background: white; border-radius: 0.5rem;
            margin-bottom: 0.5rem; border: 1px solid #e5e7eb;
        }
        .pos-cart-item:hover { background: #f3f4f6; }

        .pos-numpad-btn {
            display: flex; align-items: center; justify-content: center;
            height: 52px; border-radius: 0.5rem; font-size: 1.25rem; font-weight: 600;
            cursor: pointer; transition: all 0.1s; border: 1px solid #d1d5db;
            background: white;
        }
        .pos-numpad-btn:hover { background: #f3f4f6; }
        .pos-numpad-btn:active { transform: scale(0.95); background: #e5e7eb; }

        .pos-search { font-size: 1.1rem; padding: 0.75rem 1rem; }

        .cart-qty-btn {
            width: 28px; height: 28px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-weight: bold;
            cursor: pointer; transition: all 0.1s; border: none; font-size: 1rem;
        }
        .cart-qty-btn:active { transform: scale(0.9); }

        .pos-total { font-size: 2rem; font-weight: 800; }
        .pos-cobrar-btn {
            width: 100%; padding: 1rem; font-size: 1.3rem; font-weight: 700;
            border-radius: 0.75rem; border: none; cursor: pointer; transition: all 0.15s;
        }
        .pos-cobrar-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .pos-cobrar-btn:active { transform: scale(0.98); }
        .pos-cobrar-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

        /* Success animation */
        .sale-success-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            display: flex; align-items: center; justify-content: center;
            z-index: 50; animation: fadeIn 0.2s;
        }
        .sale-success-card {
            background: white; border-radius: 1rem; padding: 2rem; text-align: center;
            animation: scaleIn 0.3s; max-width: 400px; width: 90%;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes scaleIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* Quick add product modal */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            display: flex; align-items: center; justify-content: center; z-index: 50;
        }
        .modal-card {
            background: white; border-radius: 1rem; padding: 1.5rem;
            max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .pos-main { flex-direction: column; }
            .pos-cart { width: 100%; height: 45vh; border-left: none; border-top: 2px solid #e5e7eb; }
            .pos-products { height: 55vh; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">

<div class="pos-container" id="pos-app">

    {{-- ═══════════ HEADER ═══════════ --}}
    <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1">
                ← Dashboard
            </a>
            <h1 class="text-xl font-bold text-gray-900">🛒 Modo Caja</h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500" id="pos-clock"></span>
            <button onclick="openQuickProductModal()" class="btn btn-secondary btn-sm">+ Producto Rápido</button>
            <a href="{{ route('sales.today') }}" class="btn btn-secondary btn-sm">📋 Ventas Hoy</a>
        </div>
    </div>

    {{-- ═══════════ MAIN ═══════════ --}}
    <div class="pos-main">

        {{-- ═══ PANEL IZQUIERDO: Productos ═══ --}}
        <div class="pos-products">

            {{-- Buscador rápido --}}
            <div class="mb-4">
                <input
                    type="text"
                    id="pos-search"
                    class="form-input pos-search w-full"
                    placeholder="🔎 Buscar producto por nombre o código..."
                    autocomplete="off"
                    autofocus
                >
                <div id="search-results" class="hidden mt-2 bg-white rounded-lg shadow-lg border border-gray-200 max-h-60 overflow-y-auto"></div>
            </div>

            {{-- Productos frecuentes --}}
            @if($frequentProducts->count() > 0)
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">⭐ Productos Frecuentes</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3" id="frequent-grid">
                        @foreach($frequentProducts as $product)
                            <button
                                class="pos-product-btn frequent"
                                onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->sale_price }}, {{ $product->stock }})"
                                data-product-id="{{ $product->id }}"
                                data-stock="{{ $product->stock }}"
                            >
                                <span class="text-lg font-bold text-gray-800 leading-tight">{{ $product->name }}</span>
                                <span class="text-green-700 font-bold text-lg mt-1">${{ number_format($product->sale_price, 0, ',', '.') }}</span>
                                <span class="text-xs text-gray-400 mt-1">Stock: <span class="stock-display">{{ $product->stock }}</span></span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Todos los productos --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">📦 Todos los Productos</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3" id="all-products-grid">
                    @foreach($allProducts as $product)
                        <button
                            class="pos-product-btn"
                            onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->sale_price }}, {{ $product->stock }})"
                            data-product-id="{{ $product->id }}"
                            data-stock="{{ $product->stock }}"
                        >
                            <span class="text-sm font-semibold text-gray-800 leading-tight">{{ $product->name }}</span>
                            <span class="text-green-700 font-bold mt-1">${{ number_format($product->sale_price, 0, ',', '.') }}</span>
                            <span class="text-xs text-gray-400 mt-1">Stock: <span class="stock-display">{{ $product->stock }}</span></span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══ PANEL DERECHO: Carrito ═══ --}}
        <div class="pos-cart">

            {{-- Header carrito --}}
            <div class="px-4 py-3 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-gray-800 text-lg">🧾 Carrito</h2>
                    <button onclick="clearCart()" class="text-red-500 hover:text-red-700 text-sm font-medium" id="clear-cart-btn" style="display:none;">
                        🗑 Vaciar
                    </button>
                </div>
                <p class="text-sm text-gray-500"><span id="cart-count">0</span> productos</p>
            </div>

            {{-- Items del carrito --}}
            <div class="pos-cart-items" id="cart-items">
                <div id="cart-empty" class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    <p class="text-sm">Toca un producto para agregarlo</p>
                </div>
            </div>

            {{-- Teclado numérico --}}
            <div class="px-4 py-2 bg-gray-50 border-t border-gray-200" id="numpad-section" style="display:none;">
                <p class="text-xs text-gray-500 mb-2 font-medium">Teclado — editando: <span id="numpad-target" class="font-bold text-gray-700"></span></p>
                <div class="grid grid-cols-4 gap-1.5">
                    <button class="pos-numpad-btn" onclick="numpadInput(1)">1</button>
                    <button class="pos-numpad-btn" onclick="numpadInput(2)">2</button>
                    <button class="pos-numpad-btn" onclick="numpadInput(3)">3</button>
                    <button class="pos-numpad-btn bg-red-50 text-red-600 border-red-200" onclick="numpadDelete()">⌫</button>
                    <button class="pos-numpad-btn" onclick="numpadInput(4)">4</button>
                    <button class="pos-numpad-btn" onclick="numpadInput(5)">5</button>
                    <button class="pos-numpad-btn" onclick="numpadInput(6)">6</button>
                    <button class="pos-numpad-btn bg-blue-50 text-blue-600 border-blue-200" onclick="numpadClear()">C</button>
                    <button class="pos-numpad-btn" onclick="numpadInput(7)">7</button>
                    <button class="pos-numpad-btn" onclick="numpadInput(8)">8</button>
                    <button class="pos-numpad-btn" onclick="numpadInput(9)">9</button>
                    <button class="pos-numpad-btn bg-green-50 text-green-600 border-green-200" onclick="numpadConfirm()">✓</button>
                    <button class="pos-numpad-btn col-span-2" onclick="numpadInput(0)">0</button>
                    <button class="pos-numpad-btn col-span-2 bg-gray-100" onclick="numpadClose()">Cerrar</button>
                </div>
            </div>

            {{-- Footer: Total + Cobrar --}}
            <div class="pos-cart-footer">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-600 font-medium">Total:</span>
                    <span class="pos-total text-green-700" id="cart-total">$0</span>
                </div>
                <button
                    class="pos-cobrar-btn bg-green-600 text-white hover:bg-green-700"
                    id="cobrar-btn"
                    onclick="processSale()"
                    disabled
                >
                    💰 COBRAR
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ MODAL: Producto Rápido ═══════════ --}}
<div id="quick-product-modal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <h3 class="text-lg font-bold text-gray-800 mb-4">➕ Agregar Producto Rápido</h3>
        <div class="space-y-3">
            <div>
                <label class="form-label">Nombre *</label>
                <input type="text" id="qp-name" class="form-input" placeholder="Ej: Coca Cola 600ml">
            </div>
            <div>
                <label class="form-label">Precio de venta *</label>
                <input type="number" id="qp-price" class="form-input" placeholder="0" min="0" step="1">
            </div>
            <div>
                <label class="form-label">Stock *</label>
                <input type="number" id="qp-stock" class="form-input" placeholder="0" min="0">
            </div>
        </div>
        <div class="flex gap-2 mt-4">
            <button onclick="closeQuickProductModal()" class="btn btn-secondary flex-1">Cancelar</button>
            <button onclick="saveQuickProduct()" class="btn btn-success flex-1">Guardar</button>
        </div>
        <p id="qp-error" class="text-red-600 text-sm mt-2 hidden"></p>
    </div>
</div>

{{-- ═══════════ JAVASCRIPT POS ═══════════ --}}
<script>
    // ── State ──
    let cart = [];
    let numpadActive = false;
    let numpadProductId = null;
    let numpadValue = '';
    let searchTimeout = null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // ── Clock ──
    function updateClock() {
        const now = new Date();
        document.getElementById('pos-clock').textContent = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ── Add to Cart ──
    function addToCart(productId, name, price, stock) {
        const existing = cart.find(item => item.productId === productId);

        if (existing) {
            if (existing.quantity >= stock) {
                showToast('⚠️ Stock máximo alcanzado', 'warning');
                return;
            }
            existing.quantity++;
        } else {
            if (stock <= 0) {
                showToast('⚠️ Producto sin stock', 'warning');
                return;
            }
            cart.push({ productId, name, price, stock, quantity: 1 });
        }

        renderCart();
        showToast(`+ ${name}`, 'success');
    }

    // ── Remove from Cart ──
    function removeFromCart(productId) {
        cart = cart.filter(item => item.productId !== productId);
        renderCart();
    }

    // ── Update Quantity ──
    function updateQuantity(productId, delta) {
        const item = cart.find(i => i.productId === productId);
        if (!item) return;

        const newQty = item.quantity + delta;
        if (newQty < 1) { removeFromCart(productId); return; }
        if (newQty > item.stock) { showToast('⚠️ Stock máximo', 'warning'); return; }

        item.quantity = newQty;
        renderCart();
    }

    // ── Numpad ──
    function openNumpad(productId, name) {
        numpadActive = true;
        numpadProductId = productId;
        numpadValue = '';
        document.getElementById('numpad-section').style.display = 'block';
        document.getElementById('numpad-target').textContent = name;
    }

    function numpadInput(digit) {
        numpadValue += digit.toString();
    }

    function numpadDelete() {
        numpadValue = numpadValue.slice(0, -1);
    }

    function numpadClear() {
        numpadValue = '';
    }

    function numpadConfirm() {
        const qty = parseInt(numpadValue) || 1;
        const item = cart.find(i => i.productId === numpadProductId);
        if (item) {
            if (qty > item.stock) {
                showToast('⚠️ Cantidad supera stock', 'warning');
                return;
            }
            if (qty < 1) {
                removeFromCart(numpadProductId);
            } else {
                item.quantity = qty;
            }
            renderCart();
        }
        numpadClose();
    }

    function numpadClose() {
        numpadActive = false;
        numpadProductId = null;
        numpadValue = '';
        document.getElementById('numpad-section').style.display = 'none';
    }

    // ── Clear Cart ──
    function clearCart() {
        if (cart.length === 0) return;
        if (!confirm('¿Vaciar el carrito?')) return;
        cart = [];
        renderCart();
        numpadClose();
    }

    // ── Render Cart ──
    function renderCart() {
        const container = document.getElementById('cart-items');
        const emptyMsg = document.getElementById('cart-empty');
        const countEl = document.getElementById('cart-count');
        const totalEl = document.getElementById('cart-total');
        const cobrarBtn = document.getElementById('cobrar-btn');
        const clearBtn = document.getElementById('clear-cart-btn');

        if (cart.length === 0) {
            container.innerHTML = '';
            container.appendChild(createEmptyMessage());
            countEl.textContent = '0';
            totalEl.textContent = '$0';
            cobrarBtn.disabled = true;
            clearBtn.style.display = 'none';
            return;
        }

        clearBtn.style.display = 'block';
        let total = 0;
        let html = '';

        cart.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            html += `
                <div class="pos-cart-item">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">${item.name}</p>
                        <p class="text-xs text-gray-500">$${formatNumber(item.price)} × ${item.quantity} = <span class="font-bold text-green-700">$${formatNumber(subtotal)}</span></p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button class="cart-qty-btn bg-gray-200 text-gray-700 hover:bg-gray-300" onclick="updateQuantity(${item.productId}, -1)">−</button>
                        <button class="w-8 h-7 text-center text-sm font-bold bg-gray-100 rounded cursor-pointer" onclick="openNumpad(${item.productId}, '${item.name.replace(/'/g, "\\'")}')">${item.quantity}</button>
                        <button class="cart-qty-btn bg-green-100 text-green-700 hover:bg-green-200" onclick="updateQuantity(${item.productId}, 1)">+</button>
                        <button class="cart-qty-btn bg-red-100 text-red-600 hover:bg-red-200 ml-1" onclick="removeFromCart(${item.productId})">✕</button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        countEl.textContent = cart.reduce((sum, i) => sum + i.quantity, 0);
        totalEl.textContent = '$' + formatNumber(total);
        cobrarBtn.disabled = false;
    }

    function createEmptyMessage() {
        const div = document.createElement('div');
        div.id = 'cart-empty';
        div.className = 'flex flex-col items-center justify-center h-full text-gray-400';
        div.innerHTML = `
            <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            <p class="text-sm">Toca un producto para agregarlo</p>
        `;
        return div;
    }

    // ── Process Sale (with offline support) ──
    async function processSale() {
        if (cart.length === 0) return;

        const cobrarBtn = document.getElementById('cobrar-btn');
        cobrarBtn.disabled = true;
        cobrarBtn.textContent = '⏳ Procesando...';

        const items = cart.map(item => ({
            product_id: item.productId,
            quantity: item.quantity
        }));

        if (!navigator.onLine) {
            // Save offline
            saveOfflineSale(items, cart);
            showSaleSuccess(cart.reduce((s, i) => s + i.price * i.quantity, 0), 'OFFLINE');
            updateStockDisplays();
            cart = [];
            renderCart();
            numpadClose();
            cobrarBtn.textContent = '💰 COBRAR';
            showToast('📡 Venta guardada offline', 'warning');
            return;
        }

        try {
            const response = await fetch('{{ route("pos.sell") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ items })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error al procesar la venta');
            }

            showSaleSuccess(data.total, data.sale_id);
            updateStockDisplays();
            cart = [];
            renderCart();
            numpadClose();

        } catch (error) {
            if (!navigator.onLine) {
                saveOfflineSale(items, cart);
                showSaleSuccess(cart.reduce((s, i) => s + i.price * i.quantity, 0), 'OFFLINE');
                updateStockDisplays();
                cart = [];
                renderCart();
                numpadClose();
                showToast('📡 Venta guardada offline', 'warning');
            } else {
                showToast('❌ ' + error.message, 'error');
                cobrarBtn.disabled = false;
            }
        }

        cobrarBtn.textContent = '💰 COBRAR';
    }

    function updateStockDisplays() {
        cart.forEach(item => {
            const btns = document.querySelectorAll(`[data-product-id="${item.productId}"]`);
            btns.forEach(btn => {
                const newStock = item.stock - item.quantity;
                btn.dataset.stock = newStock;
                const stockSpan = btn.querySelector('.stock-display');
                if (stockSpan) stockSpan.textContent = newStock;
                if (newStock <= 0) {
                    btn.style.opacity = '0.4';
                    btn.style.pointerEvents = 'none';
                }
            });
        });
    }

    // ── Offline Sales Storage ──
    function saveOfflineSale(items, cartItems) {
        const pending = JSON.parse(localStorage.getItem('pendingSales') || '[]');
        pending.push({
            items: items,
            total: cartItems.reduce((s, i) => s + i.price * i.quantity, 0),
            timestamp: new Date().toISOString()
        });
        localStorage.setItem('pendingSales', JSON.stringify(pending));
        updatePendingBadge();
    }

    async function syncOfflineSales() {
        const pending = JSON.parse(localStorage.getItem('pendingSales') || '[]');
        if (pending.length === 0) return;

        let synced = 0;
        const failed = [];

        for (const sale of pending) {
            try {
                const response = await fetch('{{ route("pos.sell") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ items: sale.items })
                });

                if (response.ok) {
                    synced++;
                } else {
                    failed.push(sale);
                }
            } catch (e) {
                failed.push(sale);
            }
        }

        localStorage.setItem('pendingSales', JSON.stringify(failed));
        updatePendingBadge();

        if (synced > 0) {
            showToast(`✅ ${synced} venta(s) sincronizada(s)`, 'success');
        }
        if (failed.length > 0) {
            showToast(`⚠️ ${failed.length} venta(s) pendiente(s)`, 'warning');
        }
    }

    function updatePendingBadge() {
        const pending = JSON.parse(localStorage.getItem('pendingSales') || '[]');
        let badge = document.getElementById('pending-badge');
        if (pending.length > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.id = 'pending-badge';
                badge.className = 'inline-flex items-center px-2 py-1 text-xs font-bold bg-yellow-500 text-white rounded-full ml-2';
                document.querySelector('.pos-container .flex.items-center.gap-4').appendChild(badge);
            }
            badge.textContent = `${pending.length} pendiente(s)`;
        } else if (badge) {
            badge.remove();
        }
    }

    // Sync when coming back online
    window.addEventListener('online', () => {
        showToast('🌐 Conexión restaurada, sincronizando...', 'info');
        syncOfflineSales();
    });

    // Listen for SW sync message
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', event => {
            if (event.data.type === 'SYNC_OFFLINE_SALES') {
                syncOfflineSales();
            }
        });
    }

    // Init: check pending sales
    updatePendingBadge();
    if (navigator.onLine) syncOfflineSales();

    // ── Sale Success Overlay ──
    function showSaleSuccess(total, saleId) {
        const overlay = document.createElement('div');
        overlay.className = 'sale-success-overlay';
        overlay.innerHTML = `
            <div class="sale-success-card">
                <div class="text-6xl mb-4">✅</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">¡Venta registrada!</h2>
                <p class="text-3xl font-bold text-green-600 mb-2">$${formatNumber(total)}</p>
                <p class="text-sm text-gray-500 mb-4">Venta #${saleId}</p>
                <button onclick="this.closest('.sale-success-overlay').remove()" class="btn btn-primary px-8 py-3 text-lg">
                    Continuar vendiendo
                </button>
            </div>
        `;
        document.body.appendChild(overlay);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (overlay.parentNode) overlay.remove();
        }, 3000);
    }

    // ── Search ──
    const searchInput = document.getElementById('pos-search');
    const searchResults = document.getElementById('search-results');

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 1) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(query)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const products = await response.json();

                if (products.length === 0) {
                    searchResults.innerHTML = '<div class="p-3 text-gray-500 text-sm">No se encontraron productos</div>';
                } else {
                    searchResults.innerHTML = products.map(p => `
                        <button class="w-full text-left px-4 py-3 hover:bg-blue-50 flex items-center justify-between border-b border-gray-100 last:border-0"
                                onclick="addToCart(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.sale_price}, ${p.stock}); document.getElementById('pos-search').value=''; document.getElementById('search-results').classList.add('hidden');">
                            <div>
                                <span class="font-medium text-gray-800">${p.name}</span>
                                ${p.barcode ? `<span class="text-xs text-gray-400 ml-2">${p.barcode}</span>` : ''}
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-green-700">$${formatNumber(p.sale_price)}</span>
                                <span class="text-xs text-gray-400 ml-2">Stock: ${p.stock}</span>
                            </div>
                        </button>
                    `).join('');
                }

                searchResults.classList.remove('hidden');
            } catch (err) {
                console.error('Search error:', err);
            }
        }, 200);
    });

    // Close search on click outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // Keyboard shortcut: focus search on '/'
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            searchInput.focus();
        }
        if (e.key === 'F2') {
            e.preventDefault();
            processSale();
        }
    });

    // ── Quick Product Modal ──
    function openQuickProductModal() {
        document.getElementById('quick-product-modal').style.display = 'flex';
        document.getElementById('qp-name').focus();
    }

    function closeQuickProductModal() {
        document.getElementById('quick-product-modal').style.display = 'none';
        document.getElementById('qp-name').value = '';
        document.getElementById('qp-price').value = '';
        document.getElementById('qp-stock').value = '';
        document.getElementById('qp-error').classList.add('hidden');
    }

    async function saveQuickProduct() {
        const name = document.getElementById('qp-name').value.trim();
        const price = parseFloat(document.getElementById('qp-price').value);
        const stock = parseInt(document.getElementById('qp-stock').value);
        const errorEl = document.getElementById('qp-error');

        if (!name || isNaN(price) || isNaN(stock)) {
            errorEl.textContent = 'Todos los campos son obligatorios.';
            errorEl.classList.remove('hidden');
            return;
        }

        try {
            const response = await fetch('{{ route("products.quickStore") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name, sale_price: price, stock })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error al crear producto');
            }

            // Add button to all products grid
            const grid = document.getElementById('all-products-grid');
            const btn = document.createElement('button');
            btn.className = 'pos-product-btn';
            btn.setAttribute('data-product-id', data.product.id);
            btn.setAttribute('data-stock', data.product.stock);
            btn.onclick = function() {
                addToCart(data.product.id, data.product.name, data.product.sale_price, data.product.stock);
            };
            btn.innerHTML = `
                <span class="text-sm font-semibold text-gray-800 leading-tight">${data.product.name}</span>
                <span class="text-green-700 font-bold mt-1">$${formatNumber(data.product.sale_price)}</span>
                <span class="text-xs text-gray-400 mt-1">Stock: <span class="stock-display">${data.product.stock}</span></span>
            `;
            grid.prepend(btn);

            closeQuickProductModal();
            showToast(`✅ ${data.product.name} creado`, 'success');

        } catch (error) {
            errorEl.textContent = error.message;
            errorEl.classList.remove('hidden');
        }
    }

    // ── Toast Notifications ──
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const colors = {
            success: 'bg-green-600', error: 'bg-red-600',
            warning: 'bg-yellow-500', info: 'bg-blue-600'
        };
        toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-4 py-2 rounded-lg shadow-lg z-50 text-sm font-medium`;
        toast.style.animation = 'fadeIn 0.2s';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 1500);
    }

    // ── Format Number ──
    function formatNumber(num) {
        return parseFloat(num).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

</script>

<!-- PWA Service Worker + Offline indicator -->
<div id="offline-banner" class="fixed top-0 left-0 right-0 bg-yellow-500 text-yellow-900 text-center py-2 text-sm font-semibold z-50 hidden">
    📡 Sin conexión — Las ventas se guardarán localmente
</div>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }
    function updateOnlineStatus() {
        document.getElementById('offline-banner').classList.toggle('hidden', navigator.onLine);
    }
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
</script>

</body>
</html>
