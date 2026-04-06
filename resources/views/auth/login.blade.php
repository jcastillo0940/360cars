@extends('layouts.auth')

@section('title', 'Ingresar | Movikaa')

@section('content')
<section class="relative isolate overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(0,91,183,0.16),_transparent_24%),radial-gradient(circle_at_82%_18%,_rgba(254,107,0,0.14),_transparent_18%)]"></div>
    <div class="mx-auto grid min-h-[calc(100vh-5rem)] w-full max-w-screen-2xl items-center gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-12">
        <div class="reveal rounded-[2rem] border border-outline-variant/20 bg-white/72 p-6 shadow-2xl backdrop-blur-xl sm:p-8 lg:p-10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-[#0d1117]/88' : '' }}">
            <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.24em] text-secondary">Acceso seguro</p>
            <h1 class="max-w-[10ch] font-headline text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">Entra y sigue moviendo tu negocio.</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                Ingresa con tu cuenta para administrar autos, revisar leads, activar planes o seguir comprando con una experiencia consistente y simple.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-outline-variant/20 bg-white/80 p-4 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5' : '' }}">
                    <span class="material-symbols-outlined text-primary">storefront</span>
                    <h2 class="mt-3 font-headline text-lg font-extrabold">Seller</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Publica varios autos, mejora visibilidad y administra tu inventario desde un solo lugar.</p>
                </article>
                <article class="rounded-2xl border border-outline-variant/20 bg-white/80 p-4 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5' : '' }}">
                    <span class="material-symbols-outlined text-primary">favorite</span>
                    <h2 class="mt-3 font-headline text-lg font-extrabold">Buyer</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Guarda favoritos, compara autos y retoma tus evaluaciones sin perder contexto.</p>
                </article>
                <article class="rounded-2xl border border-outline-variant/20 bg-white/80 p-4 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5' : '' }}">
                    <span class="material-symbols-outlined text-primary">admin_panel_settings</span>
                    <h2 class="mt-3 font-headline text-lg font-extrabold">Admin</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Controla monetizacion, tema publico, tasas y la operacion del marketplace.</p>
                </article>
            </div>

            <div class="mt-8 rounded-[1.75rem] border border-outline-variant/20 bg-gradient-to-r from-primary to-primary-container p-5 text-white shadow-xl sm:p-6">
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-white/70">Accesos demo</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <article class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                        <strong class="block font-headline text-base font-extrabold">Seller</strong>
                        <p class="mt-1 text-sm text-white/80">seller@360cars.local</p>
                        <p class="text-sm text-white/80">password</p>
                    </article>
                    <article class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                        <strong class="block font-headline text-base font-extrabold">Buyer</strong>
                        <p class="mt-1 text-sm text-white/80">buyer@360cars.local</p>
                        <p class="text-sm text-white/80">password</p>
                    </article>
                    <article class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                        <strong class="block font-headline text-base font-extrabold">Admin</strong>
                        <p class="mt-1 text-sm text-white/80">admin@360cars.local</p>
                        <p class="text-sm text-white/80">password</p>
                    </article>
                </div>
            </div>
        </div>

        <div class="reveal reveal--delay rounded-[2rem] border border-outline-variant/20 bg-white/86 p-6 shadow-2xl backdrop-blur-xl sm:p-8 lg:p-10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-[#0d1117]/92' : '' }}">
            @if (session('status'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 {{ ($publicTheme ?? 'light') === 'dark' ? 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200' : '' }}">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 {{ ($publicTheme ?? 'light') === 'dark' ? 'border-rose-400/20 bg-rose-500/10 text-rose-200' : '' }}">
                    <ul class="grid gap-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-primary">Bienvenido</p>
                <h2 class="mt-3 font-headline text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Ingresa a tu cuenta</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-base">Usa tu correo para entrar a tu panel. Si aun no tienes cuenta, puedes crearla en menos de un minuto.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 grid gap-5">
                @csrf
                @if (!empty($redirectTo))
                    <input type="hidden" name="redirect" value="{{ $redirectTo }}" />
                @endif
                <label class="grid gap-2">
                    <span class="text-sm font-semibold text-slate-700">Correo electronico</span>
                    <input type="email" name="email" value="{{ old('email', 'seller@360cars.local') }}" required class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="tu@correo.com" />
                </label>
                <label class="grid gap-2">
                    <span class="text-sm font-semibold text-slate-700">Contrasena</span>
                    <input type="password" name="password" value="password" required class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="Ingresa tu contrasena" />
                </label>
                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button type="submit" class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-secondary px-6 font-headline text-base font-extrabold text-white shadow-lg transition hover:bg-secondary-container">Ingresar</button>
                    <a href="{{ route('register') }}" class="inline-flex min-h-14 items-center justify-center rounded-2xl border border-outline-variant/30 bg-white px-6 font-semibold text-slate-700 transition hover:border-primary hover:text-primary {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white' : '' }}">Crear cuenta</a>
                </div>
            </form>

            <div class="mt-6 rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5' : '' }}">
                <p class="text-sm font-semibold text-slate-800">Acceso unificado</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">Buyer, seller y admin usan la misma cuenta base. El sistema te redirige automaticamente al portal correcto segun tu rol.</p>
            </div>
        </div>
    </div>
</section>
@endsection

