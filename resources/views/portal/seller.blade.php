@extends('layouts.portal')

@section('title', 'Seller Portal | 360Cars')
@section('portal-eyebrow', 'Seller portal')
@section('portal-title', 'Controla publicaciones, media, leads y plan activo.')
@section('portal-copy', 'Backoffice pensado para publicar rapido, seguir leads, renovar anuncios gratis y convertir mejor con paquetes pagos.')

@section('header-actions')
    <a href="#listing-form" class="button button--solid">Nueva publicacion</a>
    <a href="#billing" class="button button--ghost">Ver plan</a>
@endsection

@section('sidebar')
    <nav class="portal-nav">
        <a href="#overview" class="is-active">Resumen</a>
        <a href="#listing-form">Nuevo anuncio</a>
        <a href="#inventory">Publicaciones</a>
        <a href="#visibility">Visibilidad</a>
        <a href="#media">Media</a>
        <a href="#leads">Leads</a>
        <a href="#billing">Plan y pagos</a>
        <a href="#faq-seller">FAQ</a>
    </nav>
    <div class="portal-note">
        <p class="muted-label">Backoffice seller</p>
        <p>Preparado para auth web, reglas comerciales por paquete y media async real.</p>
    </div>
    <div class="portal-note">
        <p class="muted-label">Capacidad actual</p>
        <p>{{ $currentPlan->name }} Ã‚Â· {{ $currentPlan->photo_limit ?? 'Sin limite' }} fotos Ã‚Â· {{ $capabilities['remaining_active_listings'] ?? 'Ilimitadas' }} publicaciones restantes.</p>
    </div>
</section>
@endsection

@section('content')
<section class="dashboard-grid" id="overview">
    <article class="metric-card reveal"><span>Publicaciones activas</span><strong>{{ $activeListingsCount }}</strong><p>{{ $publishedCount }} publicadas y {{ $pausedCount }} en pausa.</p></article>
    <article class="metric-card reveal reveal--delay"><span>Leads acumulados</span><strong>{{ $leadCount }}</strong><p>Contabilizados desde tus anuncios reales.</p></article>
    <article class="metric-card reveal reveal--delay-2"><span>Renovables gratis</span><strong>{{ $freeRenewableCount }}</strong><p>Anuncios basicos vencidos que puedes volver a posicionar por 30 dias.</p></article>
</section>

<section class="dashboard-panel dashboard-panel--hero reveal">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">Control rapido</p>
            <h2>Todo lo importante del dia en un solo lugar.</h2>
        </div>
        <span class="pill">{{ $currentPlan->name }}</span>
    </div>
    <div class="control-strip">
        <article class="control-card">
            <span class="muted-label">Plan efectivo</span>
            <strong>{{ $currentPlan->name }}</strong>
            <p>{{ $currentPlan->description }}</p>
        </article>
        <article class="control-card">
            <span class="muted-label">Visibilidad</span>
            <strong>{{ $currentPlan->price > 0 ? 'Pago con prioridad' : 'Gratis estandar' }}</strong>
            <p>{{ $currentPlan->price > 0 ? 'Tus anuncios ganan prioridad y pueden entrar en destacados del home si el plan lo permite.' : 'Tus anuncios salen en posicion estandar y a los 30 dias puedes renovarlos para reposicionarlos.' }}</p>
        </article>
        <article class="control-card">
            <span class="muted-label">Borradores</span>
            <strong>{{ $draftCount }}</strong>
            <p>Listos para editar o publicar cuando quieras.</p>
        </article>
    </div>
</section>

@if ($freeRenewableCount > 0)
<section class="dashboard-panel reveal">
    <div class="panel-heading"><div><p class="eyebrow">Renovacion gratis</p><h2>Tienes anuncios basicos listos para volver a posicionarse.</h2></div><span class="pill pill--soft">30 dias mas</span></div>
    <div class="list-stack">
        @foreach ($expiredListings->filter(fn ($vehicle) => $vehicle->publication_tier === 'basic')->take(3) as $vehicle)
            <div class="list-row">
                <div><strong>{{ $vehicle->title }}</strong><p>Vencio {{ optional($vehicle->expires_at)->diffForHumans() }}. Puedes renovarlo gratis y devolverlo al feed.</p></div>
                <form method="POST" action="{{ route('seller.vehicles.refresh-basic', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="button button--solid">Renovar y reposicionar</button></form>
            </div>
        @endforeach
    </div>
</section>
@endif

