<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva Venta
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

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

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form action="{{ route('sales.store') }}" method="POST" id="sale-form">
                    @csrf

                    <div id="products-container">
                        {{-- Fila de producto dinámica --}}
                    </div>

                    <button
                        type="button"
                        id="add-product-btn"
                        class="btn btn-secondary mt-4"
                    >
                        + Agregar Producto
                    </button>

                    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
                        <p class="text-lg font-semibold text-gray-900">
                            Total: $<span id="sale-total">0.00</span>
                        </p>
                        <div class="flex gap-3">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">
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
