@extends('layouts.portal')

@section('title', 'Mensajes vendedor | Movikaa')
@section('portal-eyebrow', 'Seller mensajes')
@section('portal-title', 'Conversaciones con compradores')
@section('portal-copy', 'Aquí ves las consultas que entran desde el detalle público de tus autos. El sitio conecta a las partes; no procesa la compra del vehículo.')

@section('content')
<section class="dashboard-panel reveal">
    <div class="panel-heading">
        <div><p class="portal-kicker">Comunicación</p><h2>Bandeja de Entrada</h2></div>
        <span class="status-badge">{{ $conversationList->total() }} conversaciones</span>
    </div>
    <div class="list-stack" style="margin-top: 1rem;">
        @forelse ($conversationList as $conversation)
            <article class="list-row" style="background: var(--portal-soft); border: 1px solid var(--portal-border); border-radius: 8px; padding: 1.5rem; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                         <strong style="font-size: 1.05rem; color: var(--portal-primary);">{{ $conversation->subject ?: 'Consulta por vehículo' }}</strong>
                         <span class="status-badge" style="font-size: 0.65rem;">{{ optional($conversation->last_message_at)->diffForHumans() ?? 'Sin actividad' }}</span>
                    </div>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--portal-muted);">Relacionado con: <strong>{{ $conversation->vehicle?->title ?: 'General' }}</strong></p>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem;">Interesado: {{ optional($conversation->participants->firstWhere('pivot.role', 'comprador'))->name ?: 'Anónimo' }}</p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    @if($conversation->vehicle)
                        <a href="{{ route('catalog.show', $conversation->vehicle->slug) }}" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;" target="_blank">Ver Auto</a>
                    @endif
                    <span class="button button--solid" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem; cursor: not-allowed; opacity: 0.5;">Ver Chat</span>
                </div>
            </article>
        @empty
            <div style="padding: 4rem; text-align: center; color: var(--portal-muted);">
                Aún no has recibido mensajes de posibles compradores.
            </div>
        @endforelse
    </div>
    <div class="pagination-shell">{{ $conversationList->links() }}</div>
</section>
@endsection
