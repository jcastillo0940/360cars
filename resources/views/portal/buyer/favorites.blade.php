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
    <div class="panel-heading"><div><p class="portal-kicker">Favoritos</p><h2>Autos que te interesan</h2></div><span class="status-badge">{{ $favoritesList->total() }} guardados</span></div>
    <div class="catalog-stack">
        @forelse ($favoritesList as $favorite)
            <article class="catalog-block">
                <div class="catalog-block__header"><div><strong>{{ $favorite->vehicle?->title }}</strong><p>{{ $favorite->vehicle?->make?->name }} · {{ $favorite->vehicle?->model?->name }} · {{ $favorite->vehicle?->year }}</p></div><a href="{{ $favorite->vehicle ? route('catalog.show', $favorite->vehicle->slug) : '#' }}" class="button button--ghost">Ver detalle</a></div>
                <p class="empty-copy">{{ $favorite->vehicle?->city }} · ₡{{ number_format((float) $favorite->vehicle?->price, 0, ',', '.') }}</p>
            </article>
        @empty
            <div class="empty-state"><strong>Aún no tienes favoritos.</strong><p>Guarda autos desde el catálogo público y aparecerán aquí.</p></div>
        @endforelse
    </div>
    <div class="pagination-shell">{{ $favoritesList->links() }}</div>
</section>
@endsection
