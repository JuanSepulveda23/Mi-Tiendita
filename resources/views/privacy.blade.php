<x-guest-layout>
    <div class="max-w-3xl mx-auto py-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Política de Tratamiento de Datos Personales y Privacidad</h1>
        <p class="text-xs text-gray-500 mb-6">Conforme a la Ley 1581 de 2012 (Habeas Data) y el Reglamento General de Protección de Datos (RGPD / GDPR).</p>

        <div class="prose prose-sm text-gray-700 space-y-4">
            <section>
                <h2 class="text-base font-semibold text-gray-900">1. Responsable del Tratamiento</h2>
                <p>
                    <strong>Mi-Tiendita</strong> es responsable del tratamiento de los datos personales recolectados a través de esta plataforma de punto de venta y gestión interna.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900">2. Finalidades del Tratamiento</h2>
                <p>Los datos suministrados por los usuarios y colaboradores son utilizados exclusivamente para:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Gestión de acceso, autenticación y asignación de roles de seguridad (Administrador, Cajero).</li>
                    <li>Registro y trazabilidad de operaciones de venta, facturación y control de inventario.</li>
                    <li>Cumplimiento de obligaciones legales, fiscales y contables aplicables.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900">3. Derechos de los Titulares (Derechos ARCO)</h2>
                <p>
                    Como titular de sus datos personales, usted cuenta con el derecho de:
                </p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Acceso:</strong> Conocer los datos personales que reposan en nuestras bases de datos.</li>
                    <li><strong>Rectificación:</strong> Actualizar o rectificar datos inexactos o incompletos.</li>
                    <li><strong>Cancelación / Supresión:</strong> Solicitar la eliminación de sus datos personales cuando no exista un deber legal o fiscal de conservación.</li>
                    <li><strong>Oposición:</strong> Oponerse al tratamiento de sus datos para fines específicos.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900">4. Medidas de Seguridad y Confidencialidad</h2>
                <p>
                    Implementamos controles técnicos de acceso basado en roles (RBAC), bloqueo pesimista en base de datos, cifrado de contraseñas mediante algoritmos hash seguros (BCrypt/Argon2) y protección de cabeceras HTTP para garantizar la confidencialidad e integridad de la información.
                </p>
            </section>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-4 flex justify-between items-center">
            <a href="{{ url()->previous() }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                ← Regresar
            </a>
            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700">
                Ir al inicio de sesión
            </a>
        </div>
    </div>
</x-guest-layout>
