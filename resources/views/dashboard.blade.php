<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 Dashboard
            </h2>
            <a href="{{ route('pos') }}" class="btn btn-success text-lg px-6 py-3">
                🛒 Modo Caja
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ═══════════ KPI del Día ═══════════ --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">📅 Resumen de Hoy</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    {{-- Ventas hoy --}}
                    <div class="metric-card border-l-4 border-l-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="metric-card__title">Ventas Hoy</p>
                                <p class="metric-card__value text-green-600">${{ number_format($salesToday, 0, ',', '.') }}</p>
                            </div>
                            <div class="metric-card__icon bg-green-100">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">{{ $salesCountToday }} ventas realizadas</p>
                    </div>

                    {{-- Productos vendidos hoy --}}
                    <div class="metric-card border-l-4 border-l-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="metric-card__title">Productos Vendidos</p>
                                <p class="metric-card__value text-blue-600">{{ $productsSoldToday }}</p>
                            </div>
                            <div class="metric-card__icon bg-blue-100">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">unidades vendidas hoy</p>
                    </div>

                    {{-- Ganancia estimada --}}
                    <div class="metric-card border-l-4 border-l-emerald-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="metric-card__title">Ganancia Estimada</p>
                                <p class="metric-card__value text-emerald-600">${{ number_format($profitToday, 0, ',', '.') }}</p>
                            </div>
                            <div class="metric-card__icon bg-emerald-100">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">ganancia neta del día</p>
                    </div>

                    {{-- Alerta bajo stock --}}
                    <div class="metric-card border-l-4 {{ $lowStock > 0 ? 'border-l-red-500 border-red-300 bg-red-50' : 'border-l-gray-300' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="metric-card__title">⚠️ Bajo Stock</p>
                                <p class="metric-card__value {{ $lowStock > 0 ? 'text-red-600' : '' }}">{{ $lowStock }}</p>
                            </div>
                            <div class="metric-card__icon {{ $lowStock > 0 ? 'bg-red-100' : 'bg-gray-100' }}">
                                <svg class="w-6 h-6 {{ $lowStock > 0 ? 'text-red-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                        </div>
                        @if ($lowStock > 0)
                            <p class="text-sm text-red-600 mt-2">Productos por debajo del mínimo</p>
                        @else
                            <p class="text-sm text-green-600 mt-2">✅ Todo en orden</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══════════ KPI Totales ═══════════ --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">📈 Totales Generales</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="metric-card">
                        <p class="metric-card__title">Total Ventas Acumuladas</p>
                        <p class="metric-card__value">${{ number_format($totalSales, 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ $salesCount }} ventas totales</p>
                    </div>
                    <div class="metric-card">
                        <p class="metric-card__title">Productos Registrados</p>
                        <p class="metric-card__value">{{ $totalProducts }}</p>
                    </div>
                    <div class="metric-card">
                        <p class="metric-card__title">Acceso Rápido</p>
                        <div class="mt-3 flex flex-col gap-2">
                            <a href="{{ route('pos') }}" class="btn btn-success w-full justify-center">🛒 Abrir Caja</a>
                            <a href="{{ route('sales.today') }}" class="btn btn-primary w-full justify-center">📋 Ventas de Hoy</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- ═══════════ Alertas de Vencimiento ═══════════ --}}
                @if ($urgentExpirationAlerts->count() > 0 || $expirationWarningCount > 0)
                    <div class="table-container {{ $urgentExpirationAlerts->count() > 0 || $expirationWarningCount > 0 ? 'border-red-200' : '' }}">
                        <div class="table-header {{ $urgentExpirationAlerts->count() > 0 || $expirationWarningCount > 0 ? 'bg-red-50' : '' }}">
                            <h3 class="table-header__title {{ $urgentExpirationAlerts->count() > 0 || $expirationWarningCount > 0 ? 'text-red-600' : '' }}">
                                🚨 Alertas de Vencimiento
                            </h3>
                            <div class="flex gap-2">
                                @if ($expirationWarningCount > 0)
                                    <span class="badge badge-danger">{{ $expirationWarningCount }} en riesgo de pérdida</span>
                                @endif
                                <a href="{{ route('alerts.expirations') }}" class="text-xs text-blue-600 hover:underline">Ver todas</a>
                            </div>
                        </div>
                        @if ($urgentExpirationAlerts->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Días</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($urgentExpirationAlerts as $alert)
                                            <tr class="bg-red-50 hover:bg-red-100">
                                                <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                                    {{ $alert->product->name }}
                                                </td>
                                                <td class="px-6 py-3 text-center text-sm text-gray-600">
                                                    {{ $alert->product->expiration_date?->format('d/m/Y') ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-3 text-center">
                                                    <span class="text-lg font-bold text-red-600">{{ max($alert->days_remaining, 0) }}</span>
                                                </td>
                                                <td class="px-6 py-3 text-center">
                                                    @if ($alert->will_expire)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-red-600 text-white">
                                                            SE VENCERÁ
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger">🚨 URGENTE</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-6 py-8 text-center text-red-600">
                                <p>⚠️ {{ $expirationWarningCount }} productos en riesgo de pérdida por vencimiento</p>
                                <a href="{{ route('alerts.expirations') }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Revisar ahora</a>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ═══════════ Alertas de Stock Bajo ═══════════ --}}
                @if ($lowStockProducts->count() > 0)
                    <div class="table-container border-red-200">
                        <div class="table-header bg-red-50">
                            <h3 class="table-header__title text-red-600">⚠️ Alertas de Stock Bajo</h3>
                            <span class="badge badge-danger">{{ $lowStockProducts->count() }} productos</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Mínimo</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($lowStockProducts as $product)
                                        <tr class="{{ $product->stock === 0 ? 'bg-red-50' : '' }}">
                                            <td class="px-6 py-3 text-sm text-gray-900">
                                                @if($product->stock === 0)
                                                    🔴
                                                @else
                                                    ⚠️
                                                @endif
                                                {{ $product->name }}
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <span class="badge {{ $product->stock === 0 ? 'badge-danger' : 'badge-warning' }}">
                                                    {{ $product->stock }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 text-center text-sm text-gray-500">
                                                {{ $product->min_stock }}
                                            </td>
                                            <td class="px-6 py-3 text-right">
                                                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">
                                                    📦 Reabastecer
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ═══════════ Productos Más Vendidos Hoy ═══════════ --}}
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-header__title">🏆 Más Vendidos Hoy</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ingreso</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($topProductsToday as $item)
                                    <tr>
                                        <td class="px-6 py-3 text-sm text-gray-900">{{ $item->product->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-3 text-center text-sm font-semibold">{{ $item->total_qty }}</td>
                                        <td class="px-6 py-3 text-right text-sm font-semibold text-green-600">${{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                            No hay ventas hoy aún. <a href="{{ route('pos') }}" class="text-blue-600 hover:underline">¡Abre la caja!</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ═══════════ Últimas Ventas ═══════════ --}}
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-header__title">🕐 Últimas Ventas</h3>
                        <a href="{{ route('sales.index') }}" class="text-sm text-blue-600 hover:underline">Ver todas</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($recentSales as $sale)
                                    <tr>
                                        <td class="px-6 py-3 text-sm text-gray-500">{{ $sale->id }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-500">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">${{ number_format($sale->total, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                            No hay ventas aún.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
