@extends('layouts.portal')

@section('title', 'Nuevo anuncio vendedor | Movikaa')
@section('portal-eyebrow', 'Seller create')
@section('portal-title', 'Crear nuevo anuncio')
@section('portal-copy', 'Formulario dedicado para publicar autos con una UX m?s ordenada, adaptable a tu plan y lista para crecer.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('seller.dashboard') }}">Resumen</a>
    <a href="{{ route('seller.listings') }}">Publicaciones</a>
    <a href="{{ route('seller.onboarding.create') }}" class="is-active">Nuevo anuncio</a>
    <a href="{{ route('seller.media') }}">Media</a>
    <a href="{{ (config('app.enable_payments') ? route('seller.billing') : route('seller.listings')) }}">Pagos</a>
    <a href="{{ route('buyer.dashboard') }}">Actividad comprador</a>
</nav>
<div class="portal-note-card"><span class="portal-kicker">Capacidad</span><strong>{{ $currentPlan->name }}</strong><p>{{ $currentPlan->photo_limit ?? 'Ilimitadas' }} fotos y {{ $capabilities['remaining_active_listings'] ?? 'Ilimitadas' }} publicaciones restantes.</p></div>
@endsection

@section('content')
    @include('portal.seller._form')
@endsection

