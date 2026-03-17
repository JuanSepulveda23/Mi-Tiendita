<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📋 Pedido Sugerido
            </h2>
            <a href="{{ route('intelligence.predictions') }}" class="btn btn-secondary">
                🔮 Ver Predicciones
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Resumen del pedido --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="metric-card border-l-4 border-l-blue-500">
                    <p class="metric-card__title">Productos a Pedir</p>
                    <p class="metric-card__value text-blue-600">{{ $orders->count() }}</p>
                </div>
                <div class="metric-card border-l-4 border-l-purple-500">
                    <p class="metric-card__title">Días de Análisis</p>
                    <p class="metric-card__value text-purple-600">{{ $days }}</p>
                    <p class="text-sm text-gray-500 mt-1">rotación real + buffer</p>
                </div>
                <div class="metric-card border-l-4 border-l-green-500">
                    <p class="metric-card__title">Cantidad Total a Comprar</p>
                    <p class="metric-card__value text-green-600" id="kpi-total-order">{{ $totalToOrder }}</p>
                    <p class="text-sm text-gray-500 mt-1">unidades</p>
                </div>
                <div class="metric-card border-l-4 border-l-emerald-500">
                    <p class="metric-card__title">Inversión Estimada</p>
                    <p class="metric-card__value text-emerald-600" id="kpi-total-cost">${{ number_format($totalCost, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Tabla de pedidos --}}
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-header__title">Lista de Pedido (Análisis Últimos 7 Días)</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500">Rotación real + {{ $bufferDays }} días buffer</span>
                        <button onclick="printOrder()" class="btn btn-secondary btn-sm">🖨️ Imprimir</button>
                    </div>
                </div>

                <div class="overflow-x-auto" id="order-table">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas 7 Días</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Prom./Día</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Máx. Día</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Actual</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Objetivo</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider font-bold text-blue-600">Comprar</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Costo Est.</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $order->product->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ $order->product->supplier ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-600 font-mono">
                                        <div class="flex justify-center gap-1">
                                            @foreach($order->daily_sales as $sale)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-blue-100 text-blue-800 text-xs font-semibold" title="Venta del día">
                                                    {{ $sale }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-bold text-green-600">{{ $order->avg_daily }} ud/día</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            {{ $order->max_daily }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="badge {{ $order->current_stock < $order->product->min_stock ? 'badge-danger' : 'badge-warning' }}">
                                            {{ $order->current_stock }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700">
                                        {{ $order->target_stock }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-lg font-bold text-blue-600">{{ $order->to_order }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                        ${{ number_format($order->estimated_cost, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                        ¡No necesitas pedir nada! Tu stock cubre los próximos días basado en la rotación actual.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($orders->count() > 0)
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-right text-sm font-bold text-gray-700">TOTAL PEDIDO:</td>
                                    <td class="px-6 py-4 text-center text-lg font-bold text-blue-600">{{ $totalToOrder }}</td>
                                    <td class="px-6 py-4 text-right text-lg font-bold text-gray-900">${{ number_format($totalCost, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Explicación del algoritmo --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-bold text-blue-900 mb-2">📊 Cómo funciona esta recomendación</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>✓ Se analiza la <strong>rotación de los últimos 7 días</strong> (ventas diarias reales)</li>
                    <li>✓ Se calcula el <strong>promedio diario</strong> y el <strong>máximo vendido en un día</strong></li>
                    <li>✓ Stock objetivo = (Promedio × {{ $bufferDays }} días) + Máximo diario</li>
                    <li>✓ A comprar = Stock Objetivo - Stock Actual</li>
                    <li>✓ Esta fórmula asegura que <strong>nunca te quedes sin stock</strong> ni tengas exceso</li>
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function printOrder() {
            const content = document.getElementById('order-table').innerHTML;
            const totalOrder = document.getElementById('kpi-total-order')?.textContent || '—';
            const totalCost = document.getElementById('kpi-total-cost')?.textContent || '—';
            
            const win = window.open('', '_blank');
            win.document.write(`
                <html><head><title>Pedido Sugerido</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h1 { text-align: center; margin-bottom: 10px; }
                    .subtitle { text-align: center; color: #666; margin-bottom: 20px; font-size: 12px; }
                    .summary { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
                    .summary-item { flex: 1; min-width: 200px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
                    .summary-item strong { display: block; color: #666; font-size: 11px; }
                    .summary-item .value { font-size: 16px; font-weight: bold; color: #0ea5e9; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
                    th, td { border: 1px solid #ddd; padding: 6px; text-align: center; }
                    th { background: #f3f4f6; font-weight: bold; }
                    td:first-child, th:first-child { text-align: left; }
                    tfoot { background: #f9fafb; font-weight: bold; }
                </style></head>
                <body>
                    <h1>📋 PEDIDO SUGERIDO</h1>
                    <div class="subtitle">Generado: ${new Date().toLocaleDateString('es-CO')} — Análisis: Últimos 7 días</div>
                    <div class="summary">
                        <div class="summary-item">
                            <strong>CANTIDAD TOTAL</strong>
                            <div class="value">${totalOrder} ud</div>
                        </div>
                        <div class="summary-item">
                            <strong>INVERSIÓN TOTAL</strong>
                            <div class="value">${totalCost}</div>
                        </div>
                    </div>
                    ${content}
                </body></html>
            `);
            win.document.close();
            setTimeout(() => win.print(), 200);
        }
    </script>
    @endpush
</x-app-layout>
