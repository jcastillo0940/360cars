@extends('layouts.portal')

@section('title', 'Admin Portal | 360Cars')
@section('portal-eyebrow', 'Admin portal')
@section('portal-title', 'Opera el marketplace con claridad comercial y tecnica.')
@section('portal-copy', 'Command center para moderacion, cobros, crecimiento y salud operativa del producto.')

@section('header-actions')
    <a href="#moderation" class="button button--solid">Revisar moderacion</a>
    <a href="#payments" class="button button--ghost">Ver pagos</a>
@endsection

@section('sidebar')
    <nav class="portal-nav">
        <a href="#overview" class="is-active">Resumen</a>
        <a href="#ops">Operaciones</a>
        <a href="#payments">Pagos</a>
        <a href="#exchange-rate">Tipo de cambio</a>
        <a href="#public-theme">Tema publico</a>
        <a href="#valuation-ai">Tasador IA</a>
        <a href="#moderation">Moderacion</a>
        <a href="#users">Usuarios</a>
        <a href="#insights">Insights</a>
        <a href="#settings">Planes</a>
        <a href="#features">Extras</a>
    </nav>
    <div class="portal-note">
        <p class="muted-label">Estado global</p>
        <p>{{ $publishedVehicleCount }} publicaciones publicadas · {{ $paidTransactionsCount }} transacciones pagadas.</p>
    </div>
@endsection

@section('content')
<section class="dashboard-grid" id="overview">
    <article class="metric-card reveal"><span>GMV acumulado</span><strong>${{ number_format($gmv, 0) }}</strong><p>Calculado desde transacciones pagadas.</p></article>
    <article class="metric-card reveal reveal--delay"><span>Usuarios nuevos hoy</span><strong>{{ $newUsers }}</strong><p>Sesiones y onboarding del dia actual.</p></article>
    <article class="metric-card reveal reveal--delay-2"><span>Moderacion pendiente</span><strong>{{ $pendingModeration }}</strong><p>Drafts o pausados por revisar.</p></article>
</section>

<section class="dashboard-panel reveal" id="exchange-rate">
    <div class="panel-heading"><div><p class="eyebrow">Moneda</p><h2>Tipo de cambio CRC / USD</h2></div><form method="POST" action="{{ route('admin.exchange-rate.refresh') }}">@csrf<button type="submit" class="button button--ghost">Actualizar tasa</button></form></div>
    <div class="control-strip">
        <article class="control-card"><span class="muted-label">CRC por USD</span><strong>{{ number_format((float) data_get($exchangeQuote, 'usd_to_crc', 0), 2) }}</strong><p>Fuente: {{ data_get($exchangeQuote, 'source', 'N/A') }}{{ data_get($exchangeQuote, 'stale') ? ' · cache anterior' : '' }}</p></article>
        <article class="control-card"><span class="muted-label">USD por CRC</span><strong>{{ number_format((float) data_get($exchangeQuote, 'crc_to_usd', 0), 6) }}</strong><p>Actualizado: {{ data_get($exchangeQuote, 'fetched_at') ? \Illuminate\Support\Carbon::parse(data_get($exchangeQuote, 'fetched_at'))->format('d/m/Y H:i') : 'Sin dato' }}</p></article>
    </div>
</section>

<section class="dashboard-panel reveal" id="public-theme">
    <div class="panel-heading"><div><p class="eyebrow">Frontend</p><h2>Tema publico del home</h2></div></div>
    <div class="control-strip">
        <article class="control-card"><span class="muted-label">Modo actual</span><strong>{{ $publicTheme === 'dark' ? 'Dark mode' : 'Light mode' }}</strong><p>El home publico puede mantenerse claro o pasar a una version oscura con la misma composicion visual.</p></article>
        <article class="control-card"><span class="muted-label">Alcance</span><strong>Home publico</strong><p>Este ajuste cambia la presentacion principal del front sin tocar seller, buyer ni admin.</p></article>
    </div>
    <form method="POST" action="{{ route('admin.public-theme.update') }}" class="portal-form">
        @csrf
        <div class="form-grid">
            <label class="form-field">
                <span>Tema del home</span>
                <select name="public_theme">
                    <option value="light" @selected($publicTheme === 'light')>Light mode</option>
                    <option value="dark" @selected($publicTheme === 'dark')>Dark mode</option>
                </select>
            </label>
        </div>
        <div class="form-actions">
            <button type="submit" class="button button--solid">Guardar tema publico</button>
        </div>
    </form>
