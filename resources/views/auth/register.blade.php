@extends('layouts.auth')

@section('title', 'Crear cuenta | Movikaa')

@section('content')
<div class="w-full max-w-[640px]">
    <div class="mb-10 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="/img/logo.png" alt="Movikaa" class="mx-auto h-12 w-auto object-contain">
        </a>
    </div>

    <div class="rounded-[2.5rem] border border-white/10 bg-[#0b0b0f]/80 p-8 shadow-[0_30px_120px_rgba(0,0,0,0.55)] backdrop-blur-xl sm:p-10">
        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="grid gap-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="text-center">
            <h1 class="font-headline text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Crea tu cuenta</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-400">Únete a la comunidad de compra y venta más inteligente.</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="mt-10 grid gap-5">
            @csrf
            <x-honeypot />
            <input type="hidden" name="account_type" value="seller" />

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="grid gap-2 text-left sm:col-span-2">
                    <span class="ml-1 text-xs font-bold uppercase tracking-widest text-slate-500">Nombre completo</span>
                    <input type="text" name="name" value="{{ old('name') }}" required class="min-h-14 rounded-2xl border border-white/5 bg-white/5 px-4 text-white outline-none transition placeholder:text-slate-600 focus:border-primary/50 focus:bg-white/10 focus:ring-4 focus:ring-primary/10" placeholder="Ej. Juan Pérez" />
                </label>

                <label class="grid gap-2 text-left">
                    <span class="ml-1 text-xs font-bold uppercase tracking-widest text-slate-500">Correo</span>
                    <input type="email" name="email" value="{{ old('email') }}" required class="min-h-14 rounded-2xl border border-white/5 bg-white/5 px-4 text-white outline-none transition placeholder:text-slate-600 focus:border-primary/50 focus:bg-white/10 focus:ring-4 focus:ring-primary/10" placeholder="tu@correo.com" />
                </label>

                <label class="grid gap-2 text-left">
                    <span class="ml-1 text-xs font-bold uppercase tracking-widest text-slate-500">Teléfono</span>
                    <div class="flex min-h-14 gap-0 overflow-hidden rounded-2xl border border-white/5 bg-white/5 focus-within:border-primary/50 focus-within:bg-white/10 focus-within:ring-4 focus-within:ring-primary/10">
                        <select name="country_code" class="border-0 border-r border-white/5 bg-transparent px-3 text-sm font-semibold text-white outline-none focus:ring-0">
                            @foreach (($countryOptions ?? []) as $country)
                                <option value="{{ $country['code'] }}" @selected(old('country_code', 'CR') === $country['code'])>{{ $country['flag'] }} {{ $country['dial'] }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="flex-1 border-0 bg-transparent px-4 text-white outline-none focus:ring-0" placeholder="8888-8888" />
                    </div>
                </label>

                <div class="grid gap-2 text-left">
                    <span class="ml-1 text-xs font-bold uppercase tracking-widest text-slate-500">Contraseña</span>
                    <input type="password" name="password" id="password" required autocomplete="new-password" class="min-h-14 rounded-2xl border border-white/5 bg-white/5 px-4 text-white outline-none transition placeholder:text-slate-600 focus:border-primary/50 focus:bg-white/10 focus:ring-4 focus:ring-primary/10" placeholder="••••••••" />
                    
                    <div id="password-checklist" class="mt-2 grid gap-1.5 px-1">
                        <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider transition-colors" data-requirement="min">
                            <span class="status-dot h-1.5 w-1.5 rounded-full bg-slate-700"></span>
                            <span class="text-slate-500">Mínimo 8 caracteres</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider transition-colors" data-requirement="mixed">
                            <span class="status-dot h-1.5 w-1.5 rounded-full bg-slate-700"></span>
                            <span class="text-slate-500">Mayúsculas y minúsculas</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider transition-colors" data-requirement="number">
                            <span class="status-dot h-1.5 w-1.5 rounded-full bg-slate-700"></span>
                            <span class="text-slate-500">Al menos un número</span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-2 text-left">
                    <span class="ml-1 text-xs font-bold uppercase tracking-widest text-slate-500">Confirmar</span>
                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="min-h-14 rounded-2xl border border-white/5 bg-white/5 px-4 text-white outline-none transition placeholder:text-slate-600 focus:border-primary/50 focus:bg-white/10 focus:ring-4 focus:ring-primary/10" placeholder="••••••••" />
                    
                    <div id="password-match" class="mt-2 flex items-center gap-2 px-1 text-[11px] font-bold uppercase tracking-wider opacity-0 transition-opacity">
                        <span class="status-dot h-1.5 w-1.5 rounded-full"></span>
                        <span class="match-text"></span>
                    </div>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="mt-6 flex min-h-14 items-center justify-center rounded-2xl bg-secondary px-6 font-headline text-lg font-extrabold text-white shadow-xl transition-all hover:scale-[1.02] hover:bg-secondary-container active:scale-[0.98]">
                Crear mi cuenta gratis
            </button>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const passwordInput = document.getElementById('password');
                const confirmInput = document.getElementById('password_confirmation');
                const checklist = document.getElementById('password-checklist');
                const matchFeedback = document.getElementById('password-match');
                const submitBtn = document.getElementById('submit-btn');

                const requirements = {
                    min: (val) => val.length >= 8,
                    mixed: (val) => /[a-z]/.test(val) && /[A-Z]/.test(val),
                    number: (val) => /[0-9]/.test(val)
                };

                function updateRequirements() {
                    const val = passwordInput.value;
                    let allMet = true;

                    Object.keys(requirements).forEach(req => {
                        const el = checklist.querySelector(`[data-requirement="${req}"]`);
                        const dot = el.querySelector('.status-dot');
                        const text = el.querySelector('span:last-child');
                        const isMet = requirements[req](val);

                        if (isMet) {
                            dot.classList.remove('bg-slate-700', 'bg-rose-500');
                            dot.classList.add('bg-emerald-500');
                            text.classList.remove('text-slate-500', 'text-rose-400');
                            text.classList.add('text-emerald-400');
                        } else {
                            allMet = false;
                            if (val.length > 0) {
                                dot.classList.remove('bg-slate-700', 'bg-emerald-500');
                                dot.classList.add('bg-rose-500');
                                text.classList.remove('text-slate-500', 'text-emerald-400');
                                text.classList.add('text-rose-400');
                            } else {
                                dot.classList.remove('bg-rose-500', 'bg-emerald-500');
                                dot.classList.add('bg-slate-700');
                                text.classList.remove('text-rose-400', 'text-emerald-400');
                                text.classList.add('text-slate-500');
                            }
                        }
                    });

                    updateMatch();
                    return allMet;
                }

                function updateMatch() {
                    const pass = passwordInput.value;
                    const confirm = confirmInput.value;
                    const dot = matchFeedback.querySelector('.status-dot');
                    const text = matchFeedback.querySelector('.match-text');

                    if (confirm.length === 0) {
                        matchFeedback.classList.add('opacity-0');
                        return false;
                    }

                    matchFeedback.classList.remove('opacity-0');
                    if (pass === confirm) {
                        dot.className = 'status-dot h-1.5 w-1.5 rounded-full bg-emerald-500';
                        text.textContent = 'Las contraseñas coinciden';
                        text.className = 'match-text text-emerald-400';
                        return true;
                    } else {
                        dot.className = 'status-dot h-1.5 w-1.5 rounded-full bg-rose-500';
                        text.textContent = 'Las contraseñas no coinciden';
                        text.className = 'match-text text-rose-400';
                        return false;
                    }
                }

                passwordInput.addEventListener('input', updateRequirements);
                confirmInput.addEventListener('input', updateMatch);
            });
        </script>

        <div class="mt-10 border-t border-white/5 pt-8 text-center">
            <p class="text-sm text-slate-500">
                ¿Ya eres parte de Movikaa? 
                <a href="{{ route('login') }}" class="font-bold text-white hover:text-primary transition">Inicia sesión</a>
            </p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-slate-600 hover:text-slate-300 transition">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Cerrar y volver
            </a>
        </div>
    </div>
</div>
@endsection

