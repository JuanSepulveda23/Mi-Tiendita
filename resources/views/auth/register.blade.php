<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-900">Registrar Nuevo Usuario</h2>
        <p class="text-sm text-gray-600">Acción exclusiva para administradores. Asigna el rol correspondiente al empleado.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre Completo')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Rol del Usuario')" />
            <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cajero (Solo POS y ventas)</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador (Acceso total)</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Términos y Política de Tratamiento de Datos Personales -->
        <div class="mt-4">
            <label for="terms" class="inline-flex items-start cursor-pointer">
                <input id="terms" type="checkbox" name="terms" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mt-1" required {{ old('terms') ? 'checked' : '' }}>
                <span class="ms-2 text-xs text-gray-600 leading-normal">
                    Declaro que el empleado consiente el tratamiento de sus datos personales conforme a la 
                    <a href="{{ route('privacy.policy') }}" target="_blank" class="underline text-indigo-600 hover:text-indigo-800 font-medium">Política de Tratamiento de Datos Personales (Habeas Data / GDPR)</a>.
                </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('dashboard') }}">
                {{ __('← Volver al Dashboard') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Crear Usuario') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
