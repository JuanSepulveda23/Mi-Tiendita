<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Productos
            </h2>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                + Nuevo Producto
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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
                    <span class="text-sm text-gray-500">{{ $products->total() }} productos</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">P. Compra</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">P. Venta</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Frecuente</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $product->name }}
                                        @if($product->category)
                                            <span class="text-xs text-gray-400 ml-1">{{ $product->category }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->barcode ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        ${{ number_format($product->purchase_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                        ${{ number_format($product->sale_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($product->stock < $product->min_stock)
                                            <span class="badge badge-danger">{{ $product->stock }}</span>
                                        @elseif ($product->stock < ($product->min_stock * 3))
                                            <span class="badge badge-warning">{{ $product->stock }}</span>
                                        @else
                                            <span class="badge badge-success">{{ $product->stock }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button
                                            onclick="toggleFrequent({{ $product->id }}, this)"
                                            class="text-xl cursor-pointer hover:scale-125 transition-transform"
                                            title="{{ $product->is_frequent ? 'Quitar de frecuentes' : 'Marcar como frecuente' }}"
                                        >
                                            {{ $product->is_frequent ? '⭐' : '☆' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary btn-sm mr-1">
                                            Editar
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este producto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        No hay productos registrados.
                                        <a href="{{ route('products.create') }}" class="text-blue-600 hover:underline">Crear uno</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($products->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
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
