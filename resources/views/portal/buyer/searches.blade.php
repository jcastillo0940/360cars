@extends('layouts.portal')

@section('title', 'B?squedas comprador | Movikaa')
@section('portal-eyebrow', 'Buyer búsquedas')
@section('portal-title', 'B?squedas guardadas')
@section('portal-copy', 'Tus alertas y filtros guardados en una sola pantalla, con acceso m?s ordenado y paginado.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('buyer.dashboard') }}">Resumen</a>
    <a href="{{ route('buyer.favorites.index') }}">Favoritos</a>
    <a href="{{ route('buyer.comparisons.index') }}">Comparador</a>
    <a href="{{ route('buyer.searches.index') }}" class="is-active">B?squedas</a>
    <a href="{{ route('buyer.messages.index') }}">Mensajes</a>
    <a href="{{ route('seller.dashboard') }}">Actividad vendedor</a>
</nav>
@endsection

@section('content')
<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Alertas</p><h2>B?squedas guardadas</h2></div><span class="status-badge">{{ $savedSearchList->total() }} filtros</span></div>
    <div class="catalog-stack">
        @forelse ($savedSearchList as $search)
            <article class="catalog-block">
                <div class="catalog-block__header"><div><strong>{{ $search->name }}</strong><p>{{ strtoupper($search->notification_frequency) }}</p></div></div>
                <p class="empty-copy">{{ json_encode($search->filters, JSON_UNESCAPED_UNICODE) }}</p>
                <form method="POST" action="{{ route('buyer.saved-searches.destroy', $search) }}">@csrf @method('DELETE')<button type="submit" class="button button--ghost">Eliminar</button></form>
            </article>
        @empty
            <div class="empty-state"><strong>Sin alertas activas.</strong><p>Desde el inventario puedes guardar filtros para volver rápido.</p></div>
        @endforelse
    </div>
    <div class="pagination-shell">{{ $savedSearchList->links() }}</div>
</section>
@endsection

