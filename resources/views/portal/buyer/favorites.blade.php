@extends('layouts.portal')

@section('title', 'Favoritos comprador | Movikaa')
@section('portal-eyebrow', 'Buyer favoritos')
@section('portal-title', 'Favoritos guardados')
@section('portal-copy', 'Tus autos favoritos en una vista dedicada, con paginacion y acceso rápido al detalle.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('buyer.dashboard') }}">Resumen</a>
    <a href="{{ route('buyer.favorites.index') }}" class="is-active">Favoritos</a>
    <a href="{{ route('buyer.comparisons.index') }}">Comparador</a>
    <a href="{{ route('buyer.searches.index') }}">B?squedas</a>
    <a href="{{ route('buyer.messages.index') }}">Mensajes</a>
    <a href="{{ route('seller.dashboard') }}">Actividad vendedor</a>
</nav>
@endsection

@section('content')
<section class="dashboard-panel">
    <div class="panel-heading">
        <div>
            <p class="portal-kicker">Panel del comprador</p>
            <h2>Favoritos guardados</h2>
        </div>
        <span class="status-badge">{{ $favoritesList->total() }} autos</span>
    </div>

    @if($favoritesList->isEmpty())
        <div class="empty-state" style="padding: 4rem 2rem;">
            <div style="width: 64px; height: 64px; background: var(--portal-bg); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--portal-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <strong style="font-size: 1.125rem; color: var(--portal-on-surface);">Aún no tienes favoritos</strong>
            <p style="margin-top: 0.5rem; color: var(--portal-muted);">Explora el catálogo y guarda los autos que más te gusten para verlos aquí.</p>
            <a href="{{ route('catalog.index') }}" class="button button--solid" style="margin-top: 1.5rem;">Explorar inventario</a>
        </div>
    @else
        <div class="favorites-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            @foreach ($favoritesList as $favorite)
                @php $vehicle = $favorite->vehicle; @endphp
                @if($vehicle)
                <article class="favorite-card" style="background: white; border: 1px solid var(--portal-border); border-radius: 24px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column;">
                    <div style="position: relative; height: 180px;">
                        <img src="{{ $vehicle->media->where('is_primary', true)->first()?->thumbUrl() ?: '/img/placeholder-car.jpg' }}" 
                             alt="{{ $vehicle->title }}" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 1rem; left: 1rem; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); color: white; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; tracking: 0.05em;">
                            {{ $vehicle->year }}
                        </div>
                    </div>
                    
                    <div style="padding: 1.25rem; flex-grow: 1; display: flex; flex-direction: column;">
                        <p style="text-transform: uppercase; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.1em; color: var(--portal-primary); margin: 0;">{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }}</p>
                        <h3 style="margin: 0.5rem 0 0.25rem; font-family: var(--font-headline); font-size: 1.125rem; font-weight: 800; color: var(--portal-on-surface); line-height: 1.3;">{{ $vehicle->title }}</h3>
                        
                        <div style="margin-top: auto; padding-top: 1.25rem; display: flex; items-center; justify-between; border-top: 1px solid var(--portal-border);">
                            <div>
                                <p style="margin: 0; font-size: 0.75rem; color: var(--portal-muted);">{{ $vehicle->city ?: 'Costa Rica' }}</p>
                                <strong style="font-size: 1rem; color: var(--portal-on-surface);">₡{{ number_format((float) $vehicle->price, 0, ',', '.') }}</strong>
                            </div>
                            <a href="{{ route('catalog.show', $vehicle->slug) }}" class="button button--ghost" style="padding: 0.5rem 1rem; border-radius: 12px; font-size: 0.75rem;">Ver auto</a>
                        </div>
                    </div>
                </article>
                @endif
            @endforeach
        </div>
        <div class="pagination-shell" style="margin-top: 2rem;">
            {{ $favoritesList->links() }}
        </div>
    @endif
</section>
@endsection
