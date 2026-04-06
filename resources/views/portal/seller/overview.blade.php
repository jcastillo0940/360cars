@extends('layouts.portal')

@section('title', 'Seller | Movikaa')
@section('portal-eyebrow', 'Seller overview')
@section('portal-title', 'Panel comercial del vendedor')
@section('portal-copy', 'Tu centro para publicar, renovar, gestionar media, seguir leads y potenciar anuncios sin una sola pantalla eterna.')

@section('header-actions')
    <a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Nueva publicacion</a>
    <a href="{{ route('seller.billing') }}" class="button button--ghost">Plan y pagos</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('seller.dashboard') }}" class="{{ request()->routeIs('seller.dashboard') ? 'is-active' : '' }}">Overview</a>
    <a href="{{ route('seller.listings') }}" class="{{ request()->routeIs('seller.listings') ? 'is-active' : '' }}">Publicaciones</a>
    <a href="{{ route('seller.onboarding.create') }}" class="{{ request()->routeIs('seller.onboarding.create', 'seller.create') ? 'is-active' : '' }}">Nuevo anuncio</a>
    <a href="{{ route('seller.media') }}" class="{{ request()->routeIs('seller.media') ? 'is-active' : '' }}">Media</a>
    <a href="{{ route('seller.messages') }}" class="{{ request()->routeIs('seller.messages') ? 'is-active' : '' }}">Mensajes</a>
    <a href="{{ route('seller.billing') }}" class="{{ request()->routeIs('seller.billing') ? 'is-active' : '' }}">Billing</a>
    <a href="{{ route('buyer.dashboard') }}">Actividad buyer</a>
</nav>
<div class="portal-note-card">
    <span class="portal-kicker">Plan actual</span>
    <strong>{{ $currentPlan->name }}</strong>
    <p>{{ $capabilities['remaining_active_listings'] ?? 'Ilimitadas' }} publicaciones restantes.</p>
</div>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Publicaciones activas</span><strong>{{ $activeListingsCount }}</strong><p>{{ $publishedCount }} publicadas y {{ $pausedCount }} en pausa.</p></article>
    <article class="metric-card"><span>Leads acumulados</span><strong>{{ $leadCount }}</strong><p>Contactos generados por tus anuncios.</p></article>
    <article class="metric-card"><span>Renovables gratis</span><strong>{{ $freeRenewableCount }}</strong><p>Anuncios basicos listos para reposicionarse.</p></article>
    <article class="metric-card"><span>Vistas acumuladas</span><strong>{{ number_format($viewCount) }}</strong><p>Interes total observado en tu inventario.</p></article>
    <article class="metric-card"><span>Mensajes abiertos</span><strong>{{ number_format($conversationCount) }}</strong><p>Conversaciones activas con compradores.</p></article>
</section>

<section class="dashboard-grid dashboard-grid--three-up">
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Nuevo anuncio</p><h2>Publica rapido</h2><p>Registra otra unidad y luego ajusta el contenido fino desde su vista de edicion.</p><a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Crear anuncio</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Inventario</p><h2>Visibilidad y monetizacion</h2><p>Filtra estados, revisa leads, posiciona anuncios y entra a editar cada publicacion por separado.</p><a href="{{ route('seller.listings') }}" class="button button--solid">Ver publicaciones</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Billing</p><h2>Planes seller</h2><p>La unica transaccion del sistema ocurre aqui: potenciar anuncios y administrar suscripciones.</p><a href="{{ route('seller.billing') }}" class="button button--solid">Ver billing</a></article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Snapshot</p><h2>Inventario reciente</h2></div><a href="{{ route('seller.listings') }}" class="text-link">Abrir modulo</a></div>
        <div class="table-shell">
            <table class="portal-table">
                <thead><tr><th>Vehiculo</th><th>Estado</th><th>Precio</th><th>Leads</th></tr></thead>
                <tbody>
                @forelse ($recentVehicles as $vehicle)
                    <tr>
                        <td><strong>{{ $vehicle->title }}</strong><span>{{ $vehicle->make?->name }} Â· {{ $vehicle->model?->name }}</span></td>
                        <td><span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $vehicle->status }}</span></td>
                        <td>{{ \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote)['primary_formatted'] }}</td>
                        <td>{{ $vehicle->lead_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Aun no tienes publicaciones registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Plan</p><h2>Capacidades actuales</h2></div></div>
        <div class="catalog-stack">
            <article class="catalog-block"><div class="catalog-block__header"><div><strong>{{ $currentPlan->name }}</strong><p>{{ $currentPlan->description }}</p></div><span class="status-badge">Activo</span></div><p class="empty-copy">{{ $currentPlan->photo_limit ?? 'Ilimitadas' }} fotos Â· {{ $currentPlan->max_active_listings ?? 'Ilimitadas' }} publicaciones Â· {{ $currentPlan->allows_video ? 'video' : 'sin video' }} Â· {{ $currentPlan->allows_360 ? '360' : 'sin 360' }}</p></article>
        </div>
    </article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Estados</p><h2>Distribucion del inventario</h2></div></div>
        <div class="bar-chart-stack">
            @foreach ($inventoryStatusChart as $item)
                <div class="bar-chart-row">
                    <div>
                        <strong>{{ $item['label'] }}</strong>
                        <span>{{ $item['value'] }} anuncios</span>
                    </div>
                    <div class="bar-chart-track"><span style="width: {{ $item['width'] }}%"></span></div>
                </div>
            @endforeach
        </div>
    </article>
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Rendimiento</p><h2>Leads vs vistas</h2></div></div>
        <div class="mini-bars">
            @forelse ($listingPerformanceChart as $item)
                <div class="mini-bars__item">
                    <div class="mini-bars__columns">
                        <span class="mini-bars__bar mini-bars__bar--blue" style="height: {{ $item['view_height'] }}%"></span>
                        <span class="mini-bars__bar mini-bars__bar--yellow" style="height: {{ $item['lead_height'] }}%"></span>
                    </div>
                    <strong>{{ $item['label'] }}</strong>
                    <small>{{ $item['views'] }} vistas Â· {{ $item['leads'] }} leads</small>
                </div>
            @empty
                <div class="empty-state"><strong>Aun no hay suficiente actividad.</strong><p>Cuando tus anuncios reciban vistas y leads, el panel lo mostrara aqui.</p></div>
            @endforelse
        </div>
    </article>
</section>

@if ($freeRenewableCount > 0)
<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Reposicion gratis</p><h2>Anuncios basicos para renovar</h2></div></div>
    <div class="list-stack">
        @foreach ($expiredListings->filter(fn ($vehicle) => $vehicle->publication_tier === 'basic')->take(4) as $vehicle)
            <div class="list-row">
                <div><strong>{{ $vehicle->title }}</strong><p>Vencio {{ optional($vehicle->expires_at)->diffForHumans() }}. Puedes devolverlo al feed por otros 30 dias.</p></div>
                <form method="POST" action="{{ route('seller.vehicles.refresh-basic', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="button button--solid">Renovar y reposicionar</button></form>
            </div>
        @endforeach
    </div>
</section>
@endif
@endsection



