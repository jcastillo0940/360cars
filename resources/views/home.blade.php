@extends('layouts.marketing')

@section('title', 'Movikaa | Encuentra tu próximo auto en Costa Rica')

@section('head')
<link rel="preload" as="image" href="/img/home-hero-showroom.webp" type="image/webp" fetchpriority="high"/>
@vite(['resources/css/home.css', 'resources/js/home.jsx'])
@endsection

@section('content')
@php
    $catalogUrl = route('catalog.index');
    $valuationUrl = route('valuation.index');
    $brandsUrl = route('brands.index');
    $sellUrl = route('seller.onboarding.create');
    $accountUrl = auth()->check()
        ? (auth()->user()->hasRole('admin')
            ? route('admin.dashboard')
            : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')))
        : route('login');
    $firstName = auth()->check() ? trim(strtok((string) auth()->user()->name, ' ')) : null;
    $authUser = auth()->check() ? [
        'authenticated' => true,
        'firstName' => $firstName ?: 'Cuenta',
        'dashboardUrl' => $accountUrl,
        'buyerUrl' => route('buyer.dashboard'),
    ] : [
        'authenticated' => false,
    ];

    $homeProps = [
        'homeUrl' => route('home'),
        'brandsUrl' => $brandsUrl,
        'catalogUrl' => $catalogUrl,
        'valuationUrl' => $valuationUrl,
        'sellUrl' => $sellUrl,
        'accountUrl' => $accountUrl,
        'loginUrl' => route('login'),
        'authUser' => $authUser,
        'publicTheme' => $publicTheme ?? 'light',
        'featuredVehicles' => $featuredVehicles,
        'recentVehicles' => $recentVehicles,
        'offerVehicles' => $offerVehicles,
        'catalogMakes' => $catalogMakes,
        'catalogProvinces' => $catalogProvinces,
        'catalogPriceCeiling' => $catalogPriceCeiling,
        'catalogYearRange' => $catalogYearRange,
        'footerLinks' => [
            'termsUrl' => route('legal.terms'),
            'privacyUrl' => route('legal.privacy'),
            'cookiesUrl' => route('legal.cookies'),
        ],
    ];
    $isDarkTheme = ($publicTheme ?? 'light') === 'dark';
    $heroVehicles = collect($recentVehicles)->take(2);
@endphp
<div id="home-react" data-props='@json($homeProps)'>
    <div class="font-body md:pb-0 {{ $isDarkTheme ? 'theme-dark bg-[#05070b] pb-20 text-white' : 'bg-background pb-20 text-on-background' }}">
        <div class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-transparent">
            <div class="mx-auto flex h-20 max-w-screen-2xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4 lg:gap-12">
                    <a href="{{ route('home') }}" class="text-white">
                        <span class="flex items-center">
                            <picture>
                                <source
                                    srcset="/img/logo-160.webp 160w, /img/logo-320.webp 320w"
                                    sizes="(min-width: 640px) 160px, 128px"
                                    type="image/webp"
                                >
                                <img
                                    src="/img/logo.png"
                                    alt="Movikaa"
                                    class="h-8 w-auto object-contain sm:h-10"
                                    width="160"
                                    height="56"
                                    decoding="async"
                                    fetchpriority="high"
                                >
                            </picture>
                        </span>
                    </a>
                    <div class="hidden md:flex md:gap-6 lg:gap-8">
                        <a href="{{ $catalogUrl }}" class="font-headline text-sm font-bold tracking-tight text-white/80 transition-colors hover:text-white lg:text-base">Comprar</a>
                        <a href="{{ $brandsUrl }}" class="font-headline text-sm font-bold tracking-tight text-white/80 transition-colors hover:text-white lg:text-base">Marcas</a>
                        <a href="{{ $catalogUrl }}?featured=1" class="font-headline text-sm font-bold tracking-tight text-white/80 transition-colors hover:text-white lg:text-base">Destacados</a>
                        <a href="{{ $valuationUrl }}" class="font-headline text-sm font-bold tracking-tight text-white/80 transition-colors hover:text-white lg:text-base">Estimación de mercado</a>
                        <a href="/noticias" class="font-headline text-sm font-bold tracking-tight text-white/80 transition-colors hover:text-white lg:text-base">Noticias</a>
                    </div>
                </div>
                <div class="hidden items-center gap-4 md:flex">
                    <a href="{{ $sellUrl }}" class="rounded-full bg-secondary px-6 py-2.5 font-headline text-sm font-bold text-slate-950 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-[#ffb83a] hover:shadow-md">Vender auto</a>
                    <a href="{{ $accountUrl }}" class="px-5 py-2 text-sm font-bold text-white/80 transition hover:text-white">{{ $authUser['authenticated'] ? 'Hola, ' . $authUser['firstName'] : 'Ingresar' }}</a>
                </div>
                <div class="flex items-center gap-2 md:hidden">
                    <a href="{{ $sellUrl }}" class="rounded-full bg-secondary px-4 py-2 font-headline text-xs font-bold text-slate-950 shadow-sm transition-colors hover:bg-[#ffb83a]">Vender</a>
                </div>
            </div>
        </div>

        <main>
            <section class="relative isolate flex min-h-[80vh] flex-col items-center justify-center overflow-hidden border-b border-outline-variant/5 bg-[#05070b] py-12 text-white sm:min-h-screen sm:py-20">
                <div class="absolute inset-0 z-0 overflow-hidden">
                    <img
                        src="/img/home-hero-showroom.webp"
                        class="h-full w-full object-cover opacity-55"
                        alt="Background"
                        width="960"
                        height="960"
                        fetchpriority="high"
                        decoding="async"
                        loading="eager"
                    >
                    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-[#05070b]"></div>
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_transparent_20%,_#05070b_100%)] opacity-80"></div>
                </div>

                <div class="relative z-10 mx-auto w-full max-w-screen-2xl px-4 text-center sm:px-6 lg:px-8">
                    <h1 class="mx-auto max-w-4xl font-headline text-4xl font-extrabold tracking-tight text-white sm:text-7xl lg:text-8xl">
                        Compra y vende autos de forma inteligente
                    </h1>
                    <p class="mx-auto mt-4 max-w-2xl text-base font-medium text-white/80 sm:mt-6 sm:text-xl">
                        Compara precios, analiza opciones y encuentra tu vehículo ideal.
                    </p>

                    @if ($heroVehicles->isNotEmpty())
                        <div class="mt-10 grid gap-4 lg:hidden">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Autos primero</h2>
                                <a href="{{ $catalogUrl }}" class="text-sm font-bold text-white/80 hover:text-white">Ver todos</a>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                @foreach ($heroVehicles as $vehicle)
                                    <a href="{{ $vehicle['url'] }}" class="group overflow-hidden rounded-[1.75rem] border border-outline-variant/20 bg-white shadow-xl transition-all hover:-translate-y-1 hover:shadow-2xl">
                                        <div class="relative h-52 overflow-hidden">
                                            <img
                                                src="{{ $vehicle['image_thumb'] ?: $vehicle['image'] }}"
                                                @if (! empty($vehicle['image_thumb']) && ! empty($vehicle['image_thumb_width']) && ! empty($vehicle['image']))
                                                    srcset="{{ $vehicle['image_thumb'] }} {{ $vehicle['image_thumb_width'] }}w, {{ $vehicle['image'] }} {{ $vehicle['image_width'] ?: 1600 }}w"
                                                    sizes="(min-width: 640px) 50vw, 100vw"
                                                @endif
                                                alt="{{ $vehicle['title'] }}"
                                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                width="{{ $vehicle['image_width'] ?: 1600 }}"
                                                height="{{ $vehicle['image_height'] ?: 900 }}"
                                                decoding="async"
                                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                                fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                                            >
                                        </div>
                                        <div class="p-5 sm:p-6">
                                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{{ trim(($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '')) }}</p>
                                            <p class="mt-2 font-headline text-2xl font-extrabold tracking-tight text-slate-950">{{ $vehicle['title'] }}</p>
                                            <p class="mt-2 text-sm text-slate-500">{{ $vehicle['year'] }} · {{ $vehicle['province'] ?: ($vehicle['city'] ?: 'Costa Rica') }} · {{ $vehicle['published_label'] }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mx-auto mt-12 w-full max-w-5xl">
                        <form action="{{ $catalogUrl }}" method="GET" class="hero-search-shell shadow-2xl">
                            <div class="hero-search-grid">
                                <section class="hero-search-block">
                                    <span class="hero-search-label">VEHÍCULO</span>
                                    <div class="flex gap-2">
                                        <div class="flex-1">
                                            <label for="ssr-make" class="hero-select-pill">
                                                <span class="sr-only">Marca del vehículo</span>
                                                <select id="ssr-make" name="make" class="public-hero-select hero-search-select">
                                                    <option value="">Marca</option>
                                                    @foreach ($catalogMakes as $make)
                                                        <option value="{{ $make['name'] }}">{{ $make['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                        </div>
                                        <div class="flex-1">
                                            <label for="ssr-model" class="hero-select-pill">
                                                <span class="sr-only">Modelo del vehículo</span>
                                                <select id="ssr-model" class="public-hero-select hero-search-select" disabled>
                                                    <option value="">Modelo (ej)</option>
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                </section>

                                <section class="hero-search-block">
                                    <span class="hero-search-label">PRECIO</span>
                                    <div class="flex flex-col gap-1">
                                        <strong class="hero-search-value">₡0 - Sin límite</strong>
                                        <p class="hero-price-meta"><span>Usa los filtros avanzados al cargar la página</span><span>Rango completo</span></p>
                                    </div>
                                </section>

                                <section class="hero-search-block">
                                    <span class="hero-search-label">AÑO</span>
                                    <div class="flex flex-col gap-1">
                                        <strong class="hero-search-value">{{ $catalogYearRange['min'] }} - {{ $catalogYearRange['max'] }}</strong>
                                        <p class="hero-price-meta"><span>Usa los filtros avanzados al cargar la página</span><span>Rango completo</span></p>
                                    </div>
                                </section>

                                <section class="hero-search-block">
                                    <span class="hero-search-label">UBICACIÓN</span>
                                    <label for="ssr-province" class="hero-select-pill">
                                        <span class="sr-only">Provincia</span>
                                        <select id="ssr-province" name="province" class="public-hero-select hero-search-select">
                                            <option value="">Todo el país</option>
                                            @foreach ($catalogProvinces as $province)
                                                <option value="{{ $province }}">{{ $province }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </section>

                                <div class="hero-search-cta">
                                    <button type="submit" class="hero-search-submit">
                                        <span>
                                            <strong>Buscar autos</strong>
                                            <small>Búsqueda refinada</small>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-8 flex justify-center">
                            <a href="{{ $valuationUrl }}" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-black/40 px-6 py-2.5 text-sm font-bold text-white backdrop-blur-md transition hover:bg-black/60">
                                <span>Estimación de mercado</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
@endsection

