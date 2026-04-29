@extends('layouts.marketing')

@section('title', ($pageTitle ?? 'Legal').' | Movikaa')
@section('meta_description', $pageDescription ?? 'Informacion legal y condiciones de uso del marketplace Movikaa.')

@section('head')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
@vite(['resources/css/home.css'])
@endsection

@section('content')
<div class="min-h-screen bg-background text-on-background font-body">
    <main class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            Volver al inicio
        </a>

        <section class="mt-8 overflow-hidden rounded-[2rem] border border-outline-variant/20 bg-white shadow-xl">
            <div class="bg-[radial-gradient(circle_at_top_left,_rgba(255,184,58,0.2),_transparent_42%),linear-gradient(135deg,_#0f172a_0%,_#111827_55%,_#1e293b_100%)] px-8 py-10 text-white sm:px-10 sm:py-12">
                <span class="inline-flex rounded-full bg-white/10 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.24em] text-secondary">Legal</span>
                <h1 class="mt-5 font-headline text-4xl font-extrabold tracking-tight sm:text-5xl">{{ $pageTitle ?? 'Documento legal' }}</h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-white/80">{{ $pageDescription ?? '' }}</p>
                <div class="mt-6 inline-flex rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/75">
                    Ultima actualizacion: {{ $lastUpdated ?? 'No disponible' }}
                </div>
            </div>

            <div class="px-8 py-8 sm:px-10 sm:py-10">
                <div class="rounded-2xl bg-surface-container-low p-6 text-sm leading-7 text-slate-700">
                    Este documento regula el uso de Movikaa dentro de Costa Rica. Si tienes consultas sobre su alcance, puedes escribir a
                    <a href="mailto:{{ $contactEmail ?? 'soporte@movikaa.co' }}" class="font-semibold text-primary hover:underline">{{ $contactEmail ?? 'soporte@movikaa.co' }}</a>.
                </div>

                <div class="mt-10 space-y-10">
                    @foreach (($pageSections ?? []) as $section)
                        <section>
                            <h2 class="font-headline text-2xl font-extrabold tracking-tight text-slate-950">{{ $section['heading'] }}</h2>
                            <div class="mt-4 space-y-4 text-base leading-8 text-slate-600">
                                @foreach (($section['body'] ?? []) as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
