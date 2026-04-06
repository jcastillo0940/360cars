@extends('layouts.portal')

@section('title', 'Comparador buyer | Movikaa')
@section('portal-eyebrow', 'Buyer comparador')
@section('portal-title', 'Vehiculos en comparacion')
@section('portal-copy', 'Revisa hasta 4 opciones lado a lado y recibe una lectura simple de cual se ve mas fuerte dentro de tu shortlist.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('buyer.dashboard') }}">Overview</a>
    <a href="{{ route('buyer.favorites.index') }}">Favoritos</a>
    <a href="{{ route('buyer.comparisons.index') }}" class="is-active">Comparador</a>
    <a href="{{ route('buyer.searches.index') }}">Busquedas</a>
    <a href="{{ route('buyer.messages.index') }}">Mensajes</a>
    <a href="{{ route('catalog.index') }}">Volver al inventario</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid dashboard-grid--three-up compact-grid">
    <article class="metric-card">
        <span>En comparador</span>
        <strong>{{ $comparisonVehicles->count() }}</strong>
        <p>Hasta 4 vehiculos al mismo tiempo.</p>
    </article>
    <article class="metric-card">
        <span>Mas nuevo</span>
        <strong>{{ $comparisonVehicles->max('year') ?: 'N/D' }}</strong>
        <p>Referencia rapida de ano.</p>
    </article>
    <article class="metric-card">
        <span>Rango de precio</span>
        <strong>
            @if ($comparisonVehicles->count())
                ₡{{ number_format((float) $comparisonVehicles->min('price'), 0, ',', '.') }} - ₡{{ number_format((float) $comparisonVehicles->max('price'), 0, ',', '.') }}
            @else
                N/D
            @endif
        </strong>
        <p>Te ayuda a ubicar las opciones rapido.</p>
    </article>
</section>

@if ($comparisonRecommendation)
<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Mejor opcion ahora</p>
                <h2>{{ $comparisonRecommendation['winner']['vehicle']->title }}</h2>
            </div>
            <span class="status-badge status-badge--success">{{ $comparisonRecommendation['winner']['score'] }}/100</span>
        </div>
        <div class="catalog-stack">
            <article class="catalog-block">
                <strong>{{ $comparisonRecommendation['winner']['headline'] }}</strong>
                <p class="empty-copy">{{ $comparisonRecommendation['winner']['vehicle']->make?->name }} · {{ $comparisonRecommendation['winner']['vehicle']->model?->name }} · {{ $comparisonRecommendation['winner']['vehicle']->year }}</p>
                <ul class="insight-list mt-4">
                    @foreach ($comparisonRecommendation['winner']['reasons'] as $reason)
                        <li>{{ ucfirst($reason) }}.</li>
                    @endforeach
                </ul>
                <div class="table-actions mt-4">
                    <a href="{{ route('catalog.show', $comparisonRecommendation['winner']['vehicle']->slug) }}" class="button button--solid">Ver ficha completa</a>
                </div>
            </article>
        </div>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Lectura del comparador</p>
                <h2>Ranking rapido</h2>
            </div>
        </div>
        <div class="catalog-stack">
            @foreach ($comparisonRecommendation['ranking'] as $index => $item)
                <article class="catalog-block">
                    <div class="catalog-block__header">
                        <div>
                            <strong>#{{ $index + 1 }} {{ $item['vehicle']->title }}</strong>
                            <p>{{ $item['headline'] }}</p>
                        </div>
                        <span class="status-badge">{{ $item['score'] }}/100</span>
                    </div>
                    <p class="empty-copy">{{ implode(' · ', collect($item['reasons'])->map(fn ($reason) => ucfirst($reason))->all()) }}.</p>
                </article>
            @endforeach
        </div>
    </article>
</section>
@endif

<section class="dashboard-panel">
    <div class="panel-heading">
        <div>
            <p class="portal-kicker">Comparador</p>
            <h2>Vista rapida de decision</h2>
        </div>
        <a href="{{ route('catalog.index') }}" class="button button--solid">Explorar mas autos</a>
    </div>

    @if ($comparisonVehicles->isNotEmpty())
        <div class="comparison-table-wrap">
            <table class="portal-table comparison-table">
                <thead>
                    <tr>
                        <th>Vehiculo</th>
                        <th>Precio</th>
                        <th>Ano</th>
                        <th>Combustible</th>
                        <th>Transmision</th>
                        <th>Kilometraje</th>
                        <th>Ciudad</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($comparisonVehicles as $vehicle)
                        <tr>
                            <td>
                                <div class="table-vehicle-cell">
                                    <div>
                                        <strong>{{ $vehicle->title }}</strong>
                                        <p>{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>₡{{ number_format((float) $vehicle->price, 0, ',', '.') }}</td>
                            <td>{{ $vehicle->year ?: 'N/D' }}</td>
                            <td>{{ $vehicle->fuel_type ?: 'N/D' }}</td>
                            <td>{{ $vehicle->transmission ?: 'N/D' }}</td>
                            <td>{{ $vehicle->mileage ? number_format((float) $vehicle->mileage, 0, ',', '.') . ' km' : 'N/D' }}</td>
                            <td>{{ $vehicle->city ?: 'Costa Rica' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('catalog.show', $vehicle->slug) }}" class="button button--ghost">Ver ficha</a>
                                    <form method="POST" action="{{ route('buyer.comparisons.destroy', $vehicle) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button button--ghost-danger">Quitar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <strong>Tu comparador esta vacio.</strong>
            <p>Agrega hasta 4 vehiculos desde el inventario para revisarlos aqui lado a lado.</p>
            <a href="{{ route('catalog.index') }}" class="button button--solid">Ir al inventario</a>
        </div>
    @endif
</section>
@endsection
