@extends('layouts.portal')

@section('title', 'Media vendedor | Movikaa')
@section('portal-eyebrow', 'Seller media')
@section('portal-title', 'Gestión de imágenes y galería')
@section('portal-copy', 'Primero elige un vehículo. Después podrás ver su galería, subir fotos nuevas o reemplazar una foto específica sin confundirte.')

@section('content')
@php
    $selectedVehicle = $selectedMediaVehicle;
    $existingMediaBySlot = collect();
    if ($selectedVehicle) {
        $existingMediaBySlot = $selectedVehicle->media->groupBy(function ($media) {
            return $media->alt_text ?: 'extra';
        });
    }
@endphp

<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Capacidad del Plan</span>
        <strong>{{ $capabilities['max_photos'] }}</strong>
        <p>Fotos totales permitidas.</p>
    </article>
    <article class="metric-card">
        <span>Cargadas al Auto</span>
        <strong>{{ $selectedVehicle ? $selectedVehicle->media->count() : 0 }}</strong>
        <p>Imágenes en galería actual.</p>
    </article>
    <article class="metric-card">
        <span>Espacio Libre</span>
        <strong>{{ $selectedVehicle ? max(0, $capabilities['max_photos'] - $selectedVehicle->media->count()) : $capabilities['max_photos'] }}</strong>
        <p>Cupos disponibles para subir.</p>
    </article>
</section>

