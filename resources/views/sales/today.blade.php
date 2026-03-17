<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📋 Ventas de Hoy
            </h2>
            <a href="{{ route('pos') }}" class="btn btn-success">
                🛒 Volver a Caja
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- KPI del día --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="metric-card border-l-4 border-l-green-500">
                    <p class="metric-card__title">💰 Ventas Hoy</p>
                    <p class="metric-card__value text-green-600">${{ number_format($totalToday, 0, ',', '.') }}</p>
                </div>
                <div class="metric-card border-l-4 border-l-blue-500">
                    <p class="metric-card__title">🧾 Transacciones</p>
                    <p class="metric-card__value text-blue-600">{{ $salesCountToday }}</p>
                </div>
                <div class="metric-card border-l-4 border-l-purple-500">
                    <p class="metric-card__title">📦 Productos Vendidos</p>
                    <p class="metric-card__value text-purple-600">{{ $productsSoldToday }}</p>
                </div>
                <div class="metric-card border-l-4 border-l-emerald-500">
                    <p class="metric-card__title">📈 Ganancia Estimada</p>
                    <p class="metric-card__value text-emerald-600">${{ number_format($profitToday, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Listado de ventas del día --}}
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-header__title">Detalle de ventas — {{ now()->format('d/m/Y') }}</h3>
                    <span class="text-sm text-gray-500">{{ $sales->count() }} ventas</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->created_at->format('H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <ul class="space-y-1">
                                            @foreach ($sale->items as $item)
                                                <li class="flex items-center gap-2">
                                                    <span>{{ $item->product->name ?? 'Eliminado' }}</span>
                                                    <span class="text-gray-400">×{{ $item->quantity }}</span>
                                                    <span class="text-gray-500">${{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                        ${{ number_format($sale->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        No hay ventas hoy.
                                        <a href="{{ route('pos') }}" class="text-blue-600 hover:underline">¡Abre la caja!</a>
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