<section class="panel-grid panel-grid--wide" id="listing-form">
    <article class="dashboard-panel reveal">
        <div class="panel-heading">
            <div><p class="eyebrow">Nuevo anuncio</p><h2>Formulario real de publicacion</h2></div>
        </div>
        <form class="portal-form" method="POST" action="{{ route('seller.vehicles.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <label class="form-field form-field--wide"><span>Titulo del anuncio</span><input type="text" name="title" value="{{ old('title') }}" required /></label>
                <label class="form-field"><span>Marca</span><select name="vehicle_make_id" required><option value="">Selecciona</option>@foreach ($makes as $make)<option value="{{ $make->id }}">{{ $make->name }}</option>@endforeach</select></label>
                <label class="form-field"><span>Modelo</span><select name="vehicle_model_id" required><option value="">Selecciona</option>@foreach ($makes as $make) @foreach ($make->models as $model)<option value="{{ $model->id }}">{{ $make->name }} / {{ $model->name }}</option>@endforeach @endforeach</select></label>
                <label class="form-field"><span>Anio</span><input type="number" name="year" min="1950" max="2100" value="{{ old('year', date('Y')) }}" required /></label>
                <input type="hidden" name="currency" value="CRC" />
                <label class="form-field"><span>Precio en colones (CRC)</span><input type="number" step="1" name="price" value="{{ old('price') }}" required /><small>Se mostrara grande en colones y pequeno en dolares de referencia.</small></label>
                <label class="form-field"><span>Kilometraje</span><input type="number" name="mileage" value="{{ old('mileage') }}" /></label>
                <label class="form-field"><span>Condicion</span><select name="condition"><option value="used">Usado</option><option value="new">Nuevo</option></select></label>
                <label class="form-field"><span>Combustible</span><input type="text" name="fuel_type" value="{{ old('fuel_type', 'Gasolina') }}" required /></label>
                <label class="form-field"><span>Transmision</span><input type="text" name="transmission" value="{{ old('transmission', 'Automatica') }}" required /></label>
                <label class="form-field"><span>Carroceria</span><input type="text" name="body_type" value="{{ old('body_type', 'SUV') }}" required /></label>
                <label class="form-field"><span>Ciudad</span><input type="text" name="city" value="{{ old('city', 'San Jose') }}" /></label>
                <label class="form-field"><span>Tier</span><select name="publication_tier">@foreach ($capabilities['allowed_tiers'] as $tier)<option value="{{ $tier }}">{{ ucfirst($tier) }}</option>@endforeach</select></label>
                <label class="form-field"><span>Estado</span><select name="status"><option value="draft">Borrador</option><option value="published">Publicar ahora</option></select></label>
                <label class="form-field form-field--wide"><span>Features separadas por coma</span><input type="text" name="features_list" value="{{ old('features_list') }}" placeholder="camara, carplay, cuero" /></label>
                <label class="form-field form-field--wide"><span>Categorias lifestyle</span><select name="lifestyle_category_ids[]" multiple size="4">@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>
                <label class="form-field form-field--wide"><span>Descripcion</span><textarea rows="5" name="description" required>{{ old('description') }}</textarea></label>
                <label class="form-field form-field--wide"><span>Imagenes</span><input type="file" name="images[]" multiple accept="image/*" /></label>
            </div>
            <div class="form-actions">
                <label class="inline-check"><input type="checkbox" name="supports_360" value="1" /> <span>Soporta 360</span></label>
                <label class="inline-check"><input type="checkbox" name="has_video" value="1" /> <span>Tiene video</span></label>
                <label class="inline-check"><input type="checkbox" name="is_verified_plate" value="1" /> <span>Placa verificada</span></label>
            </div>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar anuncio</button></div>
        </form>
    </article>

    <article class="dashboard-panel reveal reveal--delay" id="visibility">
        <div class="panel-heading"><div><p class="eyebrow">Capacidades del plan</p><h2>Visibilidad y monetizacion</h2></div></div>
        <div class="feature-checklist">
            <div><strong>Fotos por anuncio</strong><p>{{ $currentPlan->photo_limit ?? 'Ilimitadas' }}</p></div>
            <div><strong>Publicaciones activas</strong><p>{{ $currentPlan->max_active_listings ?? 'Ilimitadas' }}</p></div>
            <div><strong>Video</strong><p>{{ $currentPlan->allows_video ? 'Si' : 'No' }}</p></div>
            <div><strong>360</strong><p>{{ $currentPlan->allows_360 ? 'Si' : 'No' }}</p></div>
            <div><strong>Exposicion</strong><p>{{ $currentPlan->price > 0 ? 'Prioridad superior y opcion de destacados pagos.' : 'Posicion estandar con renovacion manual cada 30 dias.' }}</p></div>
        </div>
        <div class="support-band">
            <span class="muted-label">Suscripcion</span>
            <p>{{ $subscription?->status ?? 'Sin suscripcion' }} Ã‚Â· {{ optional($subscription?->ends_at)->format('d/m/Y') ?? 'Sin vencimiento' }}</p>
        </div>
    </article>
