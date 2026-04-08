@extends('layouts.portal')

@section('title', 'Media vendedor | Movikaa')
@section('portal-eyebrow', 'Seller media')
@section('portal-title', 'Gesti?n de im?genes y galer?a')
@section('portal-copy', 'Vista enfocada en pipeline, fotos principales y estado de procesamiento.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('seller.dashboard') }}">Resumen</a>
    <a href="{{ route('seller.listings') }}">Publicaciones</a>
    <a href="{{ route('seller.onboarding.create') }}">Nuevo anuncio</a>
    <a href="{{ route('seller.media') }}" class="is-active">Media</a>
    <a href="{{ route('seller.messages') }}">Mensajes</a>
    <a href="{{ route('seller.billing') }}">Pagos</a>
    <a href="{{ route('buyer.dashboard') }}">Actividad comprador</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Veh?culos</span><strong>{{ $vehicles->count() }}</strong></article>
    <article class="metric-card"><span>Publicadas</span><strong>{{ $publishedCount }}</strong></article>
    <article class="metric-card"><span>En cola</span><strong>{{ $processingCount }}</strong></article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Gesti?n de im?genes</p><h2>Estado de im?genes</h2></div></div>
    <div class="catalog-stack">
        @forelse ($vehicles as $vehicle)
            <article class="catalog-block">
                <div class="catalog-block__header"><div><strong>{{ $vehicle->title }}</strong><p>{{ $vehicle->media->count() }} archivos</p></div></div>
                <form method="POST" action="{{ route('seller.vehicles.media.store', $vehicle) }}" enctype="multipart/form-data" class="portal-form portal-form--inline">@csrf<input type="file" name="images[]" multiple accept="image/*" required><button type="submit" class="button button--solid">Subir media</button></form>
                <div class="chip-grid mt-4">
                    @forelse ($vehicle->media as $media)
                        <div class="chip-card">
                            <div><strong>#{{ $media->id }}</strong><p>{{ $media->processing_status }} @if($media->is_primary) · principal @endif</p></div>
                            <div class="table-actions">
                                @if (! $media->is_primary)
                                    <form method="POST" action="{{ route('seller.vehicles.media.primary', [$vehicle, $media]) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Principal</button></form>
                                @endif
                                <form method="POST" action="{{ route('seller.vehicles.media.destroy', [$vehicle, $media]) }}">@csrf @method('DELETE')<button type="submit" class="text-link">Quitar</button></form>
                            </div>
                        </div>
                    @empty
                        <p class="empty-copy">Sin media todavía.</p>
                    @endforelse
                </div>
            </article>
        @empty
            <div class="empty-state"><strong>Sin publicaciones.</strong><p>Primero crea un anuncio para gestionar su galer?a.</p></div>
        @endforelse
    </div>
</section>
@endsection



