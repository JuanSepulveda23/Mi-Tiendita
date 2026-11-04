<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl sm:text-2xl text-white leading-tight">
                📦 Productos
            </h2>
            <p class="text-emerald-100 text-xs sm:text-sm mt-0.5 sm:mt-1">Administra tu inventario y precios.</p>
        </div>
        @if(Auth::user()->isAdmin())
            <a href="{{ route('products.create') }}"
                class="btn bg-white/20 text-white hover:bg-white/30 border border-emerald-400 shadow-sm backdrop-blur-sm transition-all px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium">
                + Nuevo Producto
            </a>
        @endif
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">

            {{-- Mensaje de éxito --}}
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Alerta de bajo stock --}}
            @php
                $lowStockCount = $products->filter(fn($p) => $p->stock < $p->min_stock)->count();
            @endphp
            @if ($lowStockCount > 0)
                <div class="alert alert-warning">
                    ⚠️ Hay {{ $lowStockCount }} producto(s) con stock bajo.
                </div>
            @endif

            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-header__title">Listado de productos</h3>
                    <span class="text-xs sm:text-sm text-gray-500">{{ $products->total() }} productos</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Producto</th>
                                <th
                                    class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Código</th>
                                <th
                                    class="px-3 sm:px-6 py-2.5 sm:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    P. Compra</th>
                                <th
                                    class="px-3 sm:px-6 py-2.5 sm:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    P. Venta</th>
                                <th
                                    class="px-3 sm:px-6 py-2.5 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock</th>
                                <th
                                    class="px-3 sm:px-6 py-2.5 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Frecuente</th>
                                <th
                                    class="px-3 sm:px-6 py-2.5 sm:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                                        {{ $product->name }}
                                        @if($product->category)
                                            <span class="text-[11px] sm:text-xs text-gray-400 ml-1">({{ $product->category }})</span>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 font-mono">
                                        {{ $product->barcode ?? '—' }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 text-right">
                                        ${{ number_format($product->purchase_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-right font-medium">
                                        ${{ number_format($product->sale_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-center">
                                        @if ($product->stock < $product->min_stock)
                                            <span class="badge badge-danger text-[11px] sm:text-xs">{{ $product->stock }}</span>
                                        @elseif ($product->stock < ($product->min_stock * 3))
                                            <span class="badge badge-warning text-[11px] sm:text-xs">{{ $product->stock }}</span>
                                        @else
                                            <span class="badge badge-success text-[11px] sm:text-xs">{{ $product->stock }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-center">
                                        <button onclick="toggleFrequent({{ $product->id }}, this)"
                                            class="text-lg sm:text-xl cursor-pointer hover:scale-125 transition-transform"
                                            title="{{ $product->is_frequent ? 'Quitar de frecuentes' : 'Marcar como frecuente' }}">
                                            {{ $product->is_frequent ? '⭐' : '☆' }}
                                        </button>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2.5 sm:py-4 whitespace-nowrap text-right text-xs sm:text-sm">
                                        @if(Auth::user()->isAdmin())
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('products.edit', $product) }}"
                                                    class="btn btn-secondary btn-sm text-xs">
                                                    Editar
                                                </a>
                                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                                    class="inline" onsubmit="return confirm('¿Eliminar este producto?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm text-xs">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 sm:px-6 py-8 sm:py-12 text-center text-xs sm:text-sm text-gray-500">
                                        No hay productos registrados.
                                        <a href="{{ route('products.create') }}" class="text-blue-600 hover:underline">Crear uno</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($products->hasPages())
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            async function toggleFrequent(productId, btn) {
                try {
                    const response = await fetch(`/products/${productId}/toggle-frequent`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    btn.textContent = data.is_frequent ? '⭐' : '☆';
                    btn.title = data.is_frequent ? 'Quitar de frecuentes' : 'Marcar como frecuente';
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        </script>
    @endpush
</x-app-layout>