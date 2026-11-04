<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl sm:text-2xl text-white leading-tight">
                🔮 Predicción de Compras
            </h2>
        </div>
        <a href="{{ route('intelligence.orders') }}"
            class="btn bg-white/20 text-white hover:bg-white/30 border border-emerald-400 shadow-sm backdrop-blur-sm transition-all px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium">
            📋 Ver Pedido Sugerido
        </a>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">

            {{-- Resumen de alertas --}}
            @php
                $critical = $products->filter(fn($p) => $p->risk_level === 'critical');
                $warning = $products->filter(fn($p) => $p->risk_level === 'warning');
            @endphp

            @if($critical->count() > 0)
                <div class="alert bg-red-100 text-red-800 border border-red-300 mb-4 text-xs sm:text-sm">
                    🚨 <strong>{{ $critical->count() }} producto(s)</strong> se agotarán en menos de 3 días
                </div>
            @endif

            @if($warning->count() > 0)
                <div class="alert bg-yellow-100 text-yellow-800 border border-yellow-300 mb-4 text-xs sm:text-sm">
                    ⚠️ <strong>{{ $warning->count() }} producto(s)</strong> se agotarán en menos de 7 días
                </div>
            @endif

            {{-- Tabla de predicciones --}}
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-header__title">Stock restante por producto</h3>
                    <span class="text-xs sm:text-sm text-gray-500">Basado en ventas de últimos 30 días</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Actual</th>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Venta Diaria Prom.</th>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Días Restantes</th>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50 {{ $product->risk_level === 'critical' ? 'bg-red-50' : ($product->risk_level === 'warning' ? 'bg-yellow-50' : '') }}">
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                                        {{ $product->name }}
                                        @if($product->category)
                                            <span class="text-[11px] sm:text-xs text-gray-400 ml-1">({{ $product->category }})</span>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-center">
                                        <span class="badge {{ $product->stock < $product->min_stock ? 'badge-danger' : 'badge-success' }} text-[11px] sm:text-xs">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-center text-xs sm:text-sm text-gray-700">
                                        {{ $product->avg_daily_sales }} ud/día
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-center">
                                        @if($product->days_remaining !== null)
                                            <span class="text-sm sm:text-lg font-bold {{ $product->risk_level === 'critical' ? 'text-red-600' : ($product->risk_level === 'warning' ? 'text-yellow-600' : ($product->risk_level === 'low' ? 'text-blue-600' : 'text-green-600')) }}">
                                                {{ $product->days_remaining }} días
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-center">
                                        @switch($product->risk_level)
                                            @case('critical')
                                                <span class="badge badge-danger text-[11px] sm:text-xs">🔴 Crítico</span>
                                                @break
                                            @case('warning')
                                                <span class="badge badge-warning text-[11px] sm:text-xs">🟡 Alerta</span>
                                                @break
                                            @case('low')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] sm:text-xs font-medium bg-blue-100 text-blue-800">🔵 Bajo</span>
                                                @break
                                            @default
                                                <span class="badge badge-success text-[11px] sm:text-xs">🟢 OK</span>
                                        @endswitch
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm text-xs">
                                            📦 Reabastecer
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 sm:px-6 py-8 sm:py-12 text-center text-xs sm:text-sm text-gray-500">
                                        No hay datos de ventas suficientes para predecir. ¡Sigue vendiendo!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
