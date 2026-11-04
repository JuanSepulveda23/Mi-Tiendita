<x-guest-layout :fullWidth="true">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-slate-950 selection:bg-emerald-500 selection:text-white">

        {{-- ════════════════════════════════════════════════════════════
             COLUMNA IZQUIERDA: IDENTIDAD DE MARCA Y VALOR (DESKTOP)
             ════════════════════════════════════════════════════════════ --}}
        <div class="hidden lg:flex lg:col-span-7 flex-col justify-between p-12 lg:p-16 relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 border-r border-slate-800/80">
            {{-- Efectos de iluminación ambiental decorativa --}}
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-[30rem] h-[30rem] bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-emerald-600/10 rounded-full blur-2xl pointer-events-none"></div>

            {{-- Cabecera de Marca --}}
            <div class="relative z-10 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-500 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-500/30 ring-1 ring-white/20">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-7 h-7">
                        <path d="M10 14h20M10 20h20M10 26h12" stroke="white" stroke-width="2.5" stroke-linecap="round" />
                        <circle cx="30" cy="26" r="4" fill="white" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-extrabold text-2xl tracking-tight">Mi Tiendita</span>
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <span class="text-xs font-semibold text-emerald-400 tracking-widest uppercase">POS & Gestión de Tienda</span>
                </div>
            </div>

            {{-- Cuerpo Principal / Propuesta de Valor --}}
            <div class="relative z-10 my-auto py-12 max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-medium mb-6 backdrop-blur-sm">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400"></span>
                    Sistema POS Seguro &middot; Concurrencia Atómica
                </div>

                <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight tracking-tight mb-6">
                    Tu punto de venta, <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-300">más rápido y seguro.</span>
                </h1>

                <p class="text-slate-300 text-base leading-relaxed mb-10">
                    Control de inventario en tiempo real, ventas instantáneas con lector de código de barras, márgenes históricos protegidos y alertas de vencimiento automáticas.
                </p>

                {{-- Tarjetas de Características Clave --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm hover:bg-white/[0.07] transition-all">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Modo Caja Ultra Rápido</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">Cobro táctil en segundos, cálculo de cambio automático y búsqueda ágil.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm hover:bg-white/[0.07] transition-all">
                        <div class="w-9 h-9 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Stock Protegido</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">Bloqueo pesimista para evitar sobreventas concurrentes entre cajeros.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm hover:bg-white/[0.07] transition-all">
                        <div class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Alertas de Vencimiento</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">Predicción de rotación e identificación preventiva de lotes por expirar.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm hover:bg-white/[0.07] transition-all">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Rentabilidad Inmutable</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">Costo histórico congelado para reportes contables fidedignos.</p>
                    </div>
                </div>
            </div>

            {{-- Pie de columna izquierda --}}
            <div class="relative z-10 pt-6 border-t border-white/[0.08] flex items-center justify-between text-xs text-slate-400">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Terminal Operativa &middot; Versión 2.0
                </span>
                <a href="{{ route('privacy.policy') }}" class="hover:text-emerald-300 transition-colors">
                    Protección de Datos &middot; Habeas Data
                </a>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
             COLUMNA DERECHA: FORMULARIO DE ACCESO PROFESIONAL
             ════════════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-5 flex items-center justify-center p-6 sm:p-10 lg:p-12 relative">
            {{-- Resplandor suave para fondo --}}
            <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 to-slate-950 pointer-events-none"></div>

            <div class="w-full max-w-md bg-white rounded-3xl p-8 sm:p-10 shadow-2xl shadow-emerald-950/40 border border-slate-100 relative z-10">

                {{-- Logo visible solo en Mobile --}}
                <div class="lg:hidden flex items-center gap-3 mb-8 pb-6 border-b border-slate-100 justify-center">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shadow-md shadow-emerald-500/30">
                        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6">
                            <path d="M10 14h20M10 20h20M10 26h12" stroke="white" stroke-width="2.5" stroke-linecap="round" />
                            <circle cx="30" cy="26" r="4" fill="white" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-slate-900 font-extrabold text-xl leading-tight">Mi Tiendita</span>
                        <span class="block text-[11px] font-semibold text-emerald-600 tracking-wider uppercase">POS & Gestión</span>
                    </div>
                </div>

                {{-- Título y Bienvenida --}}
                <div class="mb-8">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Iniciar Sesión</h2>
                    <p class="text-sm text-slate-500 mt-1.5 leading-relaxed">
                        Ingresa tus credenciales para abrir turno en caja o acceder al panel administrativo.
                    </p>
                </div>

                {{-- Estado de la sesión --}}
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
                    @csrf

                    {{-- Campo: Correo Electrónico --}}
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="tu-correo@mitiendita.com"
                                class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Campo: Contraseña con Toggle Interactivo --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Contraseña
                            </label>
                        </div>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full pl-11 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                                title="Mostrar / Ocultar contraseña"
                            >
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Recordar contraseña y Recuperación --}}
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none group">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="rounded-md border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500 w-4 h-4 cursor-pointer"
                            />
                            <span class="ms-2.5 text-xs font-medium text-slate-600 group-hover:text-slate-900 transition-colors">
                                Recordar contraseña
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    {{-- Botón de Acción Principal --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/40 active:scale-[0.99] transition-all focus:outline-none focus:ring-4 focus:ring-emerald-500/20 text-sm cursor-pointer"
                        >
                            <span>Ingresar al Sistema</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Cuentas de Acceso Rápido / Guía Interna --}}
                <div x-data="{ openDemo: false }" class="mt-8 pt-6 border-t border-slate-100">
                    <button
                        type="button"
                        @click="openDemo = !openDemo"
                        class="w-full flex items-center justify-between text-xs text-slate-500 hover:text-emerald-700 transition-colors font-medium p-2 rounded-lg hover:bg-slate-50"
                    >
                        <span class="flex items-center gap-1.5">
                            <span class="text-amber-500">💡</span>
                            <span>Cuentas de demostración iniciales</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="{ 'rotate-180': openDemo }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openDemo" x-transition class="mt-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/70 text-xs space-y-2" style="display: none;">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-purple-700 flex items-center gap-1">
                                <span>👑</span> Admin:
                            </span>
                            <code class="text-[11px] bg-white px-2 py-0.5 rounded-md border border-slate-200 font-mono text-slate-700">admin@mitiendita.com</code>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-emerald-700 flex items-center gap-1">
                                <span>🛒</span> Cajero:
                            </span>
                            <code class="text-[11px] bg-white px-2 py-0.5 rounded-md border border-slate-200 font-mono text-slate-700">cajero@mitiendita.com</code>
                        </div>
                        <p class="text-[10px] text-slate-400 pt-1 border-t border-slate-200 text-center">
                            Claves predeterminadas del seeder: <span class="font-mono text-slate-600 font-bold">Admin123*</span> / <span class="font-mono text-slate-600 font-bold">Cajero123*</span>
                        </p>
                    </div>
                </div>

                {{-- Aviso de Privacidad y Restricción --}}
                <div class="mt-6 text-center">
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        🔒 Acceso restringido para personal autorizado.<br>
                        Tratamiento de datos regido por la <a href="{{ route('privacy.policy') }}" class="underline hover:text-emerald-600 transition-colors font-medium">Política de Privacidad</a>.
                    </p>
                </div>

            </div>
        </div>

    </div>
</x-guest-layout>
