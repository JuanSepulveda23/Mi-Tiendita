<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ⚠️ Alertas de Vencimiento
            </h2>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                ← Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Estadísticas --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="metric-card border-l-4 border-l-blue-500">
                    <p class="metric-card__title">Total Activas</p>
                    <p class="metric-card__value text-blue-600">{{ $stats['total'] }}</p>
                </div>
                <div class="metric-card border-l-4 border-l-red-500">
                    <p class="metric-card__title">🚨 Urgentes</p>
                    <p class="metric-card__value text-red-600">{{ $stats['urgent'] }}</p>
                </div>
                <div class="metric-card border-l-4 border-l-orange-500">
                    <p class="metric-card__title">⚠️ Alto Riesgo</p>
                    <p class="metric-card__value text-orange-600">{{ $stats['high'] }}</p>
                </div>
                <div class="metric-card border-l-4 border-l-purple-500">
                    <p class="metric-card__title">Se Vencerán</p>
                    <p class="metric-card__value text-purple-600">{{ $stats['will_expire'] }}</p>
                </div>
            </div>

            {{-- Tabla de alertas --}}
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-header__title">Productos Próximos a Vencer</h3>
                    <span class="text-sm text-gray-500">{{ $stats['total'] }} alertas activas</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Días</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Venta/Día</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rotación</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Riesgo</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($alerts as $alert)
                                <tr class="hover:bg-gray-50 {{ $alert->risk_level === 'urgent' ? 'bg-red-50' : ($alert->risk_level === 'high' ? 'bg-orange-50' : '') }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $alert->product->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $alert->product->expiration_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-lg font-bold {{ $alert->days_remaining <= 1 ? 'text-red-600' : ($alert->days_remaining <= 2 ? 'text-orange-600' : 'text-yellow-600') }}">
                                            {{ max($alert->days_remaining, 0) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="badge badge-warning">{{ $alert->stock_at_alert }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        {{ $alert->avg_daily_sales ?? '-' }} ud/día
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold">
                                        @if($alert->rotation_days !== null)
                                            <span class="{{ $alert->will_expire ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $alert->rotation_days }} días
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($alert->will_expire)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                🚨 Pérdida
                                            </span>
                                        @else
                                            <span class="badge {{ $alert->risk_level === 'urgent' ? 'badge-danger' : ($alert->risk_level === 'high' ? 'badge-warning' : 'badge-success') }}">
                                                {{ $alert->risk_level === 'urgent' ? '🚨 Urgente' : ($alert->risk_level === 'high' ? '⚠️ Alto' : '🟡 Bajo') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <button 
                                            onclick="dismissAlert({{ $alert->id }})"
                                            class="btn btn-secondary btn-sm"
                                            title="Descartar esta alerta"
                                        >
                                            ✓ OK
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        ✅ No hay alertas. ¡Todo está bien!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($alerts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $alerts->links() }}
                    </div>
                @endif
            </div>

            {{-- Recomendaciones --}}
            <div class="mt-6 bg-blue-50 border border-blue-300 rounded-lg p-4">
                <h3 class="font-bold text-blue-900 mb-2">💡 Recomendaciones para gestionar vencimientos</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>✓ <strong>Riesgo alto (3 días):</strong> Considera crear una promoción o colocar al frente del estante</li>
                    <li>✓ <strong>Riesgo urgente (1 día):</strong> Aplica descuento inmediato para vender antes del vencimiento</li>
                    <li>✓ <strong>Riesgo de pérdida (se vencerá antes de venderse):</strong> Acción urgente requerida</li>
                    <li>✓ El sistema analiza rotación: si vendes 10 ud/día pero vence en 1 día y tienes 20, se marcará como pérdida inevitable</li>
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        async function dismissAlert(alertId) {
            if (!confirm('¿Descartar esta alerta?')) return;

            try {
                const response = await fetch(`/alerts/${alertId}/dismiss`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                alert('Error al descartar alerta');
                console.error(error);
            }
        }
    </script>
    @endpush
</x-app-layout>
