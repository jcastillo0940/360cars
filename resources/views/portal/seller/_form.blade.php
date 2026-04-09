<div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem; align-items: start;">
    <div style="display: grid; gap: 2rem;">
        <form id="vehicle-form" method="POST" action="{{ $editingVehicle ? route('seller.vehicles.update', $editingVehicle) : route('seller.vehicles.store') }}" enctype="multipart/form-data" class="portal-form">
            @csrf
            @if ($editingVehicle) @method('PUT') @endif

            <input type="hidden" name="currency" value="CRC" />
            <input type="hidden" name="country_code" value="CR" />
            <input type="hidden" name="supports_360" value="{{ old('supports_360', $editingVehicle?->supports_360 ? 1 : 0) }}" />
            <input type="hidden" name="has_video" value="{{ old('has_video', $editingVehicle?->has_video ? 1 : 0) }}" />
            <input type="hidden" name="is_verified_plate" value="{{ old('is_verified_plate', $editingVehicle?->is_verified_plate ? 1 : 0) }}" />
            <input type="hidden" name="latitude" value="{{ old('latitude', $editingVehicle?->latitude) }}" data-map-lat />
            <input type="hidden" name="longitude" value="{{ old('longitude', $editingVehicle?->longitude) }}" data-map-lng />
            <input type="hidden" name="city" value="{{ old('city', $editingVehicle?->city ?? $editingVehicle?->district) }}" data-map-city />
            <input type="hidden" name="state" value="{{ old('state', $editingVehicle?->state ?? $editingVehicle?->province) }}" data-map-state />
            <input type="hidden" name="location_label" value="{{ old('location_label', data_get($editingVehicle?->metadata, 'location_label')) }}" data-map-label />
            <script type="application/json" id="cr-location-tree">@json($locationTree)</script>

            <!-- Identidad -->
            <section class="dashboard-panel">
                <div class="panel-heading"><div><p class="portal-kicker">01</p><h2>Datos Básicos</h2></div></div>
                <div style="display: grid; gap: 1.5rem;">
                    <label class="form-field">
                        <span>Título del anuncio</span>
                        <input type="text" name="title" value="{{ old('title', $editingVehicle?->title) }}" required placeholder="Ej. Toyota Fortuner 2023 Full Extras" />
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 120px; gap: 1rem;">
                        <label class="form-field"><span>Marca</span><select name="vehicle_make_id" data-seller-make-select required><option value="">Marca...</option>@foreach ($makes as $make)<option value="{{ $make->id }}" @selected((int) old('vehicle_make_id', $editingVehicle?->vehicle_make_id) === $make->id)>{{ $make->name }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Modelo</span><select name="vehicle_model_id" data-seller-model-select required><option value="">Modelo...</option>@foreach ($makes as $make) @foreach ($make->models as $model)<option value="{{ $model->id }}" data-make-id="{{ $make->id }}" @selected((int) old('vehicle_model_id', $editingVehicle?->vehicle_model_id) === $model->id)>{{ $model->name }}</option>@endforeach @endforeach</select></label>
                        <label class="form-field"><span>Año</span><input type="number" name="year" min="1950" max="2100" value="{{ old('year', $editingVehicle?->year ?? date('Y')) }}" required /></label>
                    </div>
                </div>
            </section>

            <!-- Precio -->
            <section class="dashboard-panel">
                <div class="panel-heading"><div><p class="portal-kicker">02</p><h2>Precio y Recorrido</h2></div></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <label class="form-field"><span>Precio (CRC)</span><input type="number" name="price" value="{{ old('price', $editingVehicle?->price) }}" required placeholder="Precio en Colones" /></label>
                    <label class="form-field"><span>Kilometraje</span><input type="number" name="mileage" value="{{ old('mileage', $editingVehicle?->mileage) }}" placeholder="Km totales" /></label>
                </div>
                @if($editingVehicle)
                    <div style="margin-top: 1rem; padding: 1rem; background: var(--portal-soft); border-radius: 8px; border: 1px solid var(--portal-border); font-size: 0.9rem;">
                        Ref. USD aproximada: <strong style="color: var(--portal-primary);">{{ $editingVehiclePrice['secondary_formatted'] }}</strong>
                    </div>
                @endif
            </section>

            <!-- Ficha Técnica -->
            <section class="dashboard-panel">
                <div class="panel-heading"><div><p class="portal-kicker">03</p><h2>Ficha Técnica</h2></div></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                    <label class="form-field"><span>Condición</span><select name="condition">@foreach ($vehicleConditions as $value => $label)<option value="{{ $value }}" @selected(old('condition', $editingVehicle?->condition ?? 'used') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="form-field"><span>Combustible</span><select name="fuel_type">@foreach ($vehicleFuelTypes as $value)<option value="{{ $value }}" @selected(old('fuel_type', $editingVehicle?->fuel_type ?? 'Gasolina') === $value)>{{ $value }}</option>@endforeach</select></label>
                    <label class="form-field"><span>Transmisión</span><select name="transmission">@foreach ($vehicleTransmissions as $value)<option value="{{ $value }}" @selected(old('transmission', $editingVehicle?->transmission ?? 'Automática') === $value)>{{ $value }}</option>@endforeach</select></label>
                    <label class="form-field"><span>Carrocería</span><select name="body_type">@foreach ($vehicleBodyTypes as $value)<option value="{{ $value }}" @selected(old('body_type', $editingVehicle?->body_type ?? 'SUV') === $value)>{{ $value }}</option>@endforeach</select></label>
                    <label class="form-field"><span>Tracción</span><select name="drivetrain"><option value="">Selecciona</option>@foreach ($vehicleDrivetrains as $value)<option value="{{ $value }}" @selected(old('drivetrain', $editingVehicle?->drivetrain) === $value)>{{ $value }}</option>@endforeach</select></label>
                    <label class="form-field"><span>Color Exterior</span><input type="text" name="exterior_color" value="{{ old('exterior_color', $editingVehicle?->exterior_color) }}" placeholder="Ej. Gris" /></label>
                </div>
            </section>

            <!-- Ubicación -->
            <section class="dashboard-panel">
                <div class="panel-heading"><div><p class="portal-kicker">04</p><h2>Ubicación</h2></div></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                    <label class="form-field"><span>Provincia</span><select name="province" data-location-province><option value="">Selecciona</option></select></label>
                    <label class="form-field"><span>Cantón</span><select name="canton" data-location-canton disabled><option value="">Primero provincia</option></select></label>
                    <label class="form-field"><span>Distrito</span><select name="district" data-location-district disabled><option value="">Primero cantón</option></select></label>
                </div>
                <label class="form-field" style="margin-top: 1rem;"><span>Referencia manual</span><input type="text" id="map-search" value="{{ old('location_label', data_get($editingVehicle?->metadata, 'location_label')) }}" placeholder="Ej. 100m Oeste de la iglesia..." /></label>
                <div style="margin-top: 1rem; border-radius: 8px; overflow: hidden; height: 200px; border: 1px solid var(--portal-border);">
                    @if ($googleMapsEnabled) <div id="map-canvas" style="height: 100%;"></div> @else <div class="empty-state" style="height: 100%;">Mapa en borrador (Configura API Key)</div> @endif
                </div>
            </section>

            <!-- Extras -->
            <section class="dashboard-panel">
                <div class="panel-heading"><div><p class="portal-kicker">05</p><h2>Características y Descripción</h2></div></div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 2rem;">
                    @foreach($featureOptions as $feature)
                        <label style="display: flex; align-items: center; gap: 0.5rem; background: var(--portal-soft); padding: 0.5rem 0.75rem; border-radius: 6px; cursor: pointer; border: 1px solid var(--portal-border);">
                            <input type="checkbox" name="features[]" value="{{ $feature->slug }}" @checked(collect(old('features', $editingVehicle?->features ?? []))->contains($feature->slug)) />
                            <span style="font-size: 0.8rem; font-weight: 600;">{{ $feature->name }}</span>
                        </label>
                    @endforeach
                </div>
                <label class="form-field">
                    <span>Descripción detallada</span>
                    <textarea rows="6" name="description" required placeholder="Describe el estado real del auto, mantenimientos, etc...">{{ old('description', $editingVehicle?->description) }}</textarea>
                </label>
            </section>

            <!-- Acciones -->
            <div style="position: sticky; bottom: 2rem; background: var(--portal-card); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--portal-primary); box-shadow: var(--portal-shadow-lg); display: flex; justify-content: space-between; align-items: center; z-index: 10;">
                <div>
                    <h4 style="margin: 0; font-size: 1rem;">Listo para guardar</h4>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--portal-muted);">Recuerda revisar que el precio y el teléfono sean correctos.</p>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('seller.listings') }}" class="button button--ghost">Cancelar</a>
                    <button type="submit" class="button button--solid" style="min-width: 180px;">Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Aside Data -->
    @if ($editingVehicle)
        <aside style="display: grid; gap: 1.5rem;">
            <section class="dashboard-panel" style="background: var(--portal-primary-soft); border-color: var(--portal-primary);">
                <div class="panel-heading"><div><p class="portal-kicker" style="color: var(--portal-primary);">Estado</p><h2>Publicación</h2></div></div>
                <div style="margin-top: 1rem;">
                    <strong style="display: block; font-size: 1.25rem;">{{ ucfirst($editingVehicle->status) }}</strong>
                    <p style="font-size: 0.85rem; color: var(--portal-muted);">Plan: {{ ucfirst($editingVehicle->publication_tier) }}</p>
                </div>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--portal-border);">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.5rem;"><span>Vistas</span><strong>{{ number_format($editingVehicle->view_count ?? 0) }}</strong></div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.5rem;"><span>Leads</span><strong>{{ number_format($editingVehicle->lead_count ?? 0) }}</strong></div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem;"><span>Fotos</span><strong>{{ $editingVehicle->media->count() }}</strong></div>
                </div>
            </section>

            <section class="dashboard-panel">
                <div class="panel-heading"><h2>Acciones</h2></div>
                <div style="display: grid; gap: 0.75rem;">
                    <a href="{{ route('seller.media', ['vehicle' => $editingVehicle->id]) }}" class="button button--ghost" style="width: 100%;">Gestionar Fotos</a>
                    @if ($editingVehicle->status !== 'published')
                        <form method="POST" action="{{ route('seller.vehicles.publish', $editingVehicle) }}">@csrf @method('PATCH')<button type="submit" class="button button--solid" style="width: 100%;">Publicar Ahora</button></form>
                    @else
                        <form method="POST" action="{{ route('seller.vehicles.pause', $editingVehicle) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost" style="width: 100%;">Pausar Anuncio</button></form>
                    @endif
                    <a href="{{ route('catalog.show', $editingVehicle->slug) }}" class="button button--ghost" style="width: 100%;" target="_blank">Ver Ficha Pública</a>
                </div>
            </section>
        </aside>
    @endif
</div>
