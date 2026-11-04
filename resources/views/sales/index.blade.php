<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl sm:text-2xl text-white leading-tight">
                Historial de Ventas
            </h2>
        </div>
        <a href="{{ route('sales.create') }}"
            class="btn bg-white/20 text-white hover:bg-white/30 border border-emerald-400 shadow-sm backdrop-blur-sm transition-all px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium">
            + Nueva Venta
        </a>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-header__title">Ventas realizadas</h3>
                    <span class="text-xs sm:text-sm text-gray-500">{{ $sales->total() }} ventas</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                                <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                        {{ $sale->id }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                        {{ $sale->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 text-xs sm:text-sm text-gray-700">
                                        <ul class="space-y-1">
                                            @foreach ($sale->items as $item)
                                                <li>
                                                    {{ $item->product->name }}
                                                    <span class="text-gray-400">×{{ $item->quantity }}</span>
                                                    <span class="text-gray-500">${{ number_format($item->price * $item->quantity, 2) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-semibold text-gray-900 text-right">
                                        ${{ number_format($sale->total, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 sm:px-6 py-8 sm:py-12 text-center text-xs sm:text-sm text-gray-500">
                                        No hay ventas registradas.
                                        <a href="{{ route('sales.create') }}" class="text-blue-600 hover:underline">Registrar una</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($sales->hasPages())
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200">
                        {{ $sales->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