<section class="dashboard-panel reveal reveal--delay-1" style="margin: 1.5rem 0;">
    <div class="panel-heading">
        <div><p class="portal-kicker">Gestor de Media</p><h2>Vehículo a gestionar</h2></div>
    </div>
    <form method="GET" action="{{ route('seller.media') }}" class="portal-form">
        <label class="form-field" style="max-width: 400px;">
            <span>Cambiar de vehículo</span>
            <select name="vehicle" onchange="this.form.submit()">
                @foreach ($mediaVehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" @if(optional($selectedVehicle)->id === $vehicle->id) selected @endif>
                        {{ $vehicle->title }} ({{ $vehicle->year }})
                    </option>
                @endforeach
            </select>
        </label>
    </form>
</section>

@if ($selectedVehicle)
    <section class="dashboard-panel reveal reveal--delay-2">
        <div class="panel-heading" style="margin-bottom: 2rem;">
            <div>
                <p class="portal-kicker">Recomendado</p>
                <h2>Fotos de Identidad</h2>
                <p style="color: var(--portal-muted);">Las fotos en estas posiciones aparecen en las vistas principales de búsqueda.</p>
            </div>
            <a href="{{ route('catalog.show', $selectedVehicle->slug) }}" class="button button--ghost" target="_blank">Ver vista pública</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
            @php $mainSlots = ['frontal', 'trasera', 'lateral_izquierdo', 'lateral_derecho']; @endphp
            @foreach($mainSlots as $slot)
                @php $slotMedia = $existingMediaBySlot->get($slot, collect())->first(); @endphp
                <article style="background: var(--portal-soft); border: 2px solid {{ $slotMedia ? 'var(--portal-border)' : 'var(--portal-primary-soft)' }}; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: all 0.2s ease;">
                    <div style="position: relative; height: 160px; background: #000; display: flex; align-items: center; justify-content: center;">
                        @if ($slotMedia)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk($slotMedia->disk ?: 'public')->url($slotMedia->path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @if($slotMedia->is_primary)
                                <span style="position: absolute; top: 10px; right: 10px; background: var(--portal-primary); color: #000; font-size: 0.6rem; font-weight: 800; padding: 2px 6px; border-radius: 4px;">PORTADA</span>
                            @endif
                        @else
                            <div style="text-align: center; padding: 1rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--portal-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                <p style="font-size: 0.7rem; margin-top: 0.5rem; color: var(--portal-muted);">Pendiente</p>
                            </div>
                        @endif
                    </div>
                    <div style="padding: 1rem;">
                        <strong style="display: block; font-size: 0.8rem; text-transform: capitalize;">{{ str_replace('_', ' ', $slot) }}</strong>
                        <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                            @if($slotMedia)
                                <form method="POST" action="{{ route('seller.vehicles.media.replace', [$selectedVehicle, $slotMedia]) }}" enctype="multipart/form-data">
                                    @csrf
                                    <label class="button button--ghost" style="width: 100%; font-size: 0.75rem; min-height: 0; padding: 0.4rem; cursor: pointer;">
                                        Reemplazar
                                        <input type="file" name="image" accept="image/*" onchange="this.form.submit()" style="display: none;">
                                    </label>
                                </form>
                                @if(!$slotMedia->is_primary)
                                    <form method="POST" action="{{ route('seller.vehicles.media.primary', [$selectedVehicle, $slotMedia]) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="button button--ghost" style="width: 100%; font-size: 0.75rem; min-height: 0; padding: 0.4rem;">Poner de Portada</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('seller.vehicles.media.destroy', [$selectedVehicle, $slotMedia]) }}" onsubmit="return confirm('¿Quitar esta foto?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="button button--ghost" style="width: 100%; font-size: 0.75rem; min-height: 0; padding: 0.4rem; color: var(--portal-warn);">Quitar</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('seller.vehicles.media.store', $selectedVehicle) }}" enctype="multipart/form-data">
                                    @csrf
                                    <label class="button button--solid" style="width: 100%; font-size: 0.75rem; min-height: 0; padding: 0.5rem; cursor: pointer;">
                                        Subir Foto
                                        <input type="file" name="required_images[{{ $slot }}]" accept="image/*" onchange="this.form.submit()" style="display: none;">
                                    </label>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="panel-heading" style="margin: 3rem 0 2rem;">
            <div>
                <p class="portal-kicker">Complementario</p>
                <h2>Motor y Detalles</h2>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
            @php $detailSlots = ['interior_conductor', 'interior_copiloto', 'interior_pasajeros', 'motor']; @endphp
            @foreach($detailSlots as $slot)
                @php $slotMedia = $existingMediaBySlot->get($slot, collect())->first(); @endphp
                <article style="background: var(--portal-soft); border: 1px solid var(--portal-border); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
                    <div style="position: relative; height: 140px; background: #000; display: flex; align-items: center; justify-content: center;">
                        @if ($slotMedia)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk($slotMedia->disk ?: 'public')->url($slotMedia->path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div style="text-align: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--portal-muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </div>
                        @endif
                    </div>
                    <div style="padding: 1rem;">
                        <strong style="display: block; font-size: 0.75rem; text-transform: capitalize; color: var(--portal-muted);">{{ str_replace('_', ' ', $slot) }}</strong>
                        <div style="margin-top: 1rem;">
                            @if($slotMedia)
                                <form method="POST" action="{{ route('seller.vehicles.media.replace', [$selectedVehicle, $slotMedia]) }}" enctype="multipart/form-data">
                                    @csrf
                                    <label class="text-link" style="font-size: 0.75rem; cursor: pointer;">
                                        Cambiar
                                        <input type="file" name="image" accept="image/*" onchange="this.form.submit()" style="display: none;">
                                    </label>
                                </form>
                            @else
                                <form method="POST" action="{{ route('seller.vehicles.media.store', $selectedVehicle) }}" enctype="multipart/form-data">
                                    @csrf
                                    <label class="button button--ghost" style="width: 100%; font-size: 0.7rem; min-height: 0; padding: 0.3rem; cursor: pointer;">
                                        Subir
                                        <input type="file" name="required_images[{{ $slot }}]" accept="image/*" onchange="this.form.submit()" style="display: none;">
                                    </label>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="panel-heading" style="margin: 3rem 0 2rem;">
            <div>
                <p class="portal-kicker">Carrusel Final</p>
                <h2>Fotos Extras</h2>
                <p style="color: var(--portal-muted);">Imágenes adicionales del tablero, aros, baúl o cualquier detalle extra.</p>
            </div>
            <form method="POST" action="{{ route('seller.vehicles.media.store', $selectedVehicle) }}" enctype="multipart/form-data">
                @csrf
                <label class="button button--solid" style="cursor: pointer;">
                    Añadir Extras
                    <input type="file" name="images[]" multiple accept="image/*" onchange="this.form.submit()" style="display: none;">
                </label>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem;">
            @forelse ($existingMediaBySlot->get('extra', collect()) as $extraMedia)
                <article style="background: var(--portal-soft); border-radius: 8px; overflow: hidden; position: relative; aspect-ratio: 1/1;">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk($extraMedia->disk ?: 'public')->url($extraMedia->path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 0.5rem; display: flex; justify-content: flex-end; gap: 0.4rem;">
                        <form method="POST" action="{{ route('seller.vehicles.media.destroy', [$selectedVehicle, $extraMedia]) }}" onsubmit="return confirm('¿Quitar?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: var(--portal-danger); color: #fff; border: none; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">&times;</button>
                        </form>
                    </div>
                </article>
            @empty
                <div style="grid-column: span 5; padding: 3rem; text-align: center; border: 1px dashed var(--portal-border); border-radius: 12px; color: var(--portal-muted);">
                    Sin fotos adicionales por ahora.
                </div>
            @endforelse
        </div>
    </section>
@endif
@endsection


