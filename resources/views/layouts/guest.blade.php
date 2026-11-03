@props(['fullWidth' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Mi Tiendita') }} - POS & Gestión</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg viewBox='0 0 40 40' fill='none' xmlns='http://www.w3.org/2000/svg'><rect width='40' height='40' rx='10' fill='%2316a34a'/><path d='M10 14h20M10 20h20M10 26h12' stroke='white' stroke-width='2.5' stroke-linecap='round'/><circle cx='30' cy='26' r='4' fill='white'/></svg>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-950 min-h-screen">
        @if($fullWidth)
            {{ $slot }}
        @else
            <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 px-4 py-8">
                <div class="mb-6 flex flex-col items-center">
                    <a href="/" class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-7 h-7">
                                <path d="M10 14h20M10 20h20M10 26h12" stroke="white" stroke-width="2.5" stroke-linecap="round" />
                                <circle cx="30" cy="26" r="4" fill="white" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <span class="block text-white font-bold text-xl leading-tight">Mi Tiendita</span>
                            <span class="block text-xs font-semibold text-emerald-400 tracking-wider">SISTEMA POS</span>
                        </div>
                    </a>
                </div>

                <div class="w-full sm:max-w-md px-8 py-8 bg-white/95 backdrop-blur-md shadow-2xl shadow-emerald-950/40 rounded-2xl border border-white/20">
                    {{ $slot }}
                </div>
            </div>
        @endif
    </body>
</html>
