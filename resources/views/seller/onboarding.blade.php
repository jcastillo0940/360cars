@extends('layouts.marketing')

@section('title', 'Vende Tu Auto | Movikaa')

@section('head')
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    @vite(['resources/css/home.css', 'resources/js/app.js'])
@endsection

@section('content')
    @php
        $prefill = $prefill ?? [];
        $homeUrl = route('home');
        $catalogUrl = route('catalog.index');
        $valuationUrl = route('valuation.index');
        $sellUrl = auth()->check() && auth()->user()->hasRole('seller', 'dealer', 'admin') ? route('seller.dashboard') : route('seller.onboarding.create');
        $accountUrl = auth()->check()
            ? (auth()->user()->hasRole('admin')
                ? route('admin.dashboard')
                : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')))
            : route('login');
        $firstName = auth()->check() ? trim(strtok((string) auth()->user()->name, ' ')) : null;
    @endphp
    <div class="seller-onboarding-page theme-dark bg-black text-white min-h-screen font-body">
        <nav class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-black/90 backdrop-blur-md" data-topbar>
            <div class="mx-auto flex h-20 max-w-screen-2xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4 lg:gap-12">
                    <button type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/10 text-primary transition hover:bg-white/5 md:hidden"
                        data-menu-toggle aria-expanded="false" aria-label="Abrir menu">
                        <span class="material-symbols-outlined text-[24px]">menu</span>
                    </button>
                    <a href="{{ route('home') }}"
                        class="font-headline text-2xl font-black tracking-tight text-primary sm:text-3xl">Movikaa</a>
                    <div class="hidden items-center gap-8 md:flex">
                        <a href="{{ route('catalog.index') }}"
                            class="font-headline text-sm font-bold tracking-tight text-slate-200 transition hover:text-primary">Comprar</a>
                        <a href="{{ route('home') }}#destacados"
                            class="font-headline text-sm font-bold tracking-tight text-slate-200 transition hover:text-primary">Destacados</a>
                        <a href="{{ route('valuation.index') }}"
                            class="font-headline text-sm font-bold tracking-tight text-slate-200 transition hover:text-primary">Estimación
                            de mercado</a>
                        <a href="{{ route('home') }}#noticias"
                            class="font-headline text-sm font-bold tracking-tight text-slate-200 transition hover:text-primary">Noticias</a>
                    </div>
                </div>
                <div class="hidden items-center gap-4 md:flex">
                    @if (auth()->check())
                        <details class="relative">
                            <summary
                                class="inline-flex list-none items-center gap-3 rounded-full border border-white/10 bg-[#11131a] px-4 py-2 text-sm font-bold text-white transition hover:border-primary hover:text-primary">
                                <span
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary-fixed text-primary">
                                    <span class="material-symbols-outlined text-[20px]">person</span>
                                </span>
                                <span>Hola, {{ $firstName ?: 'Cuenta' }}</span>
                                <span class="material-symbols-outlined text-[18px]">expand_more</span>
                            </summary>
                            <div
                                class="absolute right-0 top-[calc(100%+0.75rem)] w-72 overflow-hidden rounded-3xl border border-white/10 bg-[#11131a] p-3 shadow-2xl">
                                <div class="rounded-2xl bg-white/5 p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Tu cuenta</p>
                                    <strong class="mt-2 block font-headline text-xl font-extrabold text-white">Hola,
                                        {{ $firstName ?: 'Cuenta' }}</strong>
                                    <p class="mt-2 text-sm text-slate-400">Tu sesión ya está activa. Entra a tu panel o continúa
                                        publicando autos.</p>
                                </div>
                                <div class="mt-3 grid gap-2">
                                    <a href="{{ $accountUrl }}"
                                        class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-bold text-slate-100 transition hover:border-primary hover:bg-white/5 hover:text-primary">Ir
                                        a mi panel</a>
                                    <a href="{{ $sellUrl }}"
                                        class="rounded-2xl border border-secondary bg-secondary px-4 py-3 text-sm font-bold text-white transition hover:bg-secondary-container">Publicar
                                        o gestionar autos</a>
                                    <a href="{{ route('buyer.dashboard') }}"
                                        class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-bold text-slate-100 transition hover:border-primary hover:bg-white/5 hover:text-primary">Ver
                                        mi actividad</a>
                                </div>
                            </div>
                        </details>
                    @else
                        <a href="{{ $accountUrl }}"
                            class="px-5 py-2 text-sm font-bold text-slate-200 transition hover:text-primary">Ingresar</a>
                    @endif
                    <a href="{{ $sellUrl }}"
                        class="rounded bg-secondary px-6 py-2.5 font-headline text-sm font-bold text-white shadow-md transition hover:bg-secondary-container">Vender
                        mi auto</a>
                </div>
                <a href="{{ $accountUrl }}"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full text-primary md:hidden">
                    <span class="material-symbols-outlined text-[24px]">person</span>
                </a>
            </div>
            <div class="border-t border-white/10 bg-black px-4 py-4 shadow-xl md:hidden" data-mobile-menu hidden>
                <div class="flex flex-col gap-4">
                    <a href="{{ route('catalog.index') }}"
                        class="font-headline text-base font-bold tracking-tight text-slate-100">Comprar</a>
                    <a href="{{ route('home') }}#destacados"
                        class="font-headline text-base font-bold tracking-tight text-slate-100">Destacados</a>
                    <a href="{{ route('valuation.index') }}"
                        class="font-headline text-base font-bold tracking-tight text-slate-100">Estimación de mercado</a>
                    <a href="{{ route('home') }}#noticias"
                        class="font-headline text-base font-bold tracking-tight text-slate-100">Noticias</a>
                    <div class="mt-3 flex flex-col gap-3 border-t border-white/10 pt-4">
                        @if (auth()->check())
                            <div class="rounded-2xl bg-[#12131a] px-4 py-4">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Cuenta activa</p>
                                <strong class="mt-2 block font-headline text-lg font-extrabold text-white">Hola,
                                    {{ $firstName ?: 'Cuenta' }}</strong>
                                <p class="mt-2 text-sm text-slate-400">Tu sesión ya está iniciada.</p>
                            </div>
                            <a href="{{ $accountUrl }}"
                                class="rounded border border-white/10 px-4 py-3 text-center font-headline font-bold text-slate-100">Ir
                                a mi panel</a>
                        @else
                            <a href="{{ $accountUrl }}"
                                class="rounded border border-white/10 px-4 py-3 text-center font-headline font-bold text-slate-100">Ingresar</a>
                        @endif
                        <a href="{{ $sellUrl }}"
                            class="rounded bg-secondary px-4 py-3 text-center font-headline font-bold text-white">Vender mi
                            auto</a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="pt-20">
            <section class="relative overflow-hidden bg-black py-16 sm:py-20">
                <div class="absolute inset-0 hidden lg:block" aria-hidden="true">
                    <div class="absolute inset-y-0 right-0 w-[56%] overflow-hidden">
                        <img class="h-full w-full scale-[1.06] object-cover object-right opacity-30 blur-[1px]"
                            src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1600&q=80"
                            alt="Vehículo listo para vender" />
                    </div>
                    <div
                        class="absolute inset-0 bg-[linear-gradient(90deg,#000_0%,#000_36%,rgba(0,0,0,0.94)_52%,rgba(0,0,0,0.74)_68%,rgba(0,0,0,0.88)_100%)]">
                    </div>
                    <div
                        class="absolute inset-y-0 right-[34%] w-40 bg-[radial-gradient(circle_at_center,rgba(245,158,11,0.12),rgba(0,0,0,0)_72%)] blur-2xl">
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-40 bg-[linear-gradient(180deg,rgba(0,0,0,0)_0%,#000_100%)]">
                    </div>
                </div>

                <div class="relative z-10 mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-6 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                        <aside
                            class="grid gap-5 rounded-[28px] border border-white/10 bg-[#0d1117] p-6 shadow-[0_24px_80px_rgba(0,0,0,0.35)] backdrop-blur sm:p-8 lg:sticky lg:top-28">
                            <div>
                                <span
                                    class="inline-flex rounded-full bg-primary-fixed px-4 py-2 text-[11px] font-bold uppercase tracking-[0.24em] text-primary">Vende
                                    tu auto</span>
                                <h1
                                    class="mt-5 font-headline text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                                    Publica rápido y termina sin perderte.</h1>
                                <p class="mt-4 text-base leading-8 text-slate-300">Registra tu auto primero. Tu cuenta se
                                    crea al final. Así reducimos fricción y te ayudamos a completar la publicación más
                                    rápido.</p>
                            </div>

                            <div class="grid gap-4 rounded-2xl border border-white/10 bg-white/5 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <span
                                            class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Progreso</span>
                                        <h2 class="mt-2 font-headline text-2xl font-extrabold tracking-tight text-white"
                                            data-progress-heading>Vas por el 20%</h2>
                                        <p class="mt-2 text-sm leading-7 text-slate-400" data-progress-copy>Completa los
                                            datos base del auto para arrancar bien tu publicación.</p>
                                    </div>
                                    <div class="seller-progress-ring">
                                        <span data-progress-percent>20%</span>
                                    </div>
                                </div>
                                <div class="seller-progress-bar">
                                    <span data-progress-bar style="width: 20%"></span>
                                </div>
                                <div class="rounded-2xl bg-[#12131a] p-4 shadow-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <strong class="text-sm font-bold text-white">Estado del borrador</strong>
                                        <span
                                            class="inline-flex rounded-full bg-white/5 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400"
                                            data-autosave-status>Sin guardar</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-7 text-slate-400">Tu avance se guarda en este navegador
                                        para que no pierdas información mientras avanzas.</p>
                                </div>
                                <div class="rounded-2xl bg-[#12131a] p-4 shadow-sm">
                                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Paso
                                        actual</span>
                                    <div class="mt-3 grid gap-2">
                                        <strong class="text-base font-bold text-white" data-current-step-title>Identidad del
                                            vehículo</strong>
                                        <span class="text-sm text-slate-400" data-current-step-meta>Paso 1 de 5</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 rounded-2xl bg-primary px-5 py-5 text-white">
                                <span class="text-xs font-bold uppercase tracking-[0.2em] text-white/65">Consejo del
                                    paso</span>
                                <p class="text-sm leading-7 text-white/90" data-sidebar-tip>Usa el nombre comercial correcto
                                    de la versión y una descripción breve pero confiable.</p>
                            </div>

                            <div class="grid gap-3 rounded-2xl border border-white/10 bg-[#12131a] px-5 py-5">
                                <span class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Carga
                                    inteligente</span>
                                <p class="text-sm leading-7 text-slate-400" data-sidebar-loader>Avanza paso a paso. Aquí
                                    verás el estado de carga, compresión o validación.</p>
                            </div>
                        </aside>

                        <section
                            class="rounded-[28px] border border-white/10 bg-[#0d1117] p-5 shadow-[0_24px_80px_rgba(0,0,0,0.35)] sm:p-7 lg:min-h-[calc(100vh-8.5rem)]">
                            @if (session('status'))
                                <div
                                    class="mb-5 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-4 text-sm font-medium text-emerald-200">
                                    {{ session('status') }}</div>
                            @endif

                            @if ($errors->any())
                                <div
                                    class="mb-5 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-4 text-sm text-rose-200">
                                    <strong class="block font-bold">Revisa estos campos antes de continuar.</strong>
                                    <ul class="mt-2 list-disc space-y-1 pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div
                                class="mb-5 flex flex-col gap-4 border-b border-white/10 pb-5 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Publicación
                                        guiada</span>
                                    <h2 class="mt-2 font-headline text-3xl font-extrabold tracking-tight text-white">
                                        Completa la publicación de tu auto</h2>
                                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-400">Diseñado para que cualquier
                                        persona publique rápido, con buena información y sin registrarse al inicio.</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('seller.onboarding.store') }}" class="seller-onboarding"
                                enctype="multipart/form-data" data-wizard data-autosave-key="seller-onboarding-draft"
                                data-usd-to-crc="{{ (float) data_get($exchangeQuote, 'usd_to_crc', 0) }}">
                                @csrf
                                <input type="hidden" name="country_code" value="CR" />
                                <input type="hidden" name="latitude" value="{{ old('latitude') }}" data-map-lat />
                                <input type="hidden" name="longitude" value="{{ old('longitude') }}" data-map-lng />
                                <input type="hidden" name="city" value="{{ old('city', data_get($prefill, 'city')) }}"
                                    data-map-city />
                                <input type="hidden" name="state" value="{{ old('state', 'Costa Rica') }}" data-map-state />
                                <input type="hidden" name="location_label" value="{{ old('location_label') }}"
                                    data-map-label />
                                <script type="application/json" id="cr-location-tree">@json($locationTree)</script>

                                <div class="grid gap-5">
                                    <section class="wizard-step rounded-[24px] border border-white/10 bg-white/5 p-5 sm:p-6"
                                        data-step-panel data-step-title="Identidad del vehículo">
                                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <span
                                                    class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Paso
                                                    1</span>
                                                <h3
                                                    class="mt-2 font-headline text-2xl font-extrabold tracking-tight text-white">
                                                    Identidad del auto</h3>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Define correctamente el
                                                    vehículo para mejorar su relevancia en búsquedas y comparaciones.</p>
                                            </div>
                                            <span
                                                class="inline-flex rounded-full bg-white/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-300">Base
                                                del anuncio</span>
                                        </div>

                                        <div class="mb-5 grid gap-4 md:grid-cols-2">
                                            <div class="rounded-2xl bg-[#12131a] p-4 shadow-sm"><span
                                                    class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Importante</span>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">La selección de modelo se
                                                    filtra automáticamente según la marca para mantener consistencia.</p>
                                            </div>
                                            <div class="rounded-2xl bg-[#12131a] p-4 shadow-sm"><span
                                                    class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Consejo</span>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Una buena descripción
                                                    acelera los contactos porque responde dudas antes del chat o la llamada.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2">
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Marca</span><select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    name="vehicle_make_id" required data-make-select>
                                                    <option value="">Selecciona</option>@foreach ($makes as $make)<option
                                                        value="{{ $make->id }}" @selected(old('vehicle_make_id', data_get($prefill, 'vehicle_make_id')) == $make->id)>{{ $make->name }}
                                                    </option>@endforeach
                                                </select></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Modelo</span><select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    name="vehicle_model_id" required data-model-select>
                                                    <option value="">Selecciona</option>@foreach ($makes as $make)
                                                        @foreach ($make->models as $model)<option value="{{ $model->id }}"
                                                            @selected(old('vehicle_model_id', data_get($prefill, 'vehicle_model_id')) == $model->id) data-make="{{ $make->id }}">
                                                    {{ $model->name }}</option>@endforeach @endforeach
                                                </select></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Año</span><select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    name="year" required>@foreach ($years as $year)<option
                                                        value="{{ $year }}" @selected((int) old('year', data_get($prefill, 'year', date('Y'))) === (int) $year)>{{ $year }}</option>
                                                    @endforeach</select></label>
                                            <label class="grid gap-2 md:col-span-2"><span
                                                    class="text-sm font-semibold text-slate-100">Descripción del
                                                    anuncio</span><textarea
                                                    class="min-h-[160px] rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm leading-7 text-slate-200 shadow-sm placeholder:text-slate-500"
                                                    rows="5" name="description" required
                                                    placeholder="Describe estado general, mantenimientos, extras, historial y por qué vale la pena tu auto.">{{ old('description') }}</textarea></label>
                                        </div>

                                        <div
                                            class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <p class="text-sm text-slate-400">Primero dejamos bien armada la identidad del
                                                auto.</p>
                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container"
                                                data-wizard-next>Siguiente: especificaciones</button>
                                        </div>
                                    </section>
                                    <section class="wizard-step rounded-[24px] border border-white/10 bg-white/5 p-5 sm:p-6"
                                        data-step-panel data-step-title="Especificaciones y precio" hidden>
                                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <span
                                                    class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Paso
                                                    2</span>
                                                <h3
                                                    class="mt-2 font-headline text-2xl font-extrabold tracking-tight text-white">
                                                    Especificaciones y precio</h3>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Normalizamos la información
                                                    clave para que el anuncio sea claro, comparable y confiable.</p>
                                            </div>
                                            <span
                                                class="inline-flex rounded-full bg-white/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-300">Estandarizado</span>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Condición</span><select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    name="condition"
                                                    required>@foreach ($vehicleConfig['conditions'] as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('condition', data_get($prefill, 'condition', 'used')) === $value)>{{ $label }}
                                                    </option>@endforeach</select></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Carrocería</span><select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    name="body_type" required>
                                                    <option value="">Selecciona</option>
                                                    @foreach ($vehicleConfig['body_types'] as $bodyType)<option
                                                        value="{{ $bodyType }}" @selected(old('body_type', data_get($prefill, 'body_type')) === $bodyType)>{{ $bodyType }}
                                                    </option>@endforeach
                                                </select></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Combustible</span><select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    name="fuel_type" required>
                                                    <option value="">Selecciona</option>
                                                    @foreach ($vehicleConfig['fuel_types'] as $fuelType)<option
                                                        value="{{ $fuelType }}" @selected(old('fuel_type', data_get($prefill, 'fuel_type')) === $fuelType)>{{ $fuelType }}
                                                    </option>@endforeach
                                                </select></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Transmisión</span><select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    name="transmission" required>
                                                    <option value="">Selecciona</option>
                                                    @foreach ($vehicleConfig['transmissions'] as $transmission)<option
                                                        value="{{ $transmission }}" @selected(old('transmission', data_get($prefill, 'transmission')) === $transmission)>
                                                    {{ $transmission }}</option>@endforeach
                                                </select></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Tracción</span><select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    name="drivetrain">
                                                    <option value="">No especificar</option>
                                                    @foreach ($vehicleConfig['drivetrains'] as $drivetrain)<option
                                                        value="{{ $drivetrain }}" @selected(old('drivetrain', data_get($prefill, 'drivetrain')) === $drivetrain)>{{ $drivetrain }}
                                                    </option>@endforeach
                                                </select></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Kilometraje</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="number" name="mileage" min="0" step="1"
                                                    value="{{ old('mileage', data_get($prefill, 'mileage')) }}"
                                                    placeholder="Ej. 82000" /></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Motor</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="text" name="engine" value="{{ old('engine') }}"
                                                    placeholder="Ej. 2.0 Turbo, V6" /></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Color exterior</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="text" name="exterior_color"
                                                    value="{{ old('exterior_color') }}" /></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Color interior</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="text" name="interior_color"
                                                    value="{{ old('interior_color') }}" /></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Puertas</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="number" name="doors" value="{{ old('doors') }}" min="1"
                                                    max="8" /></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">VIN</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="text" name="vin" value="{{ old('vin') }}"
                                                    placeholder="Opcional por ahora" /></label>
                                            <label class="grid gap-2"><span
                                                    class="text-sm font-semibold text-slate-100">Placa</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="text" name="plate_number" value="{{ old('plate_number') }}"
                                                    placeholder="Opcional" /></label>
                                            <input type="hidden" name="currency" value="CRC" />
                                            <label class="grid gap-2 md:col-span-2 xl:col-span-3"><span
                                                    class="text-sm font-semibold text-slate-100">Precio en colones
                                                    (CRC)</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="number" step="1" name="price"
                                                    value="{{ old('price', data_get($prefill, 'price')) }}" required
                                                    data-price-input placeholder="18500000" /><small
                                                    class="text-sm text-slate-400" data-price-preview>El precio oficial se
                                                    mostrará en colones. Debajo verás una referencia pequeña en
                                                    dólares.</small></label>
                                        </div>

                                        <div
                                            class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#12131a] px-5 py-3 text-sm font-bold text-white transition hover:border-primary hover:bg-white/5 hover:text-white"
                                                data-wizard-prev>Volver</button>
                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container"
                                                data-wizard-next>Siguiente: extras y fotos</button>
                                        </div>
                                    </section>

                                    <section class="wizard-step rounded-[24px] border border-white/10 bg-white/5 p-5 sm:p-6"
                                        data-step-panel data-step-title="Extras y fotos" hidden>
                                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <span
                                                    class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Paso
                                                    3</span>
                                                <h3
                                                    class="mt-2 font-headline text-2xl font-extrabold tracking-tight text-white">
                                                    Extras y fotografías</h3>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Marca solo lo que realmente
                                                    tiene el auto y sube fotos claras en el orden que mejor convierte.</p>
                                            </div>
                                            <span
                                                class="inline-flex rounded-full bg-white/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-300">Más
                                                confianza</span>
                                        </div>

                                        <div class="mb-5 grid gap-4 lg:grid-cols-3">
                                            <div class="rounded-2xl bg-[#12131a] p-4 shadow-sm"><strong
                                                    class="block text-sm font-bold text-white">Obligatorias</strong>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Frontal, trasera, laterales
                                                    e interiores principales.</p>
                                            </div>
                                            <div class="rounded-2xl bg-[#12131a] p-4 shadow-sm"><strong
                                                    class="block text-sm font-bold text-white">Recomendadas</strong>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Tablero, motor, baúl,
                                                    asientos traseros y aros.</p>
                                            </div>

                                        </div>
                                        <div class="rounded-[24px] border border-white/10 bg-[#12131a] p-5 shadow-sm">
                                            <div class="mb-4"><span
                                                    class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Checklist</span>
                                                <h4
                                                    class="mt-2 font-headline text-xl font-extrabold tracking-tight text-white">
                                                    Extras configurables</h4>
                                            </div>
                                            <div class="feature-option-list">
                                                @forelse ($featureOptions as $option)
                                                    <label class="feature-option-check">
                                                        <input type="checkbox" name="features[]" value="{{ $option->slug }}"
                                                            @checked(in_array($option->slug, old('features', []), true)) />
                                                        <span>{{ $option->name }}</span>
                                                    </label>
                                                @empty
                                                    <p class="text-sm text-slate-400">Todavía no hay características
                                                        configuradas. Puedes agregarlas desde el admin.</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="mt-5 rounded-[24px] border border-white/10 bg-[#12131a] p-5 shadow-sm"
                                            data-photo-sequence>
                                            <div class="mb-4 flex items-center justify-between gap-3">
                                                <div>
                                                    <span
                                                        class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Carga
                                                        guiada</span>
                                                    <h4
                                                        class="mt-2 font-headline text-xl font-extrabold tracking-tight text-white">
                                                        Sube una foto por vez</h4>
                                                </div>
                                                <span
                                                    class="inline-flex rounded-full bg-white/5 px-3 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                                    data-photo-sequence-meta>Foto 1 de 6</span>
                                            </div>

                                            <div class="mb-4 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-300"
                                                data-photo-draft-status hidden>
                                                No hay fotos restauradas todavía.
                                            </div>

                                            <div class="grid gap-4">
                                                <div class="photo-sequence-panel" data-photo-panel
                                                    data-photo-required="true" data-photo-title="Fotografía frontal">
                                                    <label
                                                        class="seller-photo-card grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                                        <span class="text-sm font-semibold text-slate-100">Fotografía
                                                            frontal</span>
                                                        <input
                                                            class="rounded-xl border border-white/10 bg-[#12131a] px-3 py-3 text-sm text-white"
                                                            type="file" name="photo_front" accept="image/*" required
                                                            data-compress-image data-photo-input />
                                                        <small class="text-sm text-slate-400" data-file-hint>Obligatoria.
                                                            Mejor en horizontal y con el auto completo.</small>
                                                    </label>
                                                </div>

                                                <div class="photo-sequence-panel" data-photo-panel
                                                    data-photo-required="true" data-photo-title="Fotografía trasera" hidden>
                                                    <label
                                                        class="seller-photo-card grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                                        <span class="text-sm font-semibold text-slate-100">Fotografía
                                                            trasera</span>
                                                        <input
                                                            class="rounded-xl border border-white/10 bg-[#12131a] px-3 py-3 text-sm text-white"
                                                            type="file" name="photo_rear" accept="image/*" required
                                                            data-compress-image data-photo-input />
                                                        <small class="text-sm text-slate-400" data-file-hint>Obligatoria.
                                                            Intenta mostrar compuerta, bumper y luces.</small>
                                                    </label>
                                                </div>

                                                <div class="photo-sequence-panel" data-photo-panel
                                                    data-photo-required="true" data-photo-title="Lateral izquierda" hidden>
                                                    <label
                                                        class="seller-photo-card grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                                        <span class="text-sm font-semibold text-slate-100">Lateral
                                                            izquierda</span>
                                                        <input
                                                            class="rounded-xl border border-white/10 bg-[#12131a] px-3 py-3 text-sm text-white"
                                                            type="file" name="photo_left" accept="image/*" required
                                                            data-compress-image data-photo-input />
                                                        <small class="text-sm text-slate-400" data-file-hint>Obligatoria.
                                                            Toma el costado completo del vehículo.</small>
                                                    </label>
                                                </div>

                                                <div class="photo-sequence-panel" data-photo-panel
                                                    data-photo-required="true" data-photo-title="Lateral derecha" hidden>
                                                    <label
                                                        class="seller-photo-card grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                                        <span class="text-sm font-semibold text-slate-100">Lateral
                                                            derecha</span>
                                                        <input
                                                            class="rounded-xl border border-white/10 bg-[#12131a] px-3 py-3 text-sm text-white"
                                                            type="file" name="photo_right" accept="image/*" required
                                                            data-compress-image data-photo-input />
                                                        <small class="text-sm text-slate-400" data-file-hint>Obligatoria.
                                                            Evita reflejos fuertes o cortes.</small>
                                                    </label>
                                                </div>

                                                <div class="photo-sequence-panel" data-photo-panel
                                                    data-photo-required="true" data-photo-title="Interior del conductor"
                                                    hidden>
                                                    <label
                                                        class="seller-photo-card grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                                        <span class="text-sm font-semibold text-slate-100">Interior del
                                                            conductor</span>
                                                        <input
                                                            class="rounded-xl border border-white/10 bg-[#12131a] px-3 py-3 text-sm text-white"
                                                            type="file" name="photo_driver_interior" accept="image/*"
                                                            required data-compress-image data-photo-input />
                                                        <small class="text-sm text-slate-400" data-file-hint>Obligatoria.
                                                            Muestra asiento, puerta y tablero lateral.</small>
                                                    </label>
                                                </div>

                                                <div class="photo-sequence-panel" data-photo-panel
                                                    data-photo-required="true" data-photo-title="Interior" hidden>
                                                    <label
                                                        class="seller-photo-card grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                                        <span class="text-sm font-semibold text-slate-100">Interior</span>
                                                        <input
                                                            class="rounded-xl border border-white/10 bg-[#12131a] px-3 py-3 text-sm text-white"
                                                            type="file" name="photo_passenger_interior" accept="image/*"
                                                            required data-compress-image data-photo-input />
                                                        <small class="text-sm text-slate-400" data-file-hint>Obligatoria.
                                                            Cierra las 6 fotos base del anuncio.</small>
                                                    </label>
                                                </div>

                                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4"
                                                    data-photo-optional-entry hidden>
                                                    <span
                                                        class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Fotos
                                                        opcionales</span>
                                                    <h5 class="mt-2 text-lg font-bold text-white">Puedes subir más
                                                        fotografías</h5>
                                                    <p class="mt-2 text-sm leading-7 text-slate-400">Si quieres mostrar un
                                                        par de detalles extra, puedes seguir. Si no, pasa directo al
                                                        siguiente paso.</p>
                                                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                                                        <button type="button"
                                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-container"
                                                            data-photo-optional-open>Sí, subir más fotos</button>
                                                        <button type="button"
                                                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#12131a] px-5 py-3 text-sm font-bold text-white transition hover:border-primary hover:bg-white/5 hover:text-white"
                                                            data-photo-optional-skip>No, continuar</button>
                                                    </div>
                                                </div>

                                                <div class="photo-sequence-panel" data-photo-panel
                                                    data-photo-optional="true" data-photo-title="Foto adicional 1" hidden>
                                                    <label
                                                        class="seller-photo-card grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                                        <span class="text-sm font-semibold text-slate-100">Foto adicional
                                                            1</span>
                                                        <input
                                                            class="rounded-xl border border-white/10 bg-[#12131a] px-3 py-3 text-sm text-white"
                                                            type="file" name="photo_extra_1" accept="image/*"
                                                            data-compress-image data-photo-input />
                                                        <small class="text-sm text-slate-400" data-file-hint>Opcional.
                                                            Extras, golpes leves o closeups importantes.</small>
                                                    </label>
                                                </div>

                                                <div class="photo-sequence-panel" data-photo-panel
                                                    data-photo-optional="true" data-photo-title="Foto adicional 2" hidden>
                                                    <label
                                                        class="seller-photo-card grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                                        <span class="text-sm font-semibold text-slate-100">Foto adicional
                                                            2</span>
                                                        <input
                                                            class="rounded-xl border border-white/10 bg-[#12131a] px-3 py-3 text-sm text-white"
                                                            type="file" name="photo_extra_2" accept="image/*"
                                                            data-compress-image data-photo-input />
                                                        <small class="text-sm text-slate-400" data-file-hint>Opcional.
                                                            Puedes dejarla vacía si ya terminaste.</small>
                                                    </label>
                                                </div>
                                            </div>

                                            <div
                                                class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <button type="button"
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#12131a] px-5 py-3 text-sm font-bold text-white transition hover:border-primary hover:bg-white/5 hover:text-white"
                                                    data-photo-prev>Foto anterior</button>
                                                <button type="button"
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-container"
                                                    data-photo-next>Foto siguiente</button>
                                            </div>
                                        </div>

                                        <div
                                            class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#12131a] px-5 py-3 text-sm font-bold text-white transition hover:border-primary hover:bg-white/5 hover:text-white"
                                                data-wizard-prev>Volver</button>
                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container"
                                                data-wizard-next>Siguiente: ubicación</button>
                                        </div>
                                    </section>

                                    <section class="wizard-step rounded-[24px] border border-white/10 bg-white/5 p-5 sm:p-6"
                                        data-step-panel data-step-title="Ubicación en Costa Rica" hidden>
                                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <span
                                                    class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Paso
                                                    4</span>
                                                <h3
                                                    class="mt-2 font-headline text-2xl font-extrabold tracking-tight text-white">
                                                    Ubica tu auto en Costa Rica</h3>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Este marketplace solo
                                                    publica autos ubicados en Costa Rica. Usa el mapa para dejar el punto
                                                    bien claro.</p>
                                            </div>
                                            <span
                                                class="inline-flex rounded-full bg-white/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-300">Google
                                                Maps</span>
                                        </div>
                                        <div class="mb-5 grid gap-4 md:grid-cols-2">
                                            <div class="rounded-2xl bg-[#12131a] p-4 shadow-sm"><strong
                                                    class="block text-sm font-bold text-white">País permitido</strong>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Solo aceptamos ubicaciones
                                                    dentro de Costa Rica.</p>
                                            </div>
                                            <div class="rounded-2xl bg-[#12131a] p-4 shadow-sm"><strong
                                                    class="block text-sm font-bold text-white">Sugerencia</strong>
                                                <p class="mt-2 text-sm leading-7 text-slate-400">Busca por distrito, cantón
                                                    o un punto conocido para que el comprador entienda mejor la zona.</p>
                                            </div>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2">
                                            <label class="grid gap-2 md:col-span-2"><span
                                                    class="text-sm font-semibold text-slate-100">Dirección o punto de
                                                    referencia</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="text" name="map_search" value="{{ old('location_label') }}"
                                                    placeholder="Ej. Barrio Los Yoses, cerca del parque, San Pedro"
                                                    data-map-search required /></label>
                                            <label class="grid gap-2">
                                                <span class="text-sm font-semibold text-slate-100">Provincia</span>
                                                <select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm disabled:cursor-not-allowed disabled:opacity-50"
                                                    name="province" data-location-province>
                                                    <option value="">Selecciona una provincia</option>
                                                </select>
                                            </label>
                                            <label class="grid gap-2">
                                                <span class="text-sm font-semibold text-slate-100">Cantón</span>
                                                <select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm disabled:cursor-not-allowed disabled:opacity-50"
                                                    name="canton" data-location-canton disabled>
                                                    <option value="">Selecciona primero una provincia</option>
                                                </select>
                                            </label>
                                            <label class="grid gap-2 md:col-span-2">
                                                <span class="text-sm font-semibold text-slate-100">Distrito</span>
                                                <select
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm disabled:cursor-not-allowed disabled:opacity-50"
                                                    name="district" data-location-district disabled>
                                                    <option value="">Selecciona primero un cantón</option>
                                                </select>
                                                <small class="text-sm text-slate-400">La ruta correcta en Costa Rica es:
                                                    provincia, luego cantón y después distrito.</small>
                                            </label>
                                            <label class="grid gap-2 md:col-span-2"><span
                                                    class="text-sm font-semibold text-slate-100">WhatsApp de
                                                    contacto</span><input
                                                    class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                    type="text" name="contact_phone"
                                                    value="{{ old('contact_phone', auth()->user()?->whatsapp_phone ?: auth()->user()?->phone) }}"
                                                    placeholder="Ej. 8888-8888" /><small class="text-sm text-slate-400">Este
                                                    número se usará en el botón de WhatsApp del anuncio para contactar al
                                                    vendedor.</small></label>
                                            @if ($googleMapsKey)
                                                <div class="grid gap-2 md:col-span-2"><span
                                                        class="text-sm font-semibold text-slate-100">Mapa</span>
                                                    <div class="map-canvas rounded-[24px] border border-white/10 bg-[#12131a]"
                                                        data-map-canvas></div>
                                                </div>
                                            @else
                                                <div class="grid gap-2 md:col-span-2">
                                                    <span class="text-sm font-semibold text-slate-100">Mapa</span>
                                                    <div
                                                        class="map-canvas rounded-[24px] border border-white/10 bg-[#12131a] flex items-center justify-center p-8 text-center">
                                                        <div class="map-canvas__fallback text-slate-400">Configura
                                                            `Maps_API_KEY` para usar el mapa interactivo. Mientras tanto puedes
                                                            completar manualmente provincia, cantón, distrito y la referencia
                                                            del auto.</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        @if ($googleMapsKey)
                                            <script async defer
                                                src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places"></script>
                                        @endif

                                        <div
                                            class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#12131a] px-5 py-3 text-sm font-bold text-white transition hover:border-primary hover:bg-white/5 hover:text-white"
                                                data-wizard-prev>Volver</button>
                                            @if ($currentUser)
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container"
                                                    data-submit-onboarding>Publicar con mi cuenta</button>
                                            @else
                                                <button type="button"
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container"
                                                    data-wizard-next>Siguiente: tu cuenta</button>
                                            @endif
                                        </div>
                                    </section>

                                    @unless ($currentUser)
                                        <section class="wizard-step rounded-[24px] border border-white/10 bg-white/5 p-5 sm:p-6"
                                            data-step-panel data-step-title="Cuenta de vendedor" hidden>
                                            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <span
                                                        class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Paso
                                                        5</span>
                                                    <h3
                                                        class="mt-2 font-headline text-2xl font-extrabold tracking-tight text-white">
                                                        Crea tu cuenta de vendedor al final</h3>
                                                    <p class="mt-2 text-sm leading-7 text-slate-400">Aquí terminamos. Se crea tu
                                                        cuenta y tu auto queda listo para administrarse desde tu panel de
                                                        vendedor.</p>
                                                </div>
                                                <span
                                                    class="inline-flex rounded-full bg-white/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-300">Cierre
                                                    rápido</span>
                                            </div>

                                            <div class="grid gap-4 md:grid-cols-2">
                                                <label class="grid gap-2"><span
                                                        class="text-sm font-semibold text-slate-100">Nombre
                                                        completo</span><input
                                                        class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                        type="text" name="seller_name" value="{{ old('seller_name') }}"
                                                        required /></label>
                                                <label class="grid gap-2"><span
                                                        class="text-sm font-semibold text-slate-100">Correo
                                                        electrónico</span><input
                                                        class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                        type="email" name="contact_email" value="{{ old('contact_email') }}"
                                                        placeholder="Opcional si usas teléfono" /></label>
                                                <label class="grid gap-2"><span
                                                        class="text-sm font-semibold text-slate-100">Contraseña</span><input
                                                        class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                        type="password" name="password" required /></label>
                                                <label class="grid gap-2 md:col-span-2"><span
                                                        class="text-sm font-semibold text-slate-100">Confirmar
                                                        contraseña</span><input
                                                        class="rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm font-semibold text-white shadow-sm"
                                                        type="password" name="password_confirmation" required /></label>
                                                <label
                                                    class="inline-flex items-center gap-3 rounded-2xl border border-white/10 bg-[#12131a] px-4 py-4 text-sm text-slate-100 md:col-span-2"><input
                                                        type="checkbox" name="accept_terms" value="1" required
                                                        class="h-4 w-4 accent-[var(--color-secondary)] bg-[#12131a]" />
                                                    <span>Acepto términos y confirmo que el auto está en Costa
                                                        Rica.</span></label>
                                            </div>

                                            <div class="mt-6 rounded-2xl bg-primary px-5 py-5 text-white">
                                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                                    <div>
                                                        <span
                                                            class="text-xs font-bold uppercase tracking-[0.2em] text-white/65">Resultado</span>
                                                        <p class="mt-2 text-sm leading-7 text-white/90">Se crea tu cuenta de
                                                            vendedor, se publica tu auto con plan básico y luego podrás publicar
                                                            uno o varios autos desde tu panel.</p>
                                                    </div>
                                                    <button type="submit"
                                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container"
                                                        data-submit-onboarding>Registrar auto y crear cuenta</button>
                                                </div>
                                            </div>

                                            <div class="mt-6 flex justify-start">
                                                <button type="button"
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#12131a] px-5 py-3 text-sm font-bold text-white transition hover:border-primary hover:bg-white/5 hover:text-white"
                                                    data-wizard-prev>Volver</button>
                                            </div>
                                        </section>
                                    @endunless
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection
