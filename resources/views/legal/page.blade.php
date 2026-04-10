@extends('layouts.marketing')

@section('title', ($pageTitle ?? 'Legal').' | Movikaa')

@section('head')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
@vite(['resources/css/home.css'])
@endsection

@section('content')
<div class="min-h-screen bg-background text-on-background font-body">
    <main class="mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            Volver al inicio
        </a>
        <section class="mt-8 rounded-[2rem] border border-outline-variant/20 bg-white p-8 shadow-xl sm:p-10">
            <span class="inline-flex rounded-full bg-primary-fixed px-4 py-2 text-[11px] font-bold uppercase tracking-[0.24em] text-primary">Legal</span>
            <h1 class="mt-5 font-headline text-4xl font-extrabold tracking-tight text-slate-950">{{ $pageTitle ?? 'Documento legal' }}</h1>
            <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">{{ $pageDescription ?? 'Contenido legal pendiente de version final.' }}</p>
            <div class="mt-8 rounded-2xl bg-surface-container-low p-6 text-sm leading-7 text-slate-600">
                Esta vista queda preparada para el contenido final del cliente. Puedes reemplazar este resumen por el texto legal definitivo sin tocar la estructura del footer ni las rutas publicas.
            </div>
        </section>
    </main>
</div>
@endsection

