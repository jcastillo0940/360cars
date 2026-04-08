@extends('layouts.portal')

@section('title', 'Buyer Portal | 360Cars')
@section('portal-eyebrow', 'Buyer portal')
@section('portal-title', 'Tu centro de seguimiento para oportunidades reales.')
@section('portal-copy', 'Favoritos, comparador, búsquedas guardadas y conversaciones ya sobre la misma base publica del marketplace.')

@section('header-actions')
    <a href="{{ route('catalog.index') }}" class="button button--solid">Explorar inventario</a>
@endsection

@section('sidebar')
    <nav class="portal-nav">
        <a href="#overview" class="is-active">Resumen</a>
        <a href="#favorites">Favoritos</a>
        <a href="#compare">Comparador</a>
        <a href="#saved-searches">B?squedas</a>
        <a href="#messages">Mensajes</a>
    </nav>
@endsection

@section('content')
<section class="dashboard-grid" id="overview">
    <article class="metric-card reveal"><span>Guardados</span><strong>{{ $savedCount }}</strong><p>Autos que ya marcaste para volver a revisar.</p></article>
    <article class="metric-card reveal reveal--delay"><span>Nuevas coincidencias</span><strong>{{ $matchCount }}</strong><p>Inventario publicado listo para explorar.</p></article>
    <article class="metric-card reveal reveal--delay-2"><span>Comparaciones</span><strong>{{ $compareCount }}</strong><p>Vehículos listos para analizar lado a lado.</p></article>
</section>

<section class="dashboard-panel reveal" id="favorites">
    <div class="panel-heading"><div><p class="eyebrow">Favoritos</p><h2>Autos que te interesan</h2></div></div>
    <div class="kanban-grid">
        @forelse ($favorites as $favorite)
            <article class="kanban-card">
                <span class="muted-label">{{ $favorite->vehicle?->make?->name }} · {{ $favorite->vehicle?->model?->name }}</span>
                <strong>{{ $favorite->vehicle?->title }}</strong>
                <p>${{ number_format((float) $favorite->vehicle?->price, 0) }} · {{ $favorite->vehicle?->year }} · {{ $favorite->vehicle?->city }}</p>
                @if ($favorite->vehicle)
                    <a href="{{ route('catalog.show', $favorite->vehicle->slug) }}" class="button button--ghost">Ver detalle</a>
                @endif
            </article>
        @empty
            <article class="quick-card"><strong>Aún no tienes favoritos.</strong><p>Guarda autos desde el catálogo público y aparecerán aquí.</p></article>
        @endforelse
    </div>
</section>

<section class="dashboard-panel reveal" id="compare">
    <div class="panel-heading"><div><p class="eyebrow">Comparador</p><h2>Vehículos en comparacion</h2></div></div>
    <div class="kanban-grid">
        @forelse ($comparisonVehicles as $vehicle)
            <article class="kanban-card">
                <span class="muted-label">{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }}</span>
                <strong>{{ $vehicle->title }}</strong>
                <p>${{ number_format((float) $vehicle->price, 0) }} · {{ $vehicle->fuel_type }} · {{ $vehicle->transmission }}</p>
                <a href="{{ route('catalog.show', $vehicle->slug) }}" class="button button--ghost">Ver ficha</a>
            </article>
        @empty
            <article class="quick-card"><strong>Comparador vacio.</strong><p>Agrega hasta 4 vehículos desde el inventario para verlos aquí.</p></article>
        @endforelse
    </div>
</section>

<section class="dashboard-panel reveal" id="saved-searches">
    <div class="panel-heading"><div><p class="eyebrow">B?squedas guardadas</p><h2>Alertas configuradas</h2></div></div>
    <div class="kanban-grid">
        @forelse ($savedSearches as $search)
            <article class="kanban-card">
                <span class="muted-label">{{ strtoupper($search->notification_frequency) }}</span>
                <strong>{{ $search->name }}</strong>
                <p>{{ json_encode($search->filters, JSON_UNESCAPED_UNICODE) }}</p>
                <form method="POST" action="{{ route('buyer.saved-searches.destroy', $search) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button button--ghost">Eliminar</button>
                </form>
            </article>
        @empty
            <article class="quick-card"><strong>Sin alertas activas.</strong><p>Desde el catálogo podr?s guardar filtros para volver rápido.</p></article>
        @endforelse
    </div>
</section>

<section class="dashboard-panel reveal" id="messages">
    <div class="panel-heading"><div><p class="eyebrow">Conversaciones</p><h2>Mensajes con vendedores</h2></div></div>
    <div class="kanban-grid">
        @forelse ($conversations as $conversation)
            <article class="kanban-card">
                <span class="muted-label">{{ optional($conversation->last_message_at)->diffForHumans() ?? 'Sin actividad' }}</span>
                <strong>{{ $conversation->subject ?: 'Consulta comercial' }}</strong>
                <p>{{ $conversation->vehicle?->title ?: 'Conversaci?n general' }}</p>
            </article>
        @empty
            <article class="quick-card"><strong>Sin mensajes todavía.</strong><p>Cuando escribas desde el detalle del vehículo, la conversación aparecera aquí.</p></article>
        @endforelse
    </div>
</section>
@endsection
