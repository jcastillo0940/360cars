@extends('layouts.portal')

@section('title', 'Vendedor | Movikaa')
@section('portal-eyebrow', 'Vendedor')
@section('portal-title', 'Panel comercial del vendedor')
@section('portal-copy', 'Publica, renueva, organiza tu inventario y da seguimiento a tus contactos desde una experiencia simple y enfocada en vender mejor.')

@section('header-actions')
    <a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Nuevo anuncio</a>
    <a href="{{ route('seller.billing') }}" class="button button--ghost">Plan y pagos</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('seller.dashboard') }}" class="{{ request()->routeIs('seller.dashboard') ? 'is-active' : '' }}">Resumen</a>
    <a href="{{ route('seller.listings') }}" class="{{ request()->routeIs('seller.listings') ? 'is-active' : '' }}">Publicaciones</a>
    <a href="{{ route('seller.onboarding.create') }}" class="{{ request()->routeIs('seller.onboarding.create', 'seller.create') ? 'is-active' : '' }}">Nuevo anuncio</a>
    <a href="{{ route('seller.media') }}" class="{{ request()->routeIs('seller.media') ? 'is-active' : '' }}">Imágenes</a>
    <a href="{{ route('seller.messages') }}" class="{{ request()->routeIs('seller.messages') ? 'is-active' : '' }}">Mensajes</a>
    <a href="{{ route('seller.billing') }}" class="{{ request()->routeIs('seller.billing') ? 'is-active' : '' }}">Planes y pagos</a>
    <a href="{{ route('buyer.dashboard') }}">Mi seguimiento como comprador</a>
</nav>
<div class="portal-note-card">
    <span class="portal-kicker">Plan actual</span>
    <strong>{{ $currentPlan->name }}</strong>
    <p>{{ $capabilities['remaining_active_listings'] ?? 'Ilimitadas' }} publicaciones disponibles.</p>
</div>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Publicaciones activas</span><strong>{{ $activeListingsCount }}</strong><p>{{ $publishedCount }} publicadas y {{ $pausedCount }} en pausa.</p></article>
    <article class="metric-card"><span>Contactos acumulados</span><strong>{{ $leadCount }}</strong><p>Consultas generadas por tus anuncios.</p></article>
    <article class="metric-card"><span>Renovaciones gratis</span><strong>{{ $freeRenewableCount }}</strong><p>Anuncios básicos listos para volver a posicionarse.</p></article>
    <article class="metric-card"><span>Vistas acumuladas</span><strong>{{ number_format($viewCount) }}</strong><p>Interés total registrado en tu inventario.</p></article>
    <article class="metric-card"><span>Conversaciones abiertas</span><strong>{{ number_format($conversationCount) }}</strong><p>Seguimientos activos con compradores.</p></article>
</section>

<section class="dashboard-grid dashboard-grid--three-up">
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Publicar</p><h2>Crea un nuevo anuncio</h2><p>Registra otra unidad y deja el contenido listo para salir al inventario.</p><a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Crear anuncio</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Inventario</p><h2>Gestiona tus publicaciones</h2><p>Filtra estados, revisa resultados y entra a editar cada publicación desde una vista más ordenada.</p><a href="{{ route('seller.listings') }}" class="button button--solid">Ver publicaciones</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Planes</p><h2>Visibilidad y pagos</h2><p>Consulta tu plan actual y revisa opciones para dar más alcance a tus anuncios.</p><a href="{{ route('seller.billing') }}" class="button button--solid">Ver planes</a></article>
</section>
@endsection
