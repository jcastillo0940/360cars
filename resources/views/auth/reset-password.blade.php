@extends('layouts.auth')

@section('title', 'Restablecer contraseña | Movikaa')

@section('content')
<section class="min-h-[calc(100vh-5rem)] bg-black px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-10rem)] max-w-2xl items-center justify-center">
        <div class="w-full max-w-xl rounded-[2rem] border border-white/10 bg-[#0b0b0f] p-6 shadow-[0_30px_120px_rgba(0,0,0,0.55)] sm:p-8 lg:p-10">
            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    <ul class="grid gap-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-secondary">Movikaa</p>
            <h1 class="mt-3 font-headline text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Crea una nueva contraseña</h1>
            <p class="mt-3 text-sm leading-7 text-slate-300 sm:text-base">Elige una contraseña segura para volver a entrar a tu cuenta.</p>

            <form method="POST" action="{{ route('password.update') }}" class="mt-8 grid gap-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />
                <label class="grid gap-2">
                    <span class="text-sm font-semibold text-slate-200">Correo electrónico</span>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="tu@correo.com" />
                </label>
                <div class="grid gap-2 text-left">
                    <span class="text-sm font-semibold text-slate-200">Nueva contraseña</span>
                    <input type="password" name="password" id="password" required class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="Crea una contraseña nueva" />
                    
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
                    <span class="text-sm font-semibold text-slate-200">Confirmar contraseña</span>
                    <input type="password" name="password_confirmation" id="password_confirmation" required class="min-h-14 rounded-2xl border border-white/10 bg-[#12131a] px-4 text-white shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" placeholder="Repite la contraseña" />
                    
                    <div id="password-match" class="mt-2 flex items-center gap-2 px-1 text-[11px] font-bold uppercase tracking-wider opacity-0 transition-opacity">
                        <span class="status-dot h-1.5 w-1.5 rounded-full"></span>
                        <span class="match-text"></span>
                    </div>
                </div>
                
                <button type="submit" class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-secondary px-6 font-headline text-base font-extrabold text-white shadow-lg transition hover:bg-secondary-container">Guardar nueva contraseña</button>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const passwordInput = document.getElementById('password');
                    const confirmInput = document.getElementById('password_confirmation');
                    const checklist = document.getElementById('password-checklist');
                    const matchFeedback = document.getElementById('password-match');

                    const requirements = {
                        min: (val) => val.length >= 8,
                        mixed: (val) => /[a-z]/.test(val) && /[A-Z]/.test(val),
                        number: (val) => /[0-9]/.test(val)
                    };

                    function updateRequirements() {
                        const val = passwordInput.value;
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
                    }

                    function updateMatch() {
                        const pass = passwordInput.value;
                        const confirm = confirmInput.value;
                        const dot = matchFeedback.querySelector('.status-dot');
                        const text = matchFeedback.querySelector('.match-text');

                        if (confirm.length === 0) {
                            matchFeedback.classList.add('opacity-0');
                            return;
                        }

                        matchFeedback.classList.remove('opacity-0');
                        if (pass === confirm) {
                            dot.className = 'status-dot h-1.5 w-1.5 rounded-full bg-emerald-500';
                            text.textContent = 'Las contraseñas coinciden';
                            text.className = 'match-text text-emerald-400';
                        } else {
                            dot.className = 'status-dot h-1.5 w-1.5 rounded-full bg-rose-500';
                            text.textContent = 'Las contraseñas no coinciden';
                            text.className = 'match-text text-rose-400';
                        }
                    }

                    passwordInput.addEventListener('input', updateRequirements);
                    confirmInput.addEventListener('input', updateMatch);
                });
            </script>
        </div>
    </div>
</section>
@endsection

