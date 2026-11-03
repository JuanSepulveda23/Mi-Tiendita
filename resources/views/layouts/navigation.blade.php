<aside x-data="{ open: false, mobileOpen: false }" class="sidebar" :class="{ 'sidebar--mobile-open': mobileOpen }">

    {{-- ── Logo ── --}}
    <div class="sidebar__logo">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="sidebar__logo-icon">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8">
                    <rect width="40" height="40" rx="10" fill="#16a34a" />
                    <path d="M10 14h20M10 20h20M10 26h12" stroke="white" stroke-width="2.5" stroke-linecap="round" />
                    <circle cx="30" cy="26" r="4" fill="white" />
                </svg>
            </div>
            <div class="sidebar__logo-text">
                <span class="sidebar__logo-name">Mi Tiendita</span>
                <span class="sidebar__logo-sub">POS System</span>
            </div>
        </a>
    </div>

    {{-- ── Nav Links ── --}}
    <nav class="sidebar__nav">
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="chart">
            Dashboard
        </x-sidebar-link>

        <x-sidebar-link :href="route('pos')" :active="request()->routeIs('pos')" icon="cart">
            Caja
        </x-sidebar-link>

        <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="box">
            Productos
        </x-sidebar-link>

        <x-sidebar-link :href="route('intelligence.predictions')" :active="request()->routeIs('intelligence.*')"
            icon="magic">
            Predicciones
        </x-sidebar-link>

        <x-sidebar-link :href="route('reports.profits')" :active="request()->routeIs('reports.*')" icon="money">
            Ganancias
        </x-sidebar-link>
    </nav>

    {{-- ── User Footer ── --}}
    <div class="sidebar__footer">
        <div class="sidebar__user">
            <div class="sidebar__user-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="sidebar__user-info">
                <span class="sidebar__user-name">{{ Auth::user()->name }}</span>
                <span class="sidebar__user-email">{{ Auth::user()->email }}</span>
            </div>
        </div>
        <div class="sidebar__user-actions">
            <a href="{{ route('profile.edit') }}" class="sidebar__action-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Perfil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar__action-link sidebar__action-link--danger">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Salir
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ── Mobile Overlay ── --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeMobileSidebar()"></div>

{{-- ── Mobile Toggle Button ── --}}
<button class="sidebar-mobile-toggle" id="sidebar-mobile-toggle" onclick="toggleMobileSidebar()"
    aria-label="Toggle sidebar">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<script>
    function toggleMobileSidebar() {
        document.querySelector('.sidebar').classList.toggle('sidebar--mobile-open');
        document.getElementById('sidebar-overlay').classList.toggle('active');
    }
    function closeMobileSidebar() {
        document.querySelector('.sidebar').classList.remove('sidebar--mobile-open');
        document.getElementById('sidebar-overlay').classList.remove('active');
    }
</script>