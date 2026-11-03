<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#16a34a">

    <title>{{ config('app.name', 'Mi Tiendita') }}</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased app-body">

    {{-- ── App Shell ── --}}
    <div class="app-shell">

        {{-- Sidebar --}}
        @include('layouts.navigation')

        {{-- Main content area --}}
        <div class="app-content" id="app-content">

            {{-- Page Heading --}}
            @isset($header)
                <div class="app-page-header">
                    {{ $header }}
                </div>
            @endisset

            {{-- Page Content --}}
            <main class="app-main">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')

    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').then(() => {
                console.log('SW registered');
            }).catch(err => console.log('SW error:', err));
        }
    </script>

    <!-- Offline indicator -->
    <div id="offline-banner" class="offline-banner hidden">
        📡 Sin conexión — Las ventas se guardarán localmente
    </div>
    <script>
        function updateOnlineStatus() {
            document.getElementById('offline-banner').classList.toggle('hidden', navigator.onLine);
        }
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
    </script>
</body>

</html>