</section>

<section class="dashboard-panel reveal" id="inventory">
    <div class="panel-heading"><div><p class="eyebrow">Inventario</p><h2>CRUD real de publicaciones</h2></div></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Vehiculo</th><th>Estado</th><th>Precio</th><th>Media</th><th>Leads</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse ($vehicles as $vehicle)
                    <tr>
                        <td><strong>{{ $vehicle->title }}</strong><span>{{ $vehicle->make?->name }} Ã‚Â· {{ $vehicle->model?->name }} Ã‚Â· {{ $vehicle->year }}</span></td>
                        <td>
                            <span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : ($vehicle->status === 'draft' ? 'status-badge--warn' : '') }}">{{ $vehicle->status }}</span>
                            @if ($vehicle->expires_at && $vehicle->expires_at->isPast())
                                <span class="status-badge status-badge--warn">vencido</span>
                            @endif
                        </td>
                        <td><strong>{{ \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote)['primary_formatted'] }}</strong><span>{{ \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote)['secondary_formatted'] }}</span></td>
                        <td>{{ $vehicle->media->count() }}</td>
                        <td>{{ $vehicle->lead_count }}</td>
                        <td>
                            <div class="table-actions">
                                @if ($vehicle->status !== 'published')
                                    <form method="POST" action="{{ route('seller.vehicles.publish', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Publicar</button></form>
                                @else
                                    <form method="POST" action="{{ route('seller.vehicles.pause', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Pausar</button></form>
                                @endif
                                @if ($vehicle->publication_tier === 'basic' && $vehicle->expires_at && $vehicle->expires_at->isPast())
                                    <form method="POST" action="{{ route('seller.vehicles.refresh-basic', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Renovar gratis</button></form>
                                @endif
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
</section>

<section class="panel-grid panel-grid--wide" id="media">
    <article class="dashboard-panel reveal">
        <div class="panel-heading"><div><p class="eyebrow">Media pipeline</p><h2>Estado de imagenes</h2></div></div>
        <div class="progress-stack progress-stack--four">
            <div><span>Vehiculos</span><strong>{{ $vehicles->count() }}</strong></div>
            <div><span>Publicadas</span><strong>{{ $publishedCount }}</strong></div>
            <div><span>En cola</span><strong>{{ $processingCount }}</strong></div>
            <div><span>Fotos totales</span><strong>{{ $vehicles->flatMap->media->count() }}</strong></div>
        </div>
        @if ($vehicles->isNotEmpty())
            <div class="list-stack">
                @foreach ($vehicles->take(3) as $vehicle)
                    <div class="list-row list-row--stacked">
                        <div><strong>{{ $vehicle->title }}</strong><p>{{ $vehicle->media->count() }} archivos</p></div>
                        <form method="POST" action="{{ route('seller.vehicles.media.store', $vehicle) }}" enctype="multipart/form-data" class="inline-upload-form">@csrf<input type="file" name="images[]" multiple accept="image/*" required><button type="submit" class="button button--ghost">Subir media</button></form>
                        <div class="media-chip-row">@foreach ($vehicle->media->take(3) as $media)<div class="media-chip"><span>#{{ $media->id }}</span><span>{{ $media->processing_status }}</span>@if (! $media->is_primary)<form method="POST" action="{{ route('seller.vehicles.media.primary', [$vehicle, $media]) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Principal</button></form>@endif<form method="POST" action="{{ route('seller.vehicles.media.destroy', [$vehicle, $media]) }}">@csrf @method('DELETE')<button type="submit" class="text-link">Quitar</button></form></div>@endforeach</div>
                    </div>
                @endforeach
            </div>
        @endif
    </article>
    <article class="dashboard-panel reveal reveal--delay">
        <div class="panel-heading"><div><p class="eyebrow">Planes disponibles</p><h2>Escala cuando lo necesites</h2></div></div>
        <div class="feature-checklist">@foreach ($plans as $plan)<div><strong>{{ $plan->name }}</strong><p>${{ number_format((float) $plan->price, 0) }} Ã‚Â· {{ $plan->description }}</p></div>@endforeach</div>
    </article>
</section>

