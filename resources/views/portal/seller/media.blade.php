@extends('layouts.portal')

@section('title', 'Media vendedor | Movikaa')
@section('portal-eyebrow', 'Seller media')
@section('portal-title', 'Gestión de imágenes y galería')
@section('portal-copy', 'Primero elige un vehículo. Después podrás ver su galería, subir fotos nuevas o reemplazar una foto específica sin confundirte.')

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
@php
    $selectedVehicle = $selectedMediaVehicle;
@endphp

<section class="dashboard-grid">
    <article class="metric-card"><span>Vehículos</span><strong>{{ $vehicles->count() }}</strong></article>
    <article class="metric-card"><span>Publicadas</span><strong>{{ $publishedCount }}</strong></article>
    <article class="metric-card"><span>En cola</span><strong>{{ $processingCount }}</strong></article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading">
        <div>
            <p class="portal-kicker">Paso 1</p>
            <h2>Elige el vehículo que quieres gestionar</h2>
        </div>
    </div>

    @if ($mediaVehicles->isEmpty())
        <div class="empty-state">
            <strong>Sin publicaciones todavía.</strong>
            <p>Primero crea un anuncio para poder subir o reemplazar fotografías.</p>
        </div>
    @else
        <form method="GET" action="{{ route('seller.media') }}" class="portal-form">
            <div class="seller-filter-grid">
                <label class="form-field">
                    <span>Vehículo</span>
                    <select name="vehicle" onchange="this.form.submit()">
                        @foreach ($mediaVehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(optional($selectedVehicle)->id === $vehicle->id)>
                                {{ $vehicle->title }} · {{ $vehicle->media->count() }} fotos
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
        </form>
    @endif
</section>

@if ($selectedVehicle)
    <section class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Paso 2</p>
                <h2>Gestiona la galería de {{ $selectedVehicle->title }}</h2>
                <p class="portal-header__copy">
                    Usa “Subir nuevas fotos” para agregar más imágenes al final de la galería.
                    Usa “Reemplazar esta foto” dentro de cada tarjeta si quieres cambiar una imagen puntual.
                </p>
            </div>
            <a href="{{ route('seller.vehicles.edit', $selectedVehicle) }}" class="button button--ghost">Editar anuncio</a>
        </div>

        <div class="dashboard-grid dashboard-grid--three-up">
            <article class="metric-card">
                <span>Fotos actuales</span>
                <strong>{{ $selectedVehicle->media->count() }}</strong>
                <p>Total de imágenes asociadas a este vehículo.</p>
            </article>
            <article class="metric-card">
                <span>Principal</span>
                <strong>{{ optional($selectedVehicle->media->firstWhere('is_primary', true))->id ? '#'.optional($selectedVehicle->media->firstWhere('is_primary', true))->id : 'Sin definir' }}</strong>
                <p>La foto principal es la que aparece primero en el marketplace.</p>
            </article>
            <article class="metric-card">
                <span>Procesando</span>
                <strong>{{ $selectedVehicle->media->where('processing_status', 'pending')->count() }}</strong>
                <p>Imágenes que todavía están en cola o terminando de procesarse.</p>
            </article>
        </div>

        <div class="dashboard-panel" style="margin-top: 1.5rem;">
            <div class="panel-heading">
                <div>
                    <p class="portal-kicker">Subir nuevas</p>
                    <h2>Agregar fotos nuevas a la galería</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('seller.vehicles.media.store', $selectedVehicle) }}" enctype="multipart/form-data" class="portal-form">
                @csrf
                <div class="seller-filter-grid">
                    <label class="form-field" style="grid-column: 1 / -1;">
                        <span>Selecciona una o varias fotos</span>
                        <input type="file" name="images[]" multiple accept="image/*" required />
                        <small>Estas fotos se agregarán al final de la galería actual de {{ $selectedVehicle->title }}.</small>
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="button button--solid">Subir nuevas fotos</button>
                </div>
            </form>
        </div>

        <div class="panel-heading" style="margin-top: 2rem;">
            <div>
                <p class="portal-kicker">Paso 3</p>
                <h2>Reemplazar o reordenar fotos existentes</h2>
            </div>
        </div>

        <div class="chip-grid mt-4">
            @forelse ($selectedVehicle->media->sortBy('sort_order') as $media)
                @php
                    $imageUrl = $media->path !== '' ? \Illuminate\Support\Facades\Storage::disk($media->disk ?: 'public')->url($media->path) : null;
                    $label = $media->alt_text ?: 'Foto de galería';
                @endphp
                <article class="chip-card" style="display:grid; gap:1rem;">
                    <div style="display:grid; gap:0.75rem;">
                        <div style="display:flex; align-items:start; justify-content:space-between; gap:1rem;">
                            <div>
                                <strong>#{{ $media->id }} · {{ $label }}</strong>
                                <p>{{ ucfirst($media->processing_status) }}@if($media->is_primary) · principal @endif</p>
                            </div>
                            @if ($media->is_primary)
                                <span class="status-badge status-badge--success">Principal</span>
                            @endif
                        </div>

                        @if ($imageUrl)
                            <div style="border-radius:1rem; overflow:hidden; border:1px solid rgba(148,163,184,0.18); background:#0f172a;">
                                <img src="{{ $imageUrl }}" alt="{{ $label }}" style="display:block; width:100%; height:220px; object-fit:cover;">
                            </div>
                        @else
                            <div class="empty-state" style="min-height:220px;">
                                <strong>Imagen no disponible</strong>
                                <p>Este archivo todavía no tiene una ruta pública lista.</p>
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('seller.vehicles.media.replace', [$selectedVehicle, $media]) }}" enctype="multipart/form-data" class="portal-form">
                        @csrf
                        <div class="seller-filter-grid">
                            <label class="form-field" style="grid-column: 1 / -1;">
                                <span>Reemplazar esta foto</span>
                                <input type="file" name="image" accept="image/*" required />
                                <small>La imagen actual se sustituirá por la nueva, conservando su posición en la galería.</small>
                            </label>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="button button--solid">Reemplazar esta foto</button>
                        </div>
                    </form>
                    <div class="table-actions">
                        @if (! $media->is_primary)
                            <form method="POST" action="{{ route('seller.vehicles.media.primary', [$selectedVehicle, $media]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-link">Usar como principal</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('seller.vehicles.media.destroy', [$selectedVehicle, $media]) }}" onsubmit="return confirm('¿Eliminar esta fotografía?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-link">Eliminar</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <strong>Este vehículo todavía no tiene fotos.</strong>
                    <p>Usa el bloque de arriba para subir las primeras imágenes y luego aquí podrás reemplazarlas una por una.</p>
                </div>
            @endforelse
        </div>
    </section>
@endif
@endsection
