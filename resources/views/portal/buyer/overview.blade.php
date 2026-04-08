@extends('layouts.portal')

@section('title', 'Comprador | Movikaa')
@section('portal-eyebrow', 'Comprador')
@section('portal-title', 'Tu centro de seguimiento')
@section('portal-copy', 'Guarda autos, compáralos, vuelve a tus búsquedas y mantén tus conversaciones organizadas desde una sola cuenta.')

@section('header-actions')
    <a href="{{ route('catalog.index') }}" class="button button--solid">Explorar inventario</a>
    <a href="{{ route('seller.dashboard') }}" class="button button--ghost">Publicar mis autos</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('buyer.dashboard') }}" class="{{ request()->routeIs('buyer.dashboard') ? 'is-active' : '' }}">Resumen</a>
    <a href="{{ route('buyer.favorites.index') }}" class="{{ request()->routeIs('buyer.favorites.index') ? 'is-active' : '' }}">Favoritos</a>
    <a href="{{ route('buyer.comparisons.index') }}" class="{{ request()->routeIs('buyer.comparisons.index') ? 'is-active' : '' }}">Comparador</a>
    <a href="{{ route('buyer.searches.index') }}" class="{{ request()->routeIs('buyer.searches.index') ? 'is-active' : '' }}">Búsquedas</a>
    <a href="{{ route('buyer.messages.index') }}" class="{{ request()->routeIs('buyer.messages.index') ? 'is-active' : '' }}">Mensajes</a>
    <a href="{{ route('seller.dashboard') }}">Mi área de vendedor</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Favoritos</span><strong>{{ $savedCount }}</strong><p>Autos que guardaste para volver a revisarlos.</p></article>
    <article class="metric-card"><span>Nuevas coincidencias</span><strong>{{ $matchCount }}</strong><p>Inventario disponible para seguir explorando.</p></article>
    <article class="metric-card"><span>Comparaciones</span><strong>{{ $compareCount }}</strong><p>Vehículos listos para revisar lado a lado.</p></article>
    <article class="metric-card"><span>Conversaciones</span><strong>{{ $conversationCount }}</strong><p>Seguimientos abiertos con vendedores.</p></article>
</section>

<section class="dashboard-grid dashboard-grid--three-up">
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Favoritos</p><h2>Autos que te interesan</h2><p>Revisa tus guardados y vuelve rápido al detalle cuando quieras.</p><a href="{{ route('buyer.favorites.index') }}" class="button button--solid">Abrir favoritos</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Comparador</p><h2>Tu selección</h2><p>Analiza modelos guardados y toma decisiones con más claridad.</p><a href="{{ route('buyer.comparisons.index') }}" class="button button--solid">Ver comparador</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Mensajes</p><h2>Seguimiento comercial</h2><p>Conserva tus conversaciones con vendedores en un solo lugar.</p><a href="{{ route('buyer.messages.index') }}" class="button button--solid">Abrir mensajes</a></article>
</section>
@endsection