</section>

<section class="dashboard-panel reveal" id="valuation-ai">
    <div class="panel-heading"><div><p class="eyebrow">Tasador</p><h2>Motor de valuacion e IA opcional</h2></div></div>
    <div class="control-strip">
        <article class="control-card"><span class="muted-label">Algoritmo base</span><strong>Activo</strong><p>El tasador siempre funciona con comparables locales, depreciacion y reglas del mercado de Costa Rica.</p></article>
        <article class="control-card"><span class="muted-label">API IA</span><strong>{{ $valuationAiConfigured ? 'Configurada' : 'Sin configurar' }}</strong><p>{{ $valuationAiConfigured ? 'Puedes usar una capa narrativa opcional para explicar mejor la evaluacion.' : 'Configura VALUATION_AI_API_KEY y endpoint si quieres enriquecer el resultado.' }}</p></article>
    </div>
    <form method="POST" action="{{ route('admin.valuation-ai.update') }}" class="portal-form">
        @csrf
        <label class="inline-check"><input type="checkbox" name="valuation_ai_enabled" value="1" @checked($valuationAiEnabled) /> <span>Activar resumen IA opcional en nuevas evaluaciones</span></label>
        <div class="form-actions">
            <button type="submit" class="button button--solid">Guardar ajuste del tasador</button>
        </div>
    </form>
</section>
<section class="dashboard-panel dashboard-panel--hero reveal" id="ops">
    <div class="panel-heading"><div><p class="eyebrow">Marketplace health</p><h2>Radiografia del sistema en tiempo real.</h2></div><span class="pill">Admin workflow</span></div>
    <div class="control-strip">
        <article class="control-card"><span class="muted-label">Vehiculos</span><strong>{{ $vehicleCount }}</strong><p>{{ $publishedVehicleCount }} publicados actualmente.</p></article>
        <article class="control-card"><span class="muted-label">Leads</span><strong>{{ $leadCount }}</strong><p>Acumulados en el inventario activo.</p></article>
        <article class="control-card"><span class="muted-label">Subscripciones activas</span><strong>{{ $activeSubscriptions->count() }}</strong><p>Con renovacion y cobro ya trazables.</p></article>
    </div>
</section>

<section class="panel-grid panel-grid--wide" id="payments">
    <article class="dashboard-panel reveal">
        <div class="panel-heading"><div><p class="eyebrow">Billing</p><h2>Pagos recientes</h2></div></div>
        <div class="table-shell">
            <table class="portal-table">
                <thead><tr><th>Orden</th><th>Usuario</th><th>Plan</th><th>Estado</th><th>Monto</th><th>Metodo</th></tr></thead>
                <tbody>
                    @forelse ($latestTransactions as $transaction)
                        <tr>
                            <td><strong>{{ $transaction->external_reference }}</strong><span>{{ $transaction->provider }}</span></td>
                            <td>{{ $transaction->user?->email }}</td>
                            <td>{{ $transaction->plan?->name }}</td>
                            <td><span class="status-badge {{ $transaction->status === 'paid' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $transaction->status }}</span></td>
                            <td>${{ number_format((float) $transaction->amount, 0) }}</td>
                            <td>{{ $transaction->payment_method }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay transacciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
    <article class="dashboard-panel reveal reveal--delay" id="moderation">
        <div class="panel-heading"><div><p class="eyebrow">Moderacion</p><h2>Ultimos listings</h2></div></div>
        <div class="table-shell">
            <table class="portal-table">
                <thead><tr><th>Listing</th><th>Owner</th><th>Estado</th><th>Precio</th><th>Media</th></tr></thead>
                <tbody>
                    @foreach ($latestVehicles as $vehicle)
                        <tr>
                            <td><strong>{{ $vehicle->title }}</strong><span>{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }}</span></td>
                            <td>{{ $vehicle->owner?->email }}</td>
                            <td><span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : ($vehicle->status === 'draft' ? 'status-badge--warn' : '') }}">{{ $vehicle->status }}</span></td>
                            <td><strong>{{ \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote)['primary_formatted'] }}</strong><span>{{ \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote)['secondary_formatted'] }}</span></td>
                            <td>{{ $vehicle->media()->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="panel-grid panel-grid--wide" id="users">
    <article class="dashboard-panel reveal">
        <div class="panel-heading"><div><p class="eyebrow">Usuarios</p><h2>Nuevas cuentas</h2></div></div>
        <div class="table-shell">
            <table class="portal-table">
                <thead><tr><th>Usuario</th><th>Rol</th><th>Verificado</th><th>Alta</th></tr></thead>
                <tbody>
                    @foreach ($latestUsers as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></td>
                            <td>{{ $user->account_type->value }}</td>
                            <td>{{ $user->is_verified ? 'Si' : 'No' }}</td>
                            <td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
    <article class="dashboard-panel reveal reveal--delay" id="insights">
        <div class="panel-heading"><div><p class="eyebrow">Insights</p><h2>Subscripciones activas</h2></div></div>
        <div class="feature-checklist">
            @forelse ($activeSubscriptions as $subscription)
                <div><strong>{{ $subscription->user?->email }}</strong><p>{{ $subscription->plan?->name }} · vence {{ optional($subscription->ends_at)->format('d/m/Y') ?? 'N/A' }}</p></div>
            @empty
                <div><strong>Sin subscripciones</strong><p>No hay subscripciones activas registradas.</p></div>
            @endforelse
        </div>
    </article>
</section>

<section class="dashboard-panel reveal" id="settings">
    <div class="panel-heading"><div><p class="eyebrow">Planes disponibles</p><h2>Estructura comercial activa</h2></div></div>
    <div class="feature-checklist">
        @foreach ($plans as $plan)
            <div><strong>{{ $plan->name }}</strong><p>${{ number_format((float) $plan->price, 0) }} · {{ $plan->max_active_listings ?? 'Ilimitadas' }} publicaciones · {{ $plan->photo_limit ?? 'Ilimitadas' }} fotos.</p></div>
        @endforeach
    </div>
</section>


<section class="panel-grid panel-grid--wide" id="catalog">
    <article class="dashboard-panel reveal">
        <div class="panel-heading"><div><p class="eyebrow">Catalogo vehicular</p><h2>Crear marca o modelo</h2></div><span class="pill">Costa Rica</span></div>
        <div class="control-strip">
            <article class="control-card"><span class="muted-label">Marcas activas</span><strong>{{ $catalogStats['makes_active'] }}</strong><p>{{ $catalogStats['makes_total'] }} marcas registradas en total.</p></article>
            <article class="control-card"><span class="muted-label">Modelos activos</span><strong>{{ $catalogStats['models_active'] }}</strong><p>{{ $catalogStats['models_total'] }} modelos registrados en total.</p></article>
        </div>
        <div class="panel-grid panel-grid--wide" style="margin-top: 1rem;">
            <div class="dashboard-panel dashboard-panel--nested">
                <div class="panel-heading"><div><p class="eyebrow">Nueva marca</p><h2>Agregar marca</h2></div></div>
                <form method="POST" action="{{ route('admin.catalog.makes.store') }}" class="portal-form">
                    @csrf
                    <label class="form-field"><span>Nombre de la marca</span><input type="text" name="name" placeholder="Ej. Changan" required /></label>
                    <div class="form-actions"><button type="submit" class="button button--solid">Guardar marca</button></div>
                </form>
            </div>
            <div class="dashboard-panel dashboard-panel--nested">
                <div class="panel-heading"><div><p class="eyebrow">Nuevo modelo</p><h2>Agregar modelo</h2></div></div>
                <form method="POST" action="{{ route('admin.catalog.models.store') }}" class="portal-form">
                    @csrf
                    <div class="form-grid">
                        <label class="form-field"><span>Marca</span><select name="vehicle_make_id" required>@foreach ($catalogMakes as $make)<option value="{{ $make->id }}">{{ $make->name }}</option>@endforeach</select></label>
                        <label class="form-field"><span>Nombre del modelo</span><input type="text" name="name" placeholder="Ej. Tiggo 7 Pro" required /></label>
                    </div>
                    <div class="form-actions"><button type="submit" class="button button--solid">Guardar modelo</button></div>
                </form>
            </div>
        </div>
    </article>
    <article class="dashboard-panel reveal reveal--delay">
        <div class="panel-heading"><div><p class="eyebrow">Catalogo activo</p><h2>Marcas y modelos administrables</h2></div></div>
        <div class="feature-checklist">
            @forelse ($catalogMakes as $make)
                <div>
                    <div class="support-band support-band--spaced">
                        <div>
                            <strong>{{ $make->name }}</strong>
                            <p>{{ $make->models->where('is_active', true)->count() }} modelos activos de {{ $make->models->count() }}.</p>
                        </div>
                        <form method="POST" action="{{ route('admin.catalog.makes.toggle', $make) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="button button--ghost">{{ $make->is_active ? 'Desactivar marca' : 'Activar marca' }}</button>
                        </form>
                    </div>
                    <div class="media-chip-row">
                        @forelse ($make->models as $model)
                            <span class="media-chip">
                                {{ $model->name }}
                                <form method="POST" action="{{ route('admin.catalog.models.toggle', $model) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="button button--ghost">{{ $model->is_active ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                            </span>
                        @empty
                            <span class="status-badge status-badge--warn">Sin modelos registrados</span>
                        @endforelse
                    </div>
                </div>
            @empty
                <div><strong>Sin catalogo</strong><p>Todavia no se han cargado marcas y modelos.</p></div>
            @endforelse
        </div>
    </article>
</section>
<section class="panel-grid panel-grid--wide" id="features">
    <article class="dashboard-panel reveal">
        <div class="panel-heading"><div><p class="eyebrow">Configuracion del onboarding</p><h2>Crear extra configurable</h2></div><span class="pill">Seller UX</span></div>
        <form method="POST" action="{{ route('admin.feature-options.store') }}" class="portal-form">
            @csrf
            <div class="form-grid">
                <label class="form-field"><span>Nombre del extra</span><input type="text" name="name" placeholder="Ej. Camara 360" required /></label>
                <label class="form-field"><span>Categoria</span><input type="text" name="category" placeholder="seguridad, tecnologia, confort" required /></label>
                <label class="form-field form-field--wide"><span>Descripcion</span><input type="text" name="description" placeholder="Texto corto para el vendedor" /></label>
                <label class="form-field"><span>Orden</span><input type="number" name="sort_order" min="0" max="9999" value="0" /></label>
            </div>
            <div class="form-actions">
                <button type="submit" class="button button--solid">Guardar extra</button>
            </div>
        </form>
    </article>
    <article class="dashboard-panel reveal reveal--delay">
        <div class="panel-heading"><div><p class="eyebrow">Catalogo activo</p><h2>Extras visibles en vende tu auto</h2></div></div>
        <div class="feature-checklist">
            @forelse ($featureOptions as $category => $group)
                <div>
                    <strong>{{ str($category)->replace('-', ' ')->title() }}</strong>
                    <div class="media-chip-row">
                        @foreach ($group as $feature)
                            <span class="media-chip">
                                {{ $feature->name }}
                                <form method="POST" action="{{ route('admin.feature-options.toggle', $feature) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="button button--ghost">{{ $feature->is_active ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                            </span>
                        @endforeach
                    </div>
                </div>
            @empty
                <div><strong>Sin extras</strong><p>Todavia no has configurado extras para el onboarding seller.</p></div>
            @endforelse
        </div>
    </article>
</section>
@endsection


