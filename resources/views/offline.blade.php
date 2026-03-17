<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Sin Conexión
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                <div class="text-6xl mb-4">📡</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Sin conexión a Internet</h2>
                <p class="text-gray-500 mb-6">No te preocupes, puedes seguir usando el Modo Caja. Las ventas se guardarán localmente y se sincronizarán cuando vuelvas a tener conexión.</p>
                <a href="/pos" class="btn btn-success text-lg px-8 py-3">
                    🛒 Ir a Modo Caja
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
