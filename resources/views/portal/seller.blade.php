@extends('layouts.portal')

@section('title', 'Seller Portal | Movikaa')
@section('portal-eyebrow', 'Panel Vendedor')
@section('portal-title', 'Centro de Ventas')

@section('header-actions')
    <button type="button" class="button button--solid" data-tab-trigger="listing-form">Publicar Auto</button>
@endsection



@section('content')
<div data-tabs>
    {{-- TAB: OVERVIEW --}}
    <div data-tab-panel="overview" class="reveal">
        <section class="dashboard-grid">
            <article class="metric-card">
                <span>Viendo ahora</span>
                <strong>{{ $activeListingsCount }}</strong>
                <p>{{ $publishedCount }} activos, {{ $pausedCount }} pausados.</p>
            </article>
            <article class="metric-card">
                <span>Interesados</span>
                <strong>{{ $leadCount }}</strong>
                <p>Consultas acumuladas.</p>
            </article>
            <article class="metric-card">
                <span>Renovaciones</span>
                <strong>{{ $freeRenewableCount }}</strong>
                <p>Anuncios para reposicionar.</p>
            </article>
        </section>

        @if ($freeRenewableCount > 0)
        <section class="dashboard-panel mt-4">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Acción Requerida</p>
                    <h2>Renovaciones Gratuitas Gastadas</h2>
                </div>
            </div>
            <div class="list-stack">
                @foreach ($expiredListings->filter(fn ($vehicle) => $vehicle->publication_tier === 'basic')->take(3) as $vehicle)
                    <div class="list-row">
                        <div>
                            <strong>{{ $vehicle->title }}</strong>
                            <p>Venció {{ optional($vehicle->expires_at)->diffForHumans() }}.</p>
                        </div>
                        <form method="POST" action="{{ route('seller.vehicles.refresh-basic', $vehicle) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="button button--solid">Reposicionar</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>

    {{-- TAB: LISTING FORM --}}
    <div data-tab-panel="listing-form" class="reveal" hidden>
        <article class="dashboard-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Formulario</p>
                    <h2>Detalles del Vehículo</h2>
                </div>
            </div>
            <form class="portal-form" method="POST" action="{{ route('seller.vehicles.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="form-field form-field--wide">
                        <label>Título Descriptivo</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Ej. Toyota Corolla 2022 Full Extras" required />
                    </div>
                    <div class="form-field">
                        <label>Marca</label>
                        <select name="vehicle_make_id" required>
                            <option value="">Selecciona Marca</option>
                            @foreach ($makes as $make)
                                <option value="{{ $make->id }}">{{ $make->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Modelo</label>
                        <select name="vehicle_model_id" required>
                            <option value="">Selecciona Modelo</option>
                            @foreach ($makes as $make)
                                @foreach ($make->models as $model)
                                    <option value="{{ $model->id }}">{{ $make->name }} / {{ $model->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Año</label>
                        <input type="number" name="year" min="1950" max="2100" value="{{ old('year', date('Y')) }}" required />
                    </div>
                    <div class="form-field">
                        <label>Precio (CRC)</label>
                        <input type="number" name="price" value="{{ old('price') }}" required />
                        <input type="hidden" name="currency" value="CRC" />
                    </div>
                    <div class="form-field">
                        <label>Kilometraje</label>
                        <input type="number" name="mileage" value="{{ old('mileage') }}" />
                    </div>
                    <div class="form-field">
                        <label>Condición</label>
                        <select name="condition">
                            <option value="used">Usado</option>
                            <option value="new">Nuevo</option>
                        </select>
                    </div>
                    <div class="form-field form-field--wide">
                         <label>Descripción y Extras</label>
                         <textarea rows="4" name="description" placeholder="Cuenta la historia del auto..." required>{{ old('description') }}</textarea>
                    </div>
                    <div class="form-field form-field--wide">
                        <label>Galería (Múltiples fotos)</label>
                        <input type="file" name="images[]" multiple accept="image/*" />
                        <small>Puedes subir hasta {{ $currentPlan->photo_limit ?? '10' }} fotos.</small>
                    </div>
                </div>
                <div class="form-actions mt-4">
                    <button type="submit" name="status" value="published" class="button button--solid">Publicar Ahora</button>
                    <button type="submit" name="status" value="draft" class="button button--ghost">Guardar Borrador</button>
                </div>
            </form>
        </article>
    </div>

    {{-- TAB: INVENTORY --}}
    <div data-tab-panel="inventory" class="reveal" hidden>
        <article class="dashboard-panel">
            <div class="panel-heading"><h2>Mis Publicaciones</h2></div>
            <div class="table-shell">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Contactos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehicles as $vehicle)
                            <tr>
                                <td><strong>{{ $vehicle->title }}</strong><span>{{ $vehicle->make?->name }} - {{ $vehicle->year }}</span></td>
                                <td>
                                    <span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : 'status-badge--warn' }}">
                                        {{ $vehicle->status }}
                                    </span>
                                </td>
                                <td><strong>{{ number_format($vehicle->price) }} CRC</strong></td>
                                <td>{{ $vehicle->lead_count }} leads</td>
                                <td>
                                    <form method="POST" action="{{ route('seller.vehicles.destroy', $vehicle) }}" onsubmit="return confirm('¿Borrar?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-link">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">No tienes vehículos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    {{-- TAB: BILLING --}}
    <div data-tab-panel="billing" class="reveal" hidden>
        <section class="dashboard-grid">
            <article class="metric-card">
                <span>Plan Actual</span>
                <strong>{{ $currentPlan->name }}</strong>
                <p>Vence: {{ optional($subscription?->ends_at)->format('d/m/Y') ?? 'N/A' }}</p>
            </article>
            <article class="metric-card">
                <span>Inversión</span>
                <strong>${{ number_format($transactions->where('status', 'paid')->sum('amount'), 2) }}</strong>
                <p>Total en suscripciones.</p>
            </article>
        </section>

        <section class="dashboard-panel mt-4">
            <div class="panel-heading"><h2>Planes Disponibles</h2></div>
            <div class="dashboard-grid">
                @foreach ($plans as $plan)
                    <div class="control-card {{ $currentPlan->id === $plan->id ? 'border-primary' : '' }}">
                        <p class="muted-label">Paquete</p>
                        <strong>{{ $plan->name }}</strong>
                        <p>${{ number_format($plan->price, 0) }}</p>
                        <form method="POST" action="{{ (config('app.enable_payments') ? route('seller.billing.subscribe') : route('seller.dashboard')) }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                            <button type="submit" class="button {{ $currentPlan->id === $plan->id ? 'button--ghost' : 'button--solid' }}" {{ $currentPlan->id === $plan->id ? 'disabled' : '' }}>
                                {{ $currentPlan->id === $plan->id ? 'Actual' : 'Seleccionar' }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- TAB: SUPPORT --}}
    <div data-tab-panel="support" class="reveal" hidden>
        <article class="dashboard-panel">
            <div class="panel-heading"><h2>Preguntas Frecuentes</h2></div>
            <div class="accordion-stack">
                <div class="portal-note">
                    <strong>¿Cómo mejoro la visibilidad?</strong>
                    <p>Los planes pagos te permiten destacar tus anuncios en la página principal y aparecer primero en las búsquedas.</p>
                </div>
                <div class="portal-note">
                    <strong>¿Qué pasa si mi anuncio vence?</strong>
                    <p>Si es un anuncio básico, puedes renovarlo gratis cada 30 días para volver a subirlo en el feed.</p>
                </div>
            </div>
        </article>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const triggers = document.querySelectorAll('[data-tab-trigger]');
        const panels = document.querySelectorAll('[data-tab-panel]');
        
        triggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const target = trigger.getAttribute('data-tab-trigger');
                
                document.querySelectorAll('.portal-nav a').forEach(a => a.classList.remove('is-active'));
                trigger.closest('a')?.classList.add('is-active');
                
                panels.forEach(panel => {
                    panel.hidden = panel.getAttribute('data-tab-panel') !== target;
                });
                
                window.location.hash = target;
            });
        });

        const hash = window.location.hash.replace('#', '');
        if (hash) {
            const initialTrigger = document.querySelector(`[data-tab-trigger="${hash}"]`);
            if (initialTrigger) initialTrigger.click();
        }
    });
</script>
@endsection





