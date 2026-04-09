@extends('layouts.portal')

@section('title', 'Publicaciones vendedor | Movikaa')
@section('portal-eyebrow', 'Publicaciones')
@section('portal-title', 'Gesti?n de publicaciones')
@section('portal-copy', 'Filtra, pagina, prioriza y edita tu inventario desde una vista operativa pensada para trabajar con muchos anuncios sin perder tiempo.')

@section('header-actions')
    <a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Nuevo anuncio</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Inventario online</span>
        <strong>{{ $listingSummary['published'] }}</strong>
        <p>Publicaciones activas en el buscador.</p>
    </article>
    <article class="metric-card">
        <span>Borradores</span>
        <strong>{{ $listingSummary['draft'] }}</strong>
        <p>Anuncios pendientes de completar.</p>
    </article>
    <article class="metric-card">
        <span>Interesados</span>
        <strong>{{ number_format($listingSummary['leads'] ?? $listingSummary['contactos'] ?? 0) }}</strong>
        <p>Leads generados por todos tus autos.</p>
    </article>
    <article class="metric-card">
        <span>Exposición</span>
        <strong>{{ number_format($listingSummary['views']) }}</strong>
        <p>Visualizaciones totales del inventario.</p>
    </article>
</section>
<section class="dashboard-panel reveal reveal--delay-1">
    <div class="panel-heading">
        <div><p class="portal-kicker">Filtrar</p><h2>Mis Publicaciones</h2></div>
        <span class="status-badge">{{ $sellerListings->total() }} registros</span>
    </div>

    <form method="GET" action="{{ route('seller.listings') }}" class="portal-form">
        <div class="form-grid" style="grid-template-columns: repeat(4, 1fr) auto; align-items: flex-end; gap: 1rem;">
            <label class="form-field"><span>Búsqueda</span><input type="text" name="q" value="{{ $sellerFilters['q'] }}" placeholder="Título o placa..." /></label>
            <label class="form-field"><span>Estado</span>
                <select name="status">
                    <option value="">Cualquier estado</option>
                    <option value="draft" @selected($sellerFilters['status'] === 'draft')>Borrador</option>
                    <option value="published" @selected($sellerFilters['status'] === 'published')>Publicada</option>
                    <option value="paused" @selected($sellerFilters['status'] === 'paused')>Pausada</option>
                </select>
            </label>
            <label class="form-field"><span>Orden</span>
                <select name="sort">
                    <option value="latest" @selected($sellerFilters['sort'] === 'latest')>Recientes</option>
                    <option value="price_desc" @selected($sellerFilters['sort'] === 'price_desc')>Precio mayor</option>
                    <option value="price_asc" @selected($sellerFilters['sort'] === 'price_asc')>Precio menor</option>
                </select>
            </label>
            <label class="form-field"><span>Ciudad</span><input type="text" name="city" value="{{ $sellerFilters['city'] }}" placeholder="Ubicación..." /></label>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="button button--solid">Aplicar</button>
                <a href="{{ route('seller.listings') }}" class="button button--ghost">Reset</a>
            </div>
        </div>
    </form>
</section>

<section class="dashboard-panel reveal reveal--delay-2">
    <div class="table-shell">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>Vehículo</th>
                    <th>Estado / Ubicación</th>
                    <th>Precio</th>
                    <th>Impacto</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($sellerListings as $vehicle)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 32px; border-radius: 4px; background: var(--portal-soft); overflow: hidden;">
                                @if($vehicle->media->where('is_primary', true)->first())
                                    <img src="{{ asset('storage/'.$vehicle->media->where('is_primary', true)->first()->path) }}" style="width:100%; height:100%; object-fit: cover;" />
                                @endif
                            </div>
                            <div>
                                <strong>{{ $vehicle->title }}</strong>
                                <p style="margin:0; font-size: 0.75rem; color: var(--portal-muted);">{{ $vehicle->make?->name }} · {{ $vehicle->year }} · <span style="text-transform: uppercase;">{{ $vehicle->publication_tier }}</span></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : 'status-badge--warn' }}">{{ ucfirst($vehicle->status) }}</span>
                        <p style="margin:0; font-size: 0.75rem; color: var(--portal-muted); margin-top: 2px;">{{ $vehicle->city ?: 'Sin locación' }}</p>
                    </td>
                    <td>
                        @php($price = \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote))
                        <strong style="color: var(--portal-primary);">{{ $price['primary_formatted'] }}</strong>
                        <p style="margin:0; font-size: 0.7rem; color: var(--portal-muted);">{{ $price['secondary_formatted'] }}</p>
                    </td>
                    <td>
                        <div style="display: flex; gap: 1rem;">
                            <div><strong style="display:block; font-size:0.85rem;">{{ $vehicle->lead_count }}</strong><span style="font-size:0.65rem; color: var(--portal-muted);">CONTACTOS</span></div>
                            <div><strong style="display:block; font-size:0.85rem;">{{ number_format($vehicle->view_count ?? 0) }}</strong><span style="font-size:0.65rem; color: var(--portal-muted);">VISTAS</span></div>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                            <a href="{{ route('seller.vehicles.edit', $vehicle) }}" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Editar</a>
                            <a href="{{ route('catalog.show', $vehicle->slug) }}" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Ver</a>
                            <form method="POST" action="{{ route('seller.vehicles.destroy', $vehicle) }}" onsubmit="return confirm('¿Eliminar anuncio?');" style="display:inline;">@csrf @method('DELETE')<button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem; color: var(--portal-warn);">Borrar</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; padding: 4rem;">Aún no has registrado ningún vehículo en tu inventario.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $sellerListings->links() }}</div>
</section>
@endsection

