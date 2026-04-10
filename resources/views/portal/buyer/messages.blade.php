@extends('layouts.portal')

@section('title', 'Mensajes comprador | Movikaa')
@section('portal-eyebrow', 'Buyer mensajes')
@section('portal-title', 'Conversaciones con vendedores')
@section('portal-copy', 'Bandeja dedicada para seguimiento comercial. Aquí solo contactas y das continuidad al interes.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('buyer.dashboard') }}">Resumen</a>
    <a href="{{ route('buyer.favorites.index') }}">Favoritos</a>
    <a href="{{ route('buyer.comparisons.index') }}">Comparador</a>
    <a href="{{ route('buyer.searches.index') }}">B?squedas</a>
    <a href="{{ route('buyer.messages.index') }}" class="is-active">Mensajes</a>
    <a href="{{ route('seller.dashboard') }}">Actividad vendedor</a>
</nav>
@endsection

@section('content')
<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Mensajes</p><h2>Conversaciones</h2></div><span class="status-badge">{{ $conversationList->total() }} hilos</span></div>
    <div class="catalog-stack">
        @forelse ($conversationList as $conversation)
            <article class="catalog-block">
                <div class="catalog-block__header"><div><strong>{{ $conversation->subject ?: 'Consulta comercial' }}</strong><p>{{ $conversation->vehicle?->title ?: 'Conversaci?n general' }}</p></div><span class="status-badge">{{ optional($conversation->last_message_at)->diffForHumans() ?? 'Sin actividad' }}</span></div>
            </article>
        @empty
            <div class="empty-state"><strong>Sin mensajes todavía.</strong><p>Cuando escribas desde el detalle del vehículo, la conversación aparecera aquí.</p></div>
        @endforelse
    </div>
    <div class="pagination-shell">{{ $conversationList->links() }}</div>
</section>
@endsection

