<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Movikaa')</title>
    <meta name="description" content="Acceso al marketplace y backoffice de Movikaa."/>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/home.css', 'resources/js/app.js'])
    @yield('head')
</head>
<body class="theme-dark bg-black text-white font-body">
    <div class="fixed inset-0 z-0 overflow-hidden">
        <img 
            src="/luxury_car_showroom_dark_1775669453755.png" 
            class="h-full w-full scale-105 object-cover opacity-50 blur-xl animate-slow-zoom" 
            alt="Background"
        >
        <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/40 to-black/90"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_transparent_0%,_#000_100%)] opacity-60"></div>
    </div>

    <main class="relative z-10 flex min-h-screen items-center justify-center py-12 px-4">
        @yield('content')
    </main>

    <style>
        @keyframes slowZoom {
            0% { transform: scale(1.05) translate(0, 0); }
            100% { transform: scale(1.2) translate(-1%, -1%); }
        }
        .animate-slow-zoom {
            animation: slowZoom 30s linear infinite alternate;
        }
    </style>
</body>
</html>