<section class="dashboard-panel reveal" id="leads">
    <div class="panel-heading"><div><p class="eyebrow">Conversion</p><h2>Leads por anuncio</h2></div></div>
    <div class="kanban-grid">@forelse ($vehicles->sortByDesc('lead_count')->take(3) as $vehicle)<article class="kanban-card"><span class="muted-label">{{ ucfirst($vehicle->status) }}</span><strong>{{ $vehicle->title }}</strong><p>{{ $vehicle->lead_count }} leads acumulados.</p></article>@empty<article class="kanban-card"><strong>Sin leads todavia</strong><p>Publica tu primer anuncio para empezar a mover conversion.</p></article>@endforelse</div>
</section>

<section class="dashboard-panel reveal" id="billing">
    <div class="panel-heading"><div><p class="eyebrow">Billing center</p><h2>Planes, checkout y transacciones</h2></div></div>
    <div class="billing-summary">
        <article><span>Plan actual</span><strong>{{ $currentPlan->name }}</strong></article>
        <article><span>Estado</span><strong>{{ $subscription?->status ?? 'N/A' }}</strong></article>
        <article><span>Vence</span><strong>{{ optional($subscription?->ends_at)->format('d/m/Y') ?? 'N/A' }}</strong></article>
        <article><span>Ultimos pagos</span><strong>{{ $transactions->where('status', 'paid')->count() }}</strong></article>
    </div>
    <div class="panel-grid panel-grid--wide panel-grid--billing">
        <article class="dashboard-panel dashboard-panel--nested">
            <div class="panel-heading"><div><p class="eyebrow">Activar o cambiar plan</p><h2>Flujo comercial</h2></div></div>
            <div class="feature-checklist">
                @foreach ($plans as $plan)
                    <div class="billing-plan-card">
                        <div>
                            <strong>{{ $plan->name }}</strong>
                            <p>${{ number_format((float) $plan->price, 0) }} Ã‚Â· {{ $plan->description }}</p>
                        </div>
                        <div class="form-actions">
                            <form method="POST" action="{{ route('seller.billing.subscribe') }}">@csrf<input type="hidden" name="plan_slug" value="{{ $plan->slug }}"><button type="submit" class="button button--ghost">Activar sandbox</button></form>
                            <form method="POST" action="{{ route('seller.billing.paypal.create-order') }}">@csrf<input type="hidden" name="plan_slug" value="{{ $plan->slug }}"><button type="submit" class="button button--solid" {{ $paypalConfigured ? '' : 'disabled' }}>Pagar con PayPal</button></form>
                        </div>
                    </div>
                @endforeach
            </div>
            @unless ($paypalConfigured)
                <div class="support-band"><span class="muted-label">PayPal no configurado</span><p>Define `PAYPAL_CLIENT_ID` y `PAYPAL_CLIENT_SECRET` para habilitar checkout real.</p></div>
            @endunless
        </article>
        <article class="dashboard-panel dashboard-panel--nested">
            <div class="panel-heading"><div><p class="eyebrow">Transacciones</p><h2>Historial reciente</h2></div></div>
            <div class="table-shell">
                <table class="portal-table">
                    <thead><tr><th>Referencia</th><th>Plan</th><th>Estado</th><th>Monto</th><th>Metodo</th></tr></thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td><strong>{{ $transaction->external_reference }}</strong><span>{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</span></td>
                                <td>{{ $transaction->plan?->name }}</td>
                                <td><span class="status-badge {{ $transaction->status === 'paid' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $transaction->status }}</span></td>
                                <td>${{ number_format((float) $transaction->amount, 0) }}</td>
                                <td>{{ $transaction->payment_method }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Todavia no hay transacciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</section>

<section class="dashboard-panel reveal" id="faq-seller">
    <div class="panel-heading"><div><p class="eyebrow">Ayuda interna</p><h2>FAQ operativo del seller</h2></div></div>
    <div class="accordion-stack">
        <article class="accordion" data-accordion><button type="button" class="accordion__trigger" aria-expanded="false" data-accordion-trigger><span>Que pasa con el plan gratis a los 30 dias?</span><span class="accordion__icon">+</span></button><div class="accordion__panel" hidden data-accordion-panel><p>Tu anuncio basico mantiene visibilidad estandar por 30 dias. Cuando vence puedes renovarlo desde este panel para volver a posicionarlo gratis por otros 30 dias.</p></div></article>
        <article class="accordion" data-accordion><button type="button" class="accordion__trigger" aria-expanded="false" data-accordion-trigger><span>Como entro a destacados del home?</span><span class="accordion__icon">+</span></button><div class="accordion__panel" hidden data-accordion-panel><p>Los anuncios de cuentas pagas con prioridad comercial alimentan la seccion de destacados del home y obtienen mejor exposicion general.</p></div></article>
    </div>
</section>
@endsection

