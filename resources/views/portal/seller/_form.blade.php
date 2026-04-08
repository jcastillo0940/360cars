<div class="seller-editor-layout">
    <div class="seller-editor-main">
        <section class="seller-editor-card">
            <div class="seller-editor-card__header">
                <div>
                    <p class="portal-kicker">{{ $editingVehicle ? 'Edición avanzada' : 'Formulario real' }}</p>
                    <h2>{{ $editingVehicle ? 'Editar publicación' : 'Crear nuevo anuncio' }}</h2>
                    <p>Trabaja el anuncio como una ficha comercial completa: identidad, precio, especificaciones, contacto, características y galería guiada por tipo de foto.</p>
                </div>
                @if ($editingVehicle)
                    <a href="{{ route('seller.listings') }}" class="button button--ghost">Volver a publicaciones</a>
                @endif
            </div>

            <form class="portal-form" method="POST" action="{{ $editingVehicle ? route('seller.vehicles.update', $editingVehicle) : route('seller.vehicles.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($editingVehicle)
                    @method('PUT')
                @endif

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

                <div class="seller-editor-section">
                    <div class="seller-editor-section__intro">
                        <span class="portal-kicker">Identidad del anuncio</span>
                        <h3>Título, marca y modelo</h3>
                        <p>Esta parte define cómo se encuentra tu auto en el portal y en los filtros del comprador.</p>
                    </div>
                    <div class="form-grid seller-form-grid seller-form-grid--three">
                        <label class="form-field form-field--wide"><span>Título del anuncio</span><input type="text" name="title" value="{{ old('title', $editingVehicle?->title) }}" required /></label>
                        <label class="form-field"><span>Marca</span><select name="vehicle_make_id" data-seller-make-select required><option value="">Selecciona</option>@foreach ($makes as $make)<option value="{{ $make->id }}" @selected((int) old('vehicle_make_id', $editingVehicle?->vehicle_make_id) === $make->id)>{{ $make->name }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Modelo</span><select name="vehicle_model_id" data-seller-model-select required><option value="">Selecciona</option>@foreach ($makes as $make) @foreach ($make->models as $model)<option value="{{ $model->id }}" data-make-id="{{ $make->id }}" @selected((int) old('vehicle_model_id', $editingVehicle?->vehicle_model_id) === $model->id)>{{ $model->name }}</option>@endforeach @endforeach</select></label>
                        <label class="form-field"><span>Año</span><input type="number" name="year" min="1950" max="2100" value="{{ old('year', $editingVehicle?->year ?? date('Y')) }}" required /></label>
                    </div>
                </div>

                <div class="seller-editor-section">
                    <div class="seller-editor-section__intro">
                        <span class="portal-kicker">Precio y mercado</span>
                        <h3>Valor oficial del anuncio</h3>
                        <p>El precio se guarda en colones. Debajo se muestra la referencia secundaria en dólares.</p>
                    </div>
                    <div class="form-grid seller-form-grid seller-form-grid--three">
                        <label class="form-field"><span>Precio en CRC</span><input type="number" step="1" name="price" value="{{ old('price', $editingVehicle?->price) }}" required /><small>Usa el precio real que quieres mostrar al comprador.</small></label>
                        <label class="form-field"><span>Kilometraje</span><input type="number" name="mileage" value="{{ old('mileage', $editingVehicle?->mileage) }}" /><small>Dato clave para tasación y confianza.</small></label>
                        <div class="seller-data-card">
                            <span class="seller-data-card__label">Vista previa actual</span>
                            <strong>{{ $editingVehiclePrice['primary_formatted'] ?? 'CRC pendiente' }}</strong>
                            <p>{{ $editingVehiclePrice['secondary_formatted'] ?? 'La referencia USD aparecerá cuando guardes el precio.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="seller-editor-section">
                    <div class="seller-editor-section__intro">
                        <span class="portal-kicker">Especificaciones</span>
                        <h3>Ficha técnica</h3>
                        <p>Completa los datos que realmente ayudan a filtrar y comparar tu auto dentro del marketplace.</p>
                    </div>
                    <div class="form-grid seller-form-grid seller-form-grid--three">
                        <label class="form-field"><span>Condición</span><select name="condition">@foreach ($vehicleConditions as $value => $label)<option value="{{ $value }}" @selected(old('condition', $editingVehicle?->condition ?? 'used') === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Combustible</span><select name="fuel_type">@foreach ($vehicleFuelTypes as $value)<option value="{{ $value }}" @selected(old('fuel_type', $editingVehicle?->fuel_type ?? 'Gasolina') === $value)>{{ $value }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Transmisión</span><select name="transmission">@foreach ($vehicleTransmissions as $value)<option value="{{ $value }}" @selected(old('transmission', $editingVehicle?->transmission ?? 'Automática') === $value)>{{ $value }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Carrocería</span><select name="body_type">@foreach ($vehicleBodyTypes as $value)<option value="{{ $value }}" @selected(old('body_type', $editingVehicle?->body_type ?? 'SUV') === $value)>{{ $value }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Tracción</span><select name="drivetrain"><option value="">Selecciona</option>@foreach ($vehicleDrivetrains as $value)<option value="{{ $value }}" @selected(old('drivetrain', $editingVehicle?->drivetrain) === $value)>{{ $value }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Color exterior</span><input type="text" name="exterior_color" value="{{ old('exterior_color', $editingVehicle?->exterior_color) }}" placeholder="Ej. Negro, Blanco, Gris" /></label>
                    </div>
                </div>

                <div class="seller-editor-section">
                    <div class="seller-editor-section__intro">
                        <span class="portal-kicker">Ubicación</span>
                        <h3>Provincia, cantón y distrito</h3>
                        <p>Ubica el auto correctamente para mejorar la confianza y la calidad del contacto.</p>
                    </div>
                    <div class="form-grid seller-form-grid seller-form-grid--three">
                        <label class="form-field"><span>Provincia</span><select name="province" data-location-province><option value="">Selecciona una provincia</option></select></label>
                        <label class="form-field"><span>Cantón</span><select name="canton" data-location-canton disabled><option value="">Selecciona primero una provincia</option></select></label>
                        <label class="form-field"><span>Distrito</span><select name="district" data-location-district disabled><option value="">Selecciona primero un cantón</option></select></label>
                        <label class="form-field form-field--wide"><span>Referencia</span><input type="text" id="map-search" value="{{ old('location_label', data_get($editingVehicle?->metadata, 'location_label')) }}" placeholder="Ej. 300 m norte del parque central" /></label>
                        <div class="form-field form-field--wide">
                            <span>Mapa</span>
                            @if ($googleMapsEnabled)
                                <div id="map-canvas" class="map-canvas"></div>
                            @else
                                <div class="map-canvas__fallback">Configura `GOOGLE_MAPS_API_KEY` para activar el mapa. Mientras tanto puedes seleccionar provincia, cantón, distrito y escribir una referencia manual.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="seller-editor-section">
                    <div class="seller-editor-section__intro">
                        <span class="portal-kicker">Contacto del anuncio</span>
                        <h3>Datos para que te contacten</h3>
                        <p>Aquí defines el WhatsApp o teléfono y el correo que quieres usar para este auto en particular.</p>
                    </div>
                    <div class="form-grid seller-form-grid seller-form-grid--two">
                        <label class="form-field"><span>WhatsApp o teléfono de contacto</span><input type="text" name="contact_phone" value="{{ old('contact_phone', data_get($editingVehicle?->metadata, 'contact_phone', auth()->user()?->whatsapp_phone ?: auth()->user()?->phone)) }}" placeholder="Ej. 8888-8888" /><small>Este número será el que use el botón de WhatsApp del anuncio.</small></label>
                        <label class="form-field"><span>Correo de contacto</span><input type="email" name="contact_email" value="{{ old('contact_email', data_get($editingVehicle?->metadata, 'contact_email', auth()->user()?->email)) }}" placeholder="ventas@tudominio.com" /><small>Puedes usar un correo distinto al de tu cuenta si quieres separar consultas.</small></label>
                    </div>
                </div>

                <div class="seller-editor-section">
                    <div class="seller-editor-section__intro">
                        <span class="portal-kicker">Visibilidad</span>
                        <h3>Plan y publicación</h3>
                        <p>Define si el anuncio se queda en borrador o sale publicado según la capacidad de tu plan.</p>
                    </div>
                    <div class="form-grid seller-form-grid seller-form-grid--three">
                        <label class="form-field"><span>Plan del anuncio</span><select name="publication_tier">@foreach ($capabilities['allowed_tiers'] as $tier)<option value="{{ $tier }}" @selected(old('publication_tier', $editingVehicle?->publication_tier ?? 'basic') === $tier)>{{ ucfirst($tier) }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Estado</span><select name="status"><option value="draft" @selected(old('status', $editingVehicle?->status ?? 'draft') === 'draft')>Borrador</option><option value="published" @selected(old('status', $editingVehicle?->status) === 'published')>Publicar ahora</option><option value="paused" @selected(old('status', $editingVehicle?->status) === 'paused')>Pausado</option><option value="sold" @selected(old('status', $editingVehicle?->status) === 'sold')>Vendido</option></select></label>
                        <div class="seller-data-card">
                            <span class="seller-data-card__label">Capacidad actual</span>
                            <strong>{{ $capabilities['max_photos'] ?? 'Ilimitadas' }} fotos</strong>
                            <p>{{ ($capabilities['can_upload_video'] ?? false) ? 'Tu plan permite video.' : 'Tu plan actual no incluye video.' }} {{ ($capabilities['can_use_360'] ?? false) ? 'También incluye 360.' : 'Sin fotos 360 por ahora.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="seller-editor-section">
                    <div class="seller-editor-section__intro">
                        <span class="portal-kicker">Caracter?sticas y descripci?n</span>
                        <h3>Lo que más importa para vender</h3>
                        <p>Marca lo que realmente tiene tu auto y explica de forma breve por qué vale la pena contactarte.</p>
                    </div>
                    <div class="form-grid seller-form-grid seller-form-grid--two">
                        <div class="form-field form-field--wide">
                            <span>Caracter?sticas</span>
                            <div class="seller-feature-checklist">
                                @foreach($featureOptions as $feature)
                                    <label class="inline-check"><input type="checkbox" name="features[]" value="{{ $feature->slug }}" @checked(collect(old('features', $editingVehicle?->features ?? []))->contains($feature->slug)) /> <span>{{ $feature->name }}</span></label>
                                @endforeach
                            </div>
                        </div>
                        <label class="form-field form-field--wide"><span>Categorías lifestyle</span><select name="lifestyle_category_ids[]" multiple size="4">@foreach($categories as $category)<option value="{{ $category->id }}" @selected(collect(old('lifestyle_category_ids', $editingVehicle?->lifestyleCategories?->pluck('id')->all() ?? []))->contains($category->id))>{{ $category->name }}</option>@endforeach</select></label>
                        <label class="form-field form-field--wide"><span>Descripción</span><textarea rows="6" name="description" required>{{ old('description', $editingVehicle?->description) }}</textarea></label>
                    </div>
                </div>

                <div class="seller-editor-section">
                    <div class="seller-editor-section__intro">
                        <span class="portal-kicker">Galer?a guiada</span>
                        <h3>Fotos obligatorias y extras opcionales</h3>
                        <p>Sube primero las fotos base del anuncio. Luego, si quieres, agrega extras para completar mejor la galería.</p>
                    </div>
                    <div class="seller-photo-slot-grid">
                        @foreach($photoSlots as $slot => $label)
                            @php($slotMedia = $existingMediaBySlot->get($slot, collect())->first())
                            <article class="seller-photo-slot-card">
                                <div class="seller-photo-slot-card__header">
                                    <div>
                                        <strong>{{ $label }}</strong>
                                        <p>Foto guiada recomendada para una publicación fuerte.</p>
                                    </div>
                                    @if($slotMedia)
                                        <span class="status-badge {{ $slotMedia->is_primary ? 'status-badge--success' : '' }}">{{ $slotMedia->is_primary ? 'Principal' : 'Cargada' }}</span>
                                    @endif
                                </div>
                                @if($slotMedia && $slotMedia->path)
                                    <div class="seller-photo-inline-preview" style="background-image:url('{{ asset('storage/'.$slotMedia->path) }}')"></div>
                                    <div class="table-actions">
                                        @if(! $slotMedia->is_primary)
                                            <form method="POST" action="{{ route('seller.vehicles.media.primary', [$editingVehicle, $slotMedia]) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Usar como principal</button></form>
                                        @endif
                                        <form method="POST" action="{{ route('seller.vehicles.media.destroy', [$editingVehicle, $slotMedia]) }}">@csrf @method('DELETE')<button type="submit" class="text-link">Quitar</button></form>
                                    </div>
                                @endif
                                <label class="form-field"><span>{{ $editingVehicle ? 'Reemplazar foto' : 'Subir foto' }}</span><input type="file" name="required_images[{{ $slot }}]" accept="image/*" /></label>
                            </article>
                        @endforeach
                    </div>
                    <label class="form-field form-field--wide"><span>Fotos opcionales</span><input type="file" name="optional_images[]" multiple accept="image/*" /><small>Puedes agregar fotos extra del tablero, aros, ba?l o detalles especiales.</small></label>
                    @if($editingVehicle && $existingMediaBySlot->get('extra', collect())->isNotEmpty())
                        <div class="seller-extra-media-grid">
                            @foreach($existingMediaBySlot->get('extra', collect()) as $extraMedia)
                                <article class="seller-photo-slot-card">
                                    @if($extraMedia->path)
                                        <div class="seller-photo-inline-preview" style="background-image:url('{{ asset('storage/'.$extraMedia->path) }}')"></div>
                                    @endif
                                    <div class="table-actions">
                                        @if(! $extraMedia->is_primary)
                                            <form method="POST" action="{{ route('seller.vehicles.media.primary', [$editingVehicle, $extraMedia]) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Principal</button></form>
                                        @endif
                                        <form method="POST" action="{{ route('seller.vehicles.media.destroy', [$editingVehicle, $extraMedia]) }}">@csrf @method('DELETE')<button type="submit" class="text-link">Quitar</button></form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button--solid">{{ $editingVehicle ? 'Guardar cambios' : 'Guardar anuncio' }}</button>
                    @if ($editingVehicle)
                        <a href="{{ route('catalog.show', $editingVehicle->slug) }}" class="button button--ghost">Ver anuncio</a>
                    @endif
                </div>
            </form>
        </section>
    </div>

    @if ($editingVehicle)
        <aside class="seller-editor-side">
            <section class="seller-data-card seller-data-card--stacked">
                <span class="seller-data-card__label">Estado actual</span>
                <strong>{{ ucfirst($editingVehicle->status) }}</strong>
                <p>{{ $editingVehicle->make?->name }} · {{ $editingVehicle->model?->name }} · {{ $editingVehicle->year }}</p>
                <div class="seller-inline-badges">
                    <span class="status-badge">{{ ucfirst($editingVehicle->publication_tier) }}</span>
                    @if ($editingVehicle->is_featured)
                        <span class="status-badge status-badge--success">Destacado</span>
                    @endif
                </div>
            </section>

            <section class="seller-data-card seller-data-card--stacked">
                <span class="seller-data-card__label">Precio visible</span>
                <strong>{{ $editingVehiclePrice['primary_formatted'] }}</strong>
                <p>{{ $editingVehiclePrice['secondary_formatted'] }}</p>
            </section>

            <section class="seller-data-card seller-data-card--stacked">
                <span class="seller-data-card__label">Contacto del anuncio</span>
                <strong>{{ data_get($editingVehicle?->metadata, 'contact_phone', auth()->user()?->whatsapp_phone ?: auth()->user()?->phone) ?: 'Sin teléfono' }}</strong>
                <p>{{ data_get($editingVehicle?->metadata, 'contact_email', auth()->user()?->email) ?: 'Sin correo' }}</p>
            </section>

            <section class="seller-data-card seller-data-card--stacked">
                <span class="seller-data-card__label">Señales del anuncio</span>
                <ul class="seller-side-list">
                    <li>{{ $editingVehicle->media->count() }} archivos en la galería</li>
                    <li>{{ number_format($editingVehicle->view_count ?? 0) }} vistas acumuladas</li>
                    <li>{{ number_format($editingVehicle->lead_count ?? 0) }} contactos registrados</li>
                    <li>{{ $editingVehicle->city ?: 'Ubicación pendiente' }}</li>
                </ul>
            </section>
        </aside>
    @endif
</div>

