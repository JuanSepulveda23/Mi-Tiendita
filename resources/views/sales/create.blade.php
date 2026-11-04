<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-white leading-tight">
            🧾 Nueva Venta
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <form action="{{ route('sales.store') }}" method="POST" id="sale-form">
                    @csrf

                    <div id="products-container">
                        {{-- Fila de producto dinámica --}}
                    </div>

                    <button
                        type="button"
                        id="add-product-btn"
                        class="btn btn-secondary mt-4 text-xs sm:text-sm"
                    >
                        + Agregar Producto
                    </button>

                    <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-4 border-t border-gray-200">
                        <p class="text-base sm:text-lg font-semibold text-gray-900">
                            Total: $<span id="sale-total">0.00</span>
                        </p>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary text-xs sm:text-sm">Cancelar</a>
                            <button type="submit" class="btn btn-success text-xs sm:text-sm">
                                Registrar Venta
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Datos de productos para JavaScript (separación de responsabilidades) --}}
    <script>
        window.availableProducts = @json($products);
    </script>

    {{-- JavaScript separado para lógica de ventas --}}
    @vite('resources/js/sales-create.js')
</x-app-layout>
