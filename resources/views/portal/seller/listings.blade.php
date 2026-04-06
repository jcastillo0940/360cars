@extends('layouts.portal')

@section('title', 'Publicaciones seller | Movikaa')
@section('portal-eyebrow', 'Seller listings')
@section('portal-title', 'Gestion de publicaciones')
@section('portal-copy', 'Filtra, pagina, prioriza y edita tu inventario desde una vista operativa pensada para trabajar con muchos anuncios sin perder tiempo.')

@section('header-actions')
    <a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Nuevo anuncio</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('seller.dashboard') }}">Overview</a>
    <a href="{{ route('seller.listings') }}" class="is-active">Publicaciones</a>
    <a href="{{ route('seller.onboarding.create') }}">Nuevo anuncio</a>
    <a href="{{ route('seller.media') }}">Media</a>
    <a href="{{ route('seller.billing') }}">Billing</a>
    <a href="{{ route('buyer.dashboard') }}">Actividad buyer</a>
</nav>
@endsection

@section('content')
<section class="seller-kpi-strip">
    <article class="seller-kpi-card">
        <span class="seller-kpi-card__label">Inventario activo</span>
        <strong>{{ $listingSummary['published'] }}</strong>
        <p>Publicaciones visibles ahora mismo en el marketplace.</p>
    </article>
    <article class="seller-kpi-card">
        <span class="seller-kpi-card__label">Borradores</span>
        <strong>{{ $listingSummary['draft'] }}</strong>
        <p>Anuncios que todavia puedes pulir antes de exponerlos.</p>
    </article>
    <article class="seller-kpi-card">
        <span class="seller-kpi-card__label">Leads recibidos</span>
        <strong>{{ number_format($listingSummary['leads']) }}</strong>
        <p>Contactos acumulados desde todas tus publicaciones.</p>
    </article>
    <article class="seller-kpi-card">
        <span class="seller-kpi-card__label">Vistas acumuladas</span>
        <strong>{{ number_format($listingSummary['views']) }}</strong>
        <p>Senal de exposicion para medir el rendimiento del inventario.</p>
    </article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading">
        <div>
            <p class="portal-kicker">Filtros avanzados</p>
            <h2>Encuentra rapido el anuncio que quieres trabajar</h2>
        </div>
        <span class="status-badge">{{ $sellerListings->total() }} resultados</span>
    </div>

    <form method="GET" action="{{ route('seller.listings') }}" class="portal-form">
        <div class="seller-filter-grid">
            <label class="form-field">
                <span>Buscar</span>
                <input type="text" name="q" value="{{ $sellerFilters['q'] }}" placeholder="Titulo, ciudad o placa" />
            </label>
            <label class="form-field">
                <span>Estado</span>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="draft" @selected($sellerFilters['status'] === 'draft')>Borrador</option>
                    <option value="published" @selected($sellerFilters['status'] === 'published')>Publicada</option>
                    <option value="paused" @selected($sellerFilters['status'] === 'paused')>Pausada</option>
                    <option value="sold" @selected($sellerFilters['status'] === 'sold')>Vendida</option>
                </select>
            </label>
            <label class="form-field">
                <span>Plan</span>
                <select name="tier">
                    <option value="">Todos</option>
                    <option value="basic" @selected($sellerFilters['tier'] === 'basic')>Basic</option>
                    <option value="estandar" @selected($sellerFilters['tier'] === 'estandar')>Estandar</option>
                    <option value="premium" @selected($sellerFilters['tier'] === 'premium')>Premium</option>
                    <option value="agencia" @selected($sellerFilters['tier'] === 'agencia')>Agencia</option>
                    <option value="agencia-pro" @selected($sellerFilters['tier'] === 'agencia-pro')>Agencia Pro</option>
                </select>
            </label>
            <label class="form-field">
                <span>Marca</span>
                <select name="make">
                    <option value="">Todas</option>
                    @foreach($makes as $make)
                        <option value="{{ $make->id }}" @selected((int) $sellerFilters['make'] === $make->id)>{{ $make->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-field">
                <span>Ciudad</span>
                <input type="text" name="city" value="{{ $sellerFilters['city'] }}" placeholder="San Jose, Heredia..." />
            </label>
            <label class="form-field">
                <span>Año desde</span>
                <input type="number" name="year_from" min="1950" max="{{ date('Y') + 1 }}" value="{{ $sellerFilters['year_from'] }}" placeholder="2018" />
            </label>
            <label class="form-field">
                <span>Año hasta</span>
                <input type="number" name="year_to" min="1950" max="{{ date('Y') + 1 }}" value="{{ $sellerFilters['year_to'] }}" placeholder="{{ date('Y') }}" />
            </label>
            <label class="form-field">
                <span>Ordenar por</span>
                <select name="sort">
                    <option value="latest" @selected($sellerFilters['sort'] === 'latest')>Mas recientes</option>
                    <option value="price_desc" @selected($sellerFilters['sort'] === 'price_desc')>Precio mayor</option>
                    <option value="price_asc" @selected($sellerFilters['sort'] === 'price_asc')>Precio menor</option>
                    <option value="year_desc" @selected($sellerFilters['sort'] === 'year_desc')>Año mas nuevo</option>
                    <option value="year_asc" @selected($sellerFilters['sort'] === 'year_asc')>Año mas antiguo</option>
                    <option value="leads_desc" @selected($sellerFilters['sort'] === 'leads_desc')>Mas leads</option>
                    <option value="views_desc" @selected($sellerFilters['sort'] === 'views_desc')>Mas vistas</option>
                </select>
            </label>
        </div>

        <div class="seller-toolbar">
            <div class="seller-toolbar__meta">
                <span class="status-badge status-badge--success">{{ $listingSummary['published'] }} activas</span>
                <span class="status-badge">{{ $listingSummary['paused'] }} pausadas</span>
                <span class="status-badge">{{ $listingSummary['expired'] }} vencidas</span>
            </div>
            <div class="form-actions">
                <button type="submit" class="button button--solid">Aplicar filtros</button>
                <a href="{{ route('seller.listings') }}" class="button button--ghost">Limpiar</a>
            </div>
        </div>
    </form>
</section>

<section class="dashboard-panel">
    <div class="panel-heading">
        <div>
            <p class="portal-kicker">Gestion diaria</p>
            <h2>Publicaciones</h2>
        </div>
        <a href="{{ route('seller.onboarding.create') }}" class="button button--ghost">Crear otro anuncio</a>
    </div>

    <div class="table-shell">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>Vehiculo</th>
                    <th>Estado</th>
                    <th>Precio</th>
                    <th>Rendimiento</th>
                    <th>Media</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($sellerListings as $vehicle)
                <tr>
                    <td>
                        <strong>{{ $vehicle->title }}</strong>
                        <span>{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }} · {{ $vehicle->year }}</span>
                        <div class="seller-inline-badges">
                            <span class="status-badge">{{ ucfirst($vehicle->publication_tier) }}</span>
                            @if ($vehicle->is_featured)
                                <span class="status-badge status-badge--success">Destacado</span>
                            @endif
                            @if ($vehicle->expires_at)
                                <span class="status-badge">{{ $vehicle->expires_at->isPast() ? 'Vencido' : 'Vence ' . $vehicle->expires_at->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : 'status-badge--warn' }}">{{ ucfirst($vehicle->status) }}</span>
                        <span>{{ $vehicle->city ?: 'Ubicacion pendiente' }}</span>
                    </td>
                    <td>
                        @php($price = \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote))
                        <strong>{{ $price['primary_formatted'] }}</strong>
                        <span>{{ $price['secondary_formatted'] }}</span>
                    </td>
                    <td>
                        <strong>{{ number_format($vehicle->lead_count) }} leads</strong>
                        <span>{{ number_format($vehicle->view_count ?? 0) }} vistas</span>
                    </td>
                    <td>
                        <strong>{{ $vehicle->media->count() }} archivos</strong>
                        <span>{{ $vehicle->supports_360 ? '360 activo' : 'Sin 360' }} · {{ $vehicle->has_video ? 'Con video' : 'Sin video' }}</span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('seller.vehicles.edit', $vehicle) }}" class="text-link">Editar</a>
                            @if ($vehicle->status !== 'published')
                                <form method="POST" action="{{ route('seller.vehicles.publish', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Publicar</button></form>
                            @else
                                <form method="POST" action="{{ route('seller.vehicles.pause', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Pausar</button></form>
                            @endif
                            @if ($vehicle->publication_tier === 'basic' && $vehicle->expires_at && $vehicle->expires_at->isPast())
                                <form method="POST" action="{{ route('seller.vehicles.refresh-basic', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Renovar gratis</button></form>
                            @endif
                            <a href="{{ route('catalog.show', $vehicle->slug) }}" class="text-link">Ver anuncio</a>
                            <form method="POST" action="{{ route('seller.vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Eliminar publicacion?');">@csrf @method('DELETE')<button type="submit" class="text-link">Eliminar</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Aun no tienes publicaciones registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-shell">{{ $sellerListings->links() }}</div>
</section>
@endsection


