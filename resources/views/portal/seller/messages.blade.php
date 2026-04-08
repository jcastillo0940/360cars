@extends('layouts.portal')

@section('title', 'Mensajes vendedor | Movikaa')
@section('portal-eyebrow', 'Seller mensajes')
@section('portal-title', 'Conversaciones con compradores')
@section('portal-copy', 'Aquí ves las consultas que entran desde el detalle público de tus autos. El sitio conecta a las partes; no procesa la compra del vehículo.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('seller.dashboard') }}">Resumen</a>
    <a href="{{ route('seller.listings') }}">Publicaciones</a>
    <a href="{{ route('seller.onboarding.create') }}">Nuevo anuncio</a>
    <a href="{{ route('seller.media') }}">Media</a>
    <a href="{{ route('seller.messages') }}" class="is-active">Mensajes</a>
    <a href="{{ route('seller.billing') }}">Pagos</a>
    <a href="{{ route('buyer.dashboard') }}">Actividad comprador</a>
</nav>
@endsection

@section('content')
<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Mensajes</p><h2>Bandeja vendedor</h2></div><span class="status-badge">{{ $conversationList->total() }} hilos</span></div>
    <div class="catalog-stack">
        @forelse ($conversationList as $conversation)
            <article class="catalog-block">
                <div class="catalog-block__header">
                    <div>
                        <strong>{{ $conversation->subject ?: 'Consulta comercial' }}</strong>
                        <p>{{ $conversation->vehicle?->title ?: 'Conversaci?n general' }}</p>
                    </div>
                    <span class="status-badge">{{ optional($conversation->last_message_at)->diffForHumans() ?? 'Sin actividad' }}</span>
                </div>
                <div class="list-row">
                    <div>
                        <strong>Comprador interesado</strong>
                        <p>{{ optional($conversation->participants->firstWhere('pivot.role', 'comprador'))->name ?: 'Usuario comprador' }}</p>
                    </div>
                    <a href="{{ route('catalog.show', $conversation->vehicle?->slug) }}" class="button button--ghost">Ver anuncio</a>
                </div>
            </article>
        @empty
            <div class="empty-state"><strong>Sin mensajes todavía.</strong><p>Cuando un comprador te escriba desde la ficha publica de un auto, la conversación aparecera aquí.</p></div>
        @endforelse
    </div>
    <div class="pagination-shell">{{ $conversationList->links() }}</div>
</section>
@endsection
