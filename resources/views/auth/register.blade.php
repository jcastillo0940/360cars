@extends('layouts.auth')

@section('title', 'Crear cuenta | Movikaa')

@section('content')
<section class="relative isolate overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(0,91,183,0.16),_transparent_24%),radial-gradient(circle_at_82%_18%,_rgba(254,107,0,0.14),_transparent_18%)]"></div>
    <div class="mx-auto grid min-h-[calc(100vh-5rem)] w-full max-w-screen-2xl items-center gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[0.98fr_1.02fr] lg:px-8 lg:py-12">
        <div class="reveal rounded-[2rem] border border-outline-variant/20 bg-white/72 p-6 shadow-2xl backdrop-blur-xl sm:p-8 lg:p-10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-[#0d1117]/88' : '' }}">
            <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.24em] text-secondary">Onboarding</p>
            <h1 class="max-w-[10ch] font-headline text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">Crea tu cuenta y entra listo a operar.</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                Buyer, seller o dealer comparten una misma base. Solo eliges tu rol y entras al flujo correcto para publicar, comprar o administrar.
            </p>

            <div class="mt-8 grid gap-4">
                <article class="rounded-2xl border border-outline-variant/20 bg-white/80 p-4 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5' : '' }}">
                    <strong class="font-headline text-lg font-extrabold">Marketplace unificado</strong>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Tu cuenta te permite guardar favoritos, evaluar autos y publicar inventario sin duplicar registros.</p>
                </article>
                <article class="rounded-2xl border border-outline-variant/20 bg-white/80 p-4 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5' : '' }}">
                    <strong class="font-headline text-lg font-extrabold">Escala contigo</strong>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Puedes comenzar como seller individual y luego pasar a dealer sin salir del ecosistema.</p>
                </article>
                <article class="rounded-2xl border border-outline-variant/20 bg-white/80 p-4 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5' : '' }}">
                    <strong class="font-headline text-lg font-extrabold">Diseno consistente</strong>
                    <p class="mt-2 text-sm leading-6 text-slate-600">La experiencia mantiene el mismo lenguaje visual del home para que el ingreso se sienta natural y confiable.</p>
                </article>
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
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-primary">Crear cuenta</p>
                <h2 class="mt-3 font-headline text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Empieza en menos de un minuto</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-base">Completa tus datos base y te enviaremos al portal correcto segun el rol que elijas.</p>
            </div>

            <form method="POST" action="{{ route('register.store') }}" class="mt-8 grid gap-5">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 sm:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Nombre</span>
                        <input type="text" name="name" value="{{ old('name') }}" required class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="Tu nombre completo" />
                    </label>
                    <label class="grid gap-2 sm:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Correo electronico</span>
                        <input type="email" name="email" value="{{ old('email') }}" required class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="tu@correo.com" />
                    </label>
                    <label class="grid gap-2">
                        <span class="text-sm font-semibold text-slate-700">Rol</span>
                        <select name="account_type" required class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white' : '' }}">
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="dealer">Dealer</option>
                        </select>
                    </label>
                    <label class="grid gap-2">
                        <span class="text-sm font-semibold text-slate-700">Telefono</span>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="Ej. 8888-8888" />
                    </label>
                    <label class="grid gap-2">
                        <span class="text-sm font-semibold text-slate-700">Contrasena</span>
                        <input type="password" name="password" required class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="Crea una contrasena" />
                    </label>
                    <label class="grid gap-2">
                        <span class="text-sm font-semibold text-slate-700">Confirmar contrasena</span>
                        <input type="password" name="password_confirmation" required class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="Repite la contrasena" />
                    </label>
                    <label class="grid gap-2">
                        <span class="text-sm font-semibold text-slate-700">Agencia</span>
                        <input type="text" name="agency_name" value="{{ old('agency_name') }}" class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="Solo si aplica" />
                    </label>
                    <label class="grid gap-2">
                        <span class="text-sm font-semibold text-slate-700">Empresa</span>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" class="min-h-14 rounded-2xl border border-outline-variant/40 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white placeholder:text-slate-400' : '' }}" placeholder="Solo si aplica" />
                    </label>
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button type="submit" class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-secondary px-6 font-headline text-base font-extrabold text-white shadow-lg transition hover:bg-secondary-container">Crear cuenta</button>
                    <a href="{{ route('login') }}" class="inline-flex min-h-14 items-center justify-center rounded-2xl border border-outline-variant/30 bg-white px-6 font-semibold text-slate-700 transition hover:border-primary hover:text-primary {{ ($publicTheme ?? 'light') === 'dark' ? 'bg-white/5 text-white' : '' }}">Ya tengo cuenta</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
