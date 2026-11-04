<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-white leading-tight">
            📦 Nuevo Producto
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">

                <form action="{{ route('products.store') }}" method="POST">
                    @csrf

                    @include('products._form')

                    <div class="flex flex-wrap items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Guardar Producto
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
