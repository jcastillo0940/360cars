@extends('layouts.portal')

@section('title', 'Vendedor | Movikaa')
@section('portal-eyebrow', 'Vendedor')
@section('portal-title', 'Panel comercial del vendedor')
@section('portal-copy', 'Publica, renueva, organiza tu inventario y da seguimiento a tus contactos desde una experiencia simple y enfocada en vender mejor.')

@section('header-actions')
    <a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Nuevo anuncio</a>
    <a href="{{ route('seller.billing') }}" class="button button--ghost">Plan y pagos</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Anuncios Activos</span>
        <strong>{{ $activeListingsCount }}</strong>
        <p>{{ $publishedCount }} públicos, {{ $pausedCount }} pausados.</p>
    </article>
    <article class="metric-card">
        <span>Interés Total</span>
        <strong>{{ $leadCount }}</strong>
        <p>Contactos y consultas recibidas.</p>
    </article>
    <article class="metric-card">
        <span>Renovaciones</span>
        <strong>{{ $freeRenewableCount }}</strong>
        <p>Básicos listos para reposicionarse.</p>
    </article>
    <article class="metric-card">
        <span>Impacto Visual</span>
        <strong>{{ number_format($viewCount) }}</strong>
        <p>Vistas totales en el marketplace.</p>
    </article>
</section>

<section class="dashboard-grid dashboard-grid--three-up">
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Publicar</p><h2>Crea un nuevo anuncio</h2><p>Registra otra unidad y deja el contenido listo para salir al inventario.</p><a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Crear anuncio</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Inventario</p><h2>Gestiona tus publicaciones</h2><p>Filtra estados, revisa resultados y entra a editar cada publicación desde una vista más ordenada.</p><a href="{{ route('seller.listings') }}" class="button button--solid">Ver publicaciones</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Planes</p><h2>Visibilidad y pagos</h2><p>Consulta tu plan actual y revisa opciones para dar más alcance a tus anuncios.</p><a href="{{ route('seller.billing') }}" class="button button--solid">Ver planes</a></article>
</section>
@endsection
