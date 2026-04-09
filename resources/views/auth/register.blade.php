@extends('layouts.auth')

@section('title', 'Crear cuenta | Movikaa')

@section('content')
<section class="min-h-[calc(100vh-5rem)] bg-black px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-10rem)] max-w-3xl items-center justify-center">
        <div class="w-full rounded-[2rem] border border-white/10 bg-[#0b0b0f] p-6 shadow-[0_30px_120px_rgba(0,0,0,0.55)] sm:p-8 lg:p-10">
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
                <h1 class="mt-3 font-headline text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Crea tu cuenta</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">Registra tus datos para publicar, comprar y gestionar tu actividad desde una sola cuenta.</p>
            </div>

            <form method="POST" action="{{ route('register.store') }}" class="mt-8 grid gap-5">
                @csrf
                <input type="hidden" name="account_type" value="seller" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 sm:col-span-2"><span class="text-sm font-semibold text-slate-200">Nombre</span><input type="text" name="name" value="{{ old('name') }}" required class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="Tu nombre completo" /></label>
                    <label class="grid gap-2 sm:col-span-2"><span class="text-sm font-semibold text-slate-200">Correo electrónico</span><input type="email" name="email" value="{{ old('email') }}" required class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="tu@correo.com" /></label>
                    <label class="grid gap-2 sm:col-span-2">
                        <span class="text-sm font-semibold text-slate-200">Teléfono</span>
                        <div class="grid min-h-14 grid-cols-[170px_minmax(0,1fr)] overflow-hidden rounded-2xl border border-white/10 bg-[#12131a] shadow-sm focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/15">
                            <select name="country_code" class="border-0 border-r border-white/10 bg-[#12131a] px-4 text-sm font-semibold text-white outline-none focus:ring-0">
                                @foreach (($countryOptions ?? []) as $country)
                                    <option value="{{ $country['code'] }}" @selected(old('country_code', 'CR') === $country['code'])>{{ $country['flag'] }} {{ $country['dial'] }} {{ $country['label'] }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="border-0 bg-[#12131a] px-4 text-white outline-none focus:ring-0" placeholder="Ej. 8888-8888" />
                        </div>
                    </label>
                    <label class="grid gap-2"><span class="text-sm font-semibold text-slate-200">Contraseña</span><input type="password" name="password" required class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="Crea una contraseña" /></label>
                    <label class="grid gap-2"><span class="text-sm font-semibold text-slate-200">Confirmar contraseña</span><input type="password" name="password_confirmation" required class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="Repite la contraseña" /></label>
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button type="submit" class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-secondary px-6 font-headline text-base font-extrabold text-white shadow-lg transition hover:bg-secondary-container">Crear cuenta</button>
                    <a href="{{ route('login') }}" class="inline-flex min-h-14 items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-6 font-semibold text-white transition hover:border-primary hover:bg-white/10">Ya tengo cuenta</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
