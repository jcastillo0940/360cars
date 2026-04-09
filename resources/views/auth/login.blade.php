@extends('layouts.auth')

@section('title', 'Ingresar | Movikaa')

@section('content')
<div class="w-full max-w-[480px]">
    <div class="mb-10 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="/img/logo.png" alt="Movikaa" class="mx-auto h-12 w-auto object-contain">
        </a>
    </div>

    <div class="rounded-[2.5rem] border border-white/10 bg-[#0b0b0f]/80 p-8 shadow-[0_30px_120px_rgba(0,0,0,0.55)] backdrop-blur-xl sm:p-10">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="grid gap-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="text-center">
            <h1 class="font-headline text-3xl font-extrabold tracking-tight text-white sm:text-4xl">¡Hola de nuevo!</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-400">Ingresa tus credenciales para acceder a tu panel.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="mt-10 grid gap-5">
            @csrf
            @if (!empty($redirectTo))
                <input type="hidden" name="redirect" value="{{ $redirectTo }}" />
            @endif

            <label class="grid gap-2 text-left">
                <span class="ml-1 text-xs font-bold uppercase tracking-widest text-slate-500">Correo electrónico</span>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="min-h-14 rounded-2xl border border-white/5 bg-white/5 px-4 text-white outline-none transition placeholder:text-slate-600 focus:border-primary/50 focus:bg-white/10 focus:ring-4 focus:ring-primary/10" placeholder="tu@correo.com" />
            </label>

            <label class="grid gap-2 text-left">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Contraseña</span>
                    <a href="{{ route('password.request') }}" class="text-xs font-bold uppercase tracking-widest text-primary hover:text-white transition">¿La olvidaste?</a>
                </div>
                <input type="password" name="password" required autocomplete="current-password" class="min-h-14 rounded-2xl border border-white/5 bg-white/5 px-4 text-white outline-none transition placeholder:text-slate-600 focus:border-primary/50 focus:bg-white/10 focus:ring-4 focus:ring-primary/10" placeholder="••••••••" />
            </label>

            <button type="submit" class="mt-4 flex min-h-14 items-center justify-center rounded-2xl bg-secondary px-6 font-headline text-lg font-extrabold text-white shadow-xl transition-all hover:scale-[1.02] hover:bg-secondary-container active:scale-[0.98]">
                Entrar ahora
            </button>
        </form>

        <div class="mt-10 border-t border-white/5 pt-8 text-center">
            <p class="text-sm text-slate-500">
                ¿No tienes cuenta? 
                <a href="{{ route('register') }}" class="font-bold text-white hover:text-primary transition">Regístrate gratis</a>
            </p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-slate-600 hover:text-slate-300 transition">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Cerrar y volver
            </a>
        </div>
    </div>
</div>
@endsection
