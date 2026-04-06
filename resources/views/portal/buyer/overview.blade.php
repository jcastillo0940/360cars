@extends('layouts.portal')

@section('title', 'Buyer | Movikaa')
@section('portal-eyebrow', 'Buyer overview')
@section('portal-title', 'Tu centro de seguimiento')
@section('portal-copy', 'Favoritos, comparaciones, busquedas y conversaciones en vistas separadas. Si tambien publicas autos, puedes pasar a tu area seller sin cambiar de cuenta.')

@section('header-actions')
    <a href="{{ route('catalog.index') }}" class="button button--solid">Explorar inventario</a>
    <a href="{{ route('seller.dashboard') }}" class="button button--ghost">Publicar mis autos</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('buyer.dashboard') }}" class="{{ request()->routeIs('buyer.dashboard') ? 'is-active' : '' }}">Overview</a>
    <a href="{{ route('buyer.favorites.index') }}" class="{{ request()->routeIs('buyer.favorites.index') ? 'is-active' : '' }}">Favoritos</a>
    <a href="{{ route('buyer.comparisons.index') }}" class="{{ request()->routeIs('buyer.comparisons.index') ? 'is-active' : '' }}">Comparador</a>
    <a href="{{ route('buyer.searches.index') }}" class="{{ request()->routeIs('buyer.searches.index') ? 'is-active' : '' }}">Busquedas</a>
    <a href="{{ route('buyer.messages.index') }}" class="{{ request()->routeIs('buyer.messages.index') ? 'is-active' : '' }}">Mensajes</a>
    <a href="{{ route('seller.dashboard') }}">Actividad seller</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Favoritos</span><strong>{{ $savedCount }}</strong><p>Autos que ya marcaste para volver a revisar.</p></article>
    <article class="metric-card"><span>Nuevas coincidencias</span><strong>{{ $matchCount }}</strong><p>Inventario publicado listo para explorar.</p></article>
    <article class="metric-card"><span>Comparaciones</span><strong>{{ $compareCount }}</strong><p>Vehiculos listos para analizar lado a lado.</p></article>
    <article class="metric-card"><span>Conversaciones</span><strong>{{ $conversationCount }}</strong><p>Seguimiento comercial abierto con vendedores.</p></article>
</section>

<section class="dashboard-grid dashboard-grid--three-up">
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Favoritos</p><h2>Autos que te interesan</h2><p>Revisa tus guardados y vuelve rapido al detalle.</p><a href="{{ route('buyer.favorites.index') }}" class="button button--solid">Abrir favoritos</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Comparador</p><h2>Tu shortlist</h2><p>Analiza modelos guardados y preparate para decidir mejor.</p><a href="{{ route('buyer.comparisons.index') }}" class="button button--solid">Ver comparador</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Mensajes</p><h2>Seguimiento comercial</h2><p>Centraliza tus conversaciones con vendedores en un solo lugar.</p><a href="{{ route('buyer.messages.index') }}" class="button button--solid">Abrir mensajes</a></article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Actividad buyer</p><h2>Resumen de tu cuenta</h2></div></div>
        <div class="bar-chart-stack">
            @foreach ($buyerActivityChart as $item)
                <div class="bar-chart-row">
                    <div>
                        <strong>{{ $item['label'] }}</strong>
                        <span>{{ $item['value'] }} items</span>
                    </div>
                    <div class="bar-chart-track"><span style="width: {{ $item['width'] }}%"></span></div>
                </div>
            @endforeach
        </div>
    </article>
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Guardados recientes</p><h2>Autos que te interesan</h2></div></div>
        @if ($favorites->isNotEmpty())
            <div class="list-stack">
                @foreach ($favorites->take(3) as $favorite)
                    <div class="list-row">
                        <div>
                            <strong>{{ $favorite->vehicle?->title ?? 'Vehiculo guardado' }}</strong>
                            <p>{{ $favorite->vehicle?->make?->name }} · {{ $favorite->vehicle?->model?->name }} · {{ $favorite->vehicle?->city }}</p>
                        </div>
                        @if ($favorite->vehicle)
                            <a href="{{ route('catalog.show', $favorite->vehicle->slug) }}" class="button button--ghost">Ver detalle</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state"><strong>Aun no guardas favoritos.</strong><p>Explora el inventario, marca autos que te interesen y vuelve aqui para seguirlos.</p></div>
        @endif
    </article>
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Actividad seller</p><h2>Tambien puedes publicar</h2></div></div>
        <div class="empty-state"><strong>Tu cuenta es C2C.</strong><p>No compras dentro de la web: aqui solo guardas, comparas y contactas. Si quieres vender uno o varios autos, entra a tu area seller y publica cuando quieras.</p></div>
        <div class="form-actions mt-4"><a href="{{ route('seller.dashboard') }}" class="button button--solid">Abrir area seller</a></div>
    </article>
</section>
@endsection
