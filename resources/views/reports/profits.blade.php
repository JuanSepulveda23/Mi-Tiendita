<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                💰 Reporte de Ganancias
            </h2>
            <div class="flex items-center gap-2">
                <select id="days-filter" class="form-input text-sm w-auto" onchange="loadAllCharts()">
                    <option value="7">Últimos 7 días</option>
                    <option value="15">Últimos 15 días</option>
                    <option value="30" selected>Últimos 30 días</option>
                    <option value="60">Últimos 60 días</option>
                    <option value="90">Últimos 90 días</option>
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- KPI Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="metric-card border-l-4 border-l-green-500">
                    <p class="metric-card__title">Ingresos Totales</p>
                    <p class="metric-card__value text-green-600" id="kpi-revenue">$0</p>
                </div>
                <div class="metric-card border-l-4 border-l-red-400">
                    <p class="metric-card__title">Costo Total</p>
                    <p class="metric-card__value text-red-500" id="kpi-cost">$0</p>
                </div>
                <div class="metric-card border-l-4 border-l-emerald-500">
                    <p class="metric-card__title">Ganancia Neta</p>
                    <p class="metric-card__value text-emerald-600" id="kpi-profit">$0</p>
                </div>
                <div class="metric-card border-l-4 border-l-blue-500">
                    <p class="metric-card__title">Margen Promedio</p>
                    <p class="metric-card__value text-blue-600" id="kpi-margin">0%</p>
                </div>
            </div>

            {{-- Gráfico de ganancias diarias --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Ganancias Diarias</h3>
                <div id="chart-daily" style="height: 350px;"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Gráfico de margen por producto --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Ganancia por Producto</h3>
                    <div id="chart-products" style="height: 350px;"></div>
                </div>

                {{-- Gráfico de comparación precios --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">💵 Precio Compra vs Venta</h3>
                    <div id="chart-prices" style="height: 350px;"></div>
                </div>
            </div>

            {{-- Tabla resumen por producto --}}
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-header__title">Detalle por Producto</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty Vendida</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">P. Compra</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">P. Venta</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ingreso</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ganancia</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Margen</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="products-table-body">
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">Cargando datos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let chartDaily = null;
        let chartProducts = null;
        let chartPrices = null;

        function getDays() {
            return document.getElementById('days-filter').value;
        }

        function fmt(n) {
            return parseFloat(n).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        async function loadAllCharts() {
            const days = getDays();
            await Promise.all([
                loadDailyChart(days),
                loadProductCharts(days)
            ]);
        }

        async function loadDailyChart(days) {
            const res = await fetch(`/reports/profits/data?days=${days}`);
            const data = await res.json();

            // Update KPI
            const totals = data.reduce((acc, d) => ({
                revenue: acc.revenue + d.revenue,
                cost: acc.cost + d.cost,
                profit: acc.profit + d.profit,
                count: acc.count + d.sales_count,
            }), { revenue: 0, cost: 0, profit: 0, count: 0 });

            document.getElementById('kpi-revenue').textContent = '$' + fmt(totals.revenue);
            document.getElementById('kpi-cost').textContent = '$' + fmt(totals.cost);
            document.getElementById('kpi-profit').textContent = '$' + fmt(totals.profit);
            const avgMargin = totals.revenue > 0 ? ((totals.profit / totals.revenue) * 100).toFixed(1) : 0;
            document.getElementById('kpi-margin').textContent = avgMargin + '%';

            const options = {
                chart: { type: 'area', height: 350, toolbar: { show: true }, zoom: { enabled: true } },
                series: [
                    { name: 'Ingresos', data: data.map(d => d.revenue) },
                    { name: 'Costo', data: data.map(d => d.cost) },
                    { name: 'Ganancia', data: data.map(d => d.profit) }
                ],
                xaxis: {
                    categories: data.map(d => {
                        const date = new Date(d.date + 'T12:00:00');
                        return date.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
                    }),
                    labels: { rotate: -45, style: { fontSize: '11px' } }
                },
                yaxis: { labels: { formatter: v => '$' + fmt(v) } },
                colors: ['#16a34a', '#ef4444', '#0ea5e9'],
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: v => '$' + fmt(v) } },
                legend: { position: 'top' },
                grid: { borderColor: '#f1f5f9' }
            };

            if (chartDaily) {
                chartDaily.updateOptions(options);
            } else {
                chartDaily = new ApexCharts(document.getElementById('chart-daily'), options);
                chartDaily.render();
            }
        }

        async function loadProductCharts(days) {
            const res = await fetch(`/reports/profits/by-product?days=${days}`);
            const data = await res.json();

            // Bar chart: profit per product
            const barOptions = {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                series: [{
                    name: 'Ganancia',
                    data: data.map(p => p.profit)
                }],
                xaxis: {
                    categories: data.map(p => p.name.length > 15 ? p.name.substring(0, 15) + '...' : p.name),
                    labels: { rotate: -45, style: { fontSize: '10px' } }
                },
                yaxis: { labels: { formatter: v => '$' + fmt(v) } },
                colors: ['#10b981'],
                plotOptions: { bar: { borderRadius: 4, horizontal: false } },
                tooltip: { y: { formatter: v => '$' + fmt(v) } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9' }
            };

            if (chartProducts) {
                chartProducts.updateOptions(barOptions);
            } else {
                chartProducts = new ApexCharts(document.getElementById('chart-products'), barOptions);
                chartProducts.render();
            }

            // Grouped bar: purchase vs sale price
            const priceOptions = {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                series: [
                    { name: 'P. Compra', data: data.map(p => p.purchase_price) },
                    { name: 'P. Venta', data: data.map(p => p.sale_price) }
                ],
                xaxis: {
                    categories: data.map(p => p.name.length > 15 ? p.name.substring(0, 15) + '...' : p.name),
                    labels: { rotate: -45, style: { fontSize: '10px' } }
                },
                yaxis: { labels: { formatter: v => '$' + fmt(v) } },
                colors: ['#ef4444', '#16a34a'],
                plotOptions: { bar: { borderRadius: 3, horizontal: false } },
                tooltip: { y: { formatter: v => '$' + fmt(v) } },
                dataLabels: { enabled: false },
                legend: { position: 'top' },
                grid: { borderColor: '#f1f5f9' }
            };

            if (chartPrices) {
                chartPrices.updateOptions(priceOptions);
            } else {
                chartPrices = new ApexCharts(document.getElementById('chart-prices'), priceOptions);
                chartPrices.render();
            }

            // Update products table
            const tbody = document.getElementById('products-table-body');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">No hay datos para el período seleccionado.</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(p => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-sm font-medium text-gray-900">${p.name}</td>
                    <td class="px-6 py-3 text-center text-sm text-gray-700">${p.total_qty}</td>
                    <td class="px-6 py-3 text-right text-sm text-red-500">$${fmt(p.purchase_price)}</td>
                    <td class="px-6 py-3 text-right text-sm text-green-600 font-medium">$${fmt(p.sale_price)}</td>
                    <td class="px-6 py-3 text-right text-sm text-gray-700">$${fmt(p.revenue)}</td>
                    <td class="px-6 py-3 text-right text-sm text-red-500">$${fmt(p.cost)}</td>
                    <td class="px-6 py-3 text-right text-sm font-bold text-emerald-600">$${fmt(p.profit)}</td>
                    <td class="px-6 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${p.margin >= 30 ? 'bg-green-100 text-green-800' : (p.margin >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')}">
                            ${p.margin}%
                        </span>
                    </td>
                </tr>
            `).join('');
        }

        // Init
        document.addEventListener('DOMContentLoaded', loadAllCharts);
    </script>
    @endpush
</x-app-layout>
