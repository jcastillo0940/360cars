@extends('layouts.portal')

@section('title', 'Buyer Portal | 360Cars')
@section('portal-eyebrow', 'Buyer portal')
@section('portal-title', 'Tu centro de seguimiento para oportunidades reales.')
@section('portal-copy', 'Favoritos, comparador, búsquedas guardadas y conversaciones ya sobre la misma base publica del marketplace.')

@section('header-actions')
    <a href="{{ route('catalog.index') }}" class="button button--solid">Explorar inventario</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Favoritos</span>
        <strong>{{ $savedCount }}</strong>
        <p>Autos marcados para seguir.</p>
    </article>
    <article class="metric-card">
        <span>Coincidencias</span>
        <strong>{{ $matchCount }}</strong>
        <p>Novedades en tus búsquedas.</p>
    </article>
    <article class="metric-card">
        <span>Comparativas</span>
        <strong>{{ $compareCount }}</strong>
        <p>Autos listos para analizar.</p>
    </article>
</section>

<div class="panel-grid reveal reveal--delay-1" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.5rem;">
    <section class="dashboard-panel" id="favorites">
        <div class="panel-heading"><div><p class="portal-kicker">Seguimiento</p><h2>Mis Favoritos</h2></div></div>
        <div class="list-stack">
            @forelse ($favorites as $favorite)
                <div class="list-row">
                    <div>
                        <strong>{{ $favorite->vehicle?->title }}</strong>
                        <p>{{ $favorite->vehicle?->year }} · {{ $favorite->vehicle?->price }}</p>
                    </div>
                    @if ($favorite->vehicle)
                        <a href="{{ route('catalog.show', $favorite->vehicle->slug) }}" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height:0; font-size: 0.75rem;">Ver auto</a>
                    @endif
                </div>
            @empty
                <p style="padding: 2rem; text-align: center; color: var(--portal-muted);">No has guardado vehículos todavía.</p>
            @endforelse
        </div>
    </section>

    <section class="dashboard-panel" id="compare">
        <div class="panel-heading"><div><p class="portal-kicker">Análisis</p><h2>Comparador</h2></div></div>
        <div class="list-stack">
            @forelse ($comparisonVehicles as $vehicle)
                <div class="list-row">
                    <div>
                        <strong>{{ $vehicle->title }}</strong>
                        <p>{{ $vehicle->fuel_type }} · {{ $vehicle->transmission }}</p>
                    </div>
                    <a href="{{ route('catalog.show', $vehicle->slug) }}" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height:0; font-size: 0.75rem;">Ficha</a>
                </div>
            @empty
                <p style="padding: 2rem; text-align: center; color: var(--portal-muted);">Agrega autos para comparar sus specs.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="panel-grid reveal reveal--delay-2" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.5rem;">
    <section class="dashboard-panel" id="saved-searches">
        <div class="panel-heading"><div><p class="portal-kicker">Notificaciones</p><h2>Búsquedas Guardadas</h2></div></div>
        <div class="list-stack">
            @forelse ($savedSearches as $search)
                <div class="list-row">
                    <div>
                        <strong>{{ $search->name }}</strong>
                        <p>Frecuencia: {{ $search->notification_frequency }}</p>
                    </div>
                    <form method="POST" action="{{ route('buyer.saved-searches.destroy', $search) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height:0; font-size: 0.75rem; color: var(--portal-warn);">Eliminar</button>
                    </form>
                </div>
            @empty
                <p style="padding: 2rem; text-align: center; color: var(--portal-muted);">No tienes alertas de búsqueda activas.</p>
            @endforelse
        </div>
    </section>

    <section class="dashboard-panel" id="messages">
        <div class="panel-heading"><div><p class="portal-kicker">Contacto</p><h2>Mis Mensajes</h2></div></div>
        <div class="list-stack">
            @forelse ($conversations as $conversation)
                <div class="list-row">
                    <div>
                        <strong>{{ $conversation->subject ?: 'Consulta por vehículo' }}</strong>
                        <p>{{ optional($conversation->last_message_at)->diffForHumans() }}</p>
                    </div>
                    <span class="status-badge">Abierta</span>
                </div>
            @empty
                <p style="padding: 2rem; text-align: center; color: var(--portal-muted);">No tienes conversaciones activas.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
