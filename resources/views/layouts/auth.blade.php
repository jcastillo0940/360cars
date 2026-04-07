<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', '360Cars')</title>
    <meta name="description" content="Acceso al marketplace y backoffice de 360Cars."/>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/home.css', 'resources/js/app.js'])
    @yield('head')
</head>
<body class="theme-dark bg-black text-white font-body">
    <nav class="fixed top-0 z-50 w-full border-b border-white/10 bg-black/90 backdrop-blur-md" data-topbar>
        <div class="mx-auto flex h-20 w-full max-w-screen-2xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="font-headline text-3xl font-extrabold tracking-tighter text-primary">Movikaa</a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-primary hover:text-white">Volver al inicio</a>
        </div>
    </nav>

    <main class="relative overflow-hidden pt-20">
        @yield('content')
    </main>
</body>
</html>