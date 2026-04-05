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
<body class="{{ ($publicTheme ?? 'light') === 'dark' ? 'theme-dark bg-[#05070b] text-white' : 'bg-background text-on-background' }} font-body">
    @php
        $sellUrl = auth()->check() && auth()->user()->hasRole('seller', 'dealer', 'admin') ? route('seller.dashboard') : route('seller.onboarding.create');
        $accountUrl = auth()->check()
            ? (auth()->user()->hasRole('admin')
                ? route('admin.dashboard')
                : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')))
            : route('login');
    @endphp

    <nav class="fixed top-0 z-50 w-full border-b border-outline-variant/30 bg-white/80 backdrop-blur-md {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-[#0a0d12]/85' : '' }}" data-topbar>
        <div class="mx-auto flex h-20 w-full max-w-screen-2xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-10">
                <a href="{{ route('home') }}" class="font-headline text-3xl font-extrabold tracking-tighter text-primary">Movikaa</a>
                <div class="hidden items-center gap-8 md:flex">
                    <a href="{{ route('catalog.index') }}" class="font-headline text-sm font-bold tracking-tight text-slate-600 transition-colors hover:text-primary">Comprar</a>
                    <a href="{{ route('valuation.index') }}" class="font-headline text-sm font-bold tracking-tight text-slate-600 transition-colors hover:text-primary">Tasador</a>
                    <a href="{{ route('seller.onboarding.create') }}" class="font-headline text-sm font-bold tracking-tight text-slate-600 transition-colors hover:text-primary">Vender</a>
                    <a href="{{ route('catalog.index') }}#destacados" class="font-headline text-sm font-bold tracking-tight text-slate-600 transition-colors hover:text-primary">Destacados</a>
                </div>
            </div>
            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ $accountUrl }}" class="px-5 py-2 text-sm font-semibold text-slate-600 transition-colors hover:text-primary">Ingresar</a>
                <a href="{{ $sellUrl }}" class="inline-flex items-center justify-center rounded-md bg-secondary px-5 py-2.5 font-headline text-sm font-extrabold text-white shadow-lg transition-colors hover:bg-secondary-container">Vender mi auto</a>
            </div>
            <button class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-outline-variant/40 bg-white/70 text-primary md:hidden" type="button" aria-label="Abrir menu" aria-expanded="false" data-menu-toggle>
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
        <div class="mobile-menu border-t border-outline-variant/20 md:hidden" hidden data-mobile-menu>
            <div class="mx-auto grid w-full max-w-screen-2xl gap-3 px-4 py-4 sm:px-6">
                <a href="{{ route('catalog.index') }}" class="rounded-xl border border-outline-variant/20 px-4 py-3 font-semibold text-slate-700">Comprar</a>
                <a href="{{ route('valuation.index') }}" class="rounded-xl border border-outline-variant/20 px-4 py-3 font-semibold text-slate-700">Tasador</a>
                <a href="{{ route('seller.onboarding.create') }}" class="rounded-xl border border-outline-variant/20 px-4 py-3 font-semibold text-slate-700">Vender mi auto</a>
                <a href="{{ $accountUrl }}" class="rounded-xl border border-outline-variant/20 px-4 py-3 font-semibold text-slate-700">Ingresar</a>
            </div>
        </div>
    </nav>

    <main class="relative overflow-hidden pt-20">
        @yield('content')
    </main>
</body>
</html>
