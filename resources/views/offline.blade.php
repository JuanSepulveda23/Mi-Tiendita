<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-white leading-tight">
            📡 Sin Conexión
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8 text-center">
                <div class="text-5xl sm:text-6xl mb-4">📡</div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Sin conexión a Internet</h2>
                <p class="text-sm sm:text-base text-gray-500 mb-6">No te preocupes, puedes seguir usando el Modo Caja. Las ventas se guardarán localmente y se sincronizarán cuando vuelvas a tener conexión.</p>
                <a href="/pos" class="btn btn-success text-base sm:text-lg px-6 sm:px-8 py-2.5 sm:py-3">
                    🛒 Ir a Modo Caja
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
