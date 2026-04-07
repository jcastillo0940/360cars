@extends('layouts.auth')

@section('title', 'Ingresar | Movikaa')

@section('content')
<section class="min-h-[calc(100vh-5rem)] bg-black px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-10rem)] max-w-2xl items-center justify-center">
        <div class="w-full max-w-xl rounded-[2rem] border border-white/10 bg-[#0b0b0f] p-6 shadow-[0_30px_120px_rgba(0,0,0,0.55)] sm:p-8 lg:p-10">
            @if (session('status'))
                <div class="mb-5 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-200">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    <ul class="grid gap-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-secondary">Movikaa</p>
                <h1 class="mt-3 font-headline text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Inicia sesión</h1>
                <p class="mt-3 text-sm leading-7 text-slate-300 sm:text-base">Ingresa con tu correo y tu contraseña para continuar.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 grid gap-5">
                @csrf
                @if (!empty($redirectTo))
                    <input type="hidden" name="redirect" value="{{ $redirectTo }}" />
                @endif

                <label class="grid gap-2">
                    <span class="text-sm font-semibold text-slate-200">Correo electrónico</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition placeholder:text-slate-500 focus:border-primary focus:ring-4 focus:ring-primary/15"
                        placeholder="tu@correo.com"
                    />
                </label>

                <label class="grid gap-2">
                    <span class="text-sm font-semibold text-slate-200">Contraseña</span>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition placeholder:text-slate-500 focus:border-primary focus:ring-4 focus:ring-primary/15"
                        placeholder="Ingresa tu contraseña"
                    />
                </label>

                <button type="submit" class="mt-2 inline-flex min-h-14 items-center justify-center rounded-2xl bg-secondary px-6 font-headline text-base font-extrabold text-white shadow-lg transition hover:bg-secondary-container">
                    Ingresar
                </button>
            </form>
        </div>
    </div>
</section>
@endsection