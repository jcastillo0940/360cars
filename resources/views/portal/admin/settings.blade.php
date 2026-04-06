@extends('layouts.portal')

@section('title', 'Ajustes admin | Movikaa')
@section('portal-eyebrow', 'Admin ajustes')
@section('portal-title', 'Configuracion del producto')
@section('portal-copy', 'Tema publico, metodos de pago, tasador, moneda y extras configurables desde vistas claras y operativas.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Overview</a>
    <a href="{{ route('admin.catalog') }}">Catalogo</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}" class="is-active">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="panel-grid panel-grid--admin-overview" id="exchange-rate">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Moneda</p><h2>Tipo de cambio CRC / USD</h2></div><form method="POST" action="{{ route('admin.exchange-rate.refresh') }}">@csrf<button type="submit" class="button button--solid">Actualizar tasa</button></form></div>
        <div class="dashboard-grid dashboard-grid--two-up compact-grid">
            <article class="metric-card"><span>CRC por USD</span><strong>{{ number_format((float) data_get($exchangeQuote, 'usd_to_crc', 0), 2) }}</strong><p>Fuente {{ data_get($exchangeQuote, 'source', 'N/A') }}</p></article>
            <article class="metric-card"><span>USD por CRC</span><strong>{{ number_format((float) data_get($exchangeQuote, 'crc_to_usd', 0), 6) }}</strong><p>{{ data_get($exchangeQuote, 'fetched_at') ? \Illuminate\Support\Carbon::parse(data_get($exchangeQuote, 'fetched_at'))->format('d/m/Y H:i') : 'Sin dato' }}</p></article>
        </div>
    </article>

    <article class="dashboard-panel" id="public-theme">
        <div class="panel-heading"><div><p class="portal-kicker">Frontend</p><h2>Tema publico</h2></div></div>
        <form method="POST" action="{{ route('admin.public-theme.update') }}" class="portal-form">
            @csrf
            <label class="form-field"><span>Tema del frontend</span><select name="public_theme"><option value="light" @selected($publicTheme === 'light')>Light mode</option><option value="dark" @selected($publicTheme === 'dark')>Dark mode</option></select></label>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar tema</button></div>
        </form>
    </article>
</section>

<section class="panel-grid panel-grid--admin-overview" id="integrations">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">APIs e integraciones</p><h2>Claves y conexiones del sistema</h2></div></div>
        <form method="POST" action="{{ route('admin.integrations.update') }}" class="portal-form">
            @csrf
            <div class="form-grid">
                <label class="form-field form-field--wide"><span>Google Maps API Key</span><input type="text" name="google_maps_key" value="{{ $integrations['google_maps_key'] ?? '' }}" placeholder="AIza..." /><small>Activa el mapa interactivo en vende-tu-auto. Si esta vacio, el formulario usa modo manual.</small></label>
                <label class="form-field"><span>PayPal modo</span><select name="paypal_mode"><option value="sandbox" @selected(($integrations['paypal_mode'] ?? 'sandbox') === 'sandbox')>Sandbox</option><option value="live" @selected(($integrations['paypal_mode'] ?? '') === 'live')>Live</option></select></label>
                <label class="form-field"><span>PayPal Client ID</span><input type="text" name="paypal_client_id" value="{{ $integrations['paypal_client_id'] ?? '' }}" /></label>
                <label class="form-field"><span>PayPal Client Secret</span><input type="text" name="paypal_client_secret" value="{{ $integrations['paypal_client_secret'] ?? '' }}" /></label>
                <label class="form-field"><span>PayPal Webhook ID</span><input type="text" name="paypal_webhook_id" value="{{ $integrations['paypal_webhook_id'] ?? '' }}" /></label>
                <label class="inline-check"><input type="checkbox" name="tilopay_enabled" value="1" @checked($integrations['tilopay_enabled'] ?? false) /> <span>Tilopay habilitado</span></label>
                <label class="form-field form-field--wide"><span>Tilopay API Key</span><input type="text" name="tilopay_api_key" value="{{ $integrations['tilopay_api_key'] ?? '' }}" /></label>
            </div>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar integraciones</button></div>
        </form>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Estado</p><h2>Lectura rapida</h2></div></div>
        <div class="dashboard-grid dashboard-grid--two-up compact-grid">
            <article class="metric-card"><span>Google Maps</span><strong>{{ filled($integrations['google_maps_key'] ?? '') ? 'Activo' : 'Manual' }}</strong><p>{{ filled($integrations['google_maps_key'] ?? '') ? 'El onboarding puede usar mapa interactivo.' : 'Se usara ubicacion manual en vende-tu-auto.' }}</p></article>
            <article class="metric-card"><span>PayPal</span><strong>{{ filled($integrations['paypal_client_id'] ?? '') && filled($integrations['paypal_client_secret'] ?? '') ? 'Configurado' : 'Pendiente' }}</strong><p>Modo {{ strtoupper($integrations['paypal_mode'] ?? 'sandbox') }}.</p></article>
        </div>
    </article>
</section>
<section class="panel-grid panel-grid--admin-overview" id="payment-methods">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Cobros seller</p><h2>Metodos de pago</h2></div></div>
        <form method="POST" action="{{ route('admin.payment-methods.update') }}" class="portal-form">
            @csrf
            <div class="catalog-stack">
                <article class="catalog-block">
                    <strong>Metodos offline</strong>
                    <p class="empty-copy">Estos pagos quedan pendientes hasta que el owner los verifique manualmente.</p>
                    <div class="form-actions mt-4">
                        @foreach (($paymentMethods['offline'] ?? []) as $key => $method)
                            <label class="inline-check"><input type="checkbox" name="methods[]" value="{{ $key }}" @checked(! empty($method['enabled'])) /> <span>{{ $method['label'] }}</span></label>
                        @endforeach
                    </div>
                </article>
                <article class="catalog-block">
                    <strong>Metodos online</strong>
                    <p class="empty-copy">PayPal puede activar automaticamente. Tilopay puede encenderse para uso comercial desde el portal.</p>
                    <div class="form-actions mt-4">
                        @foreach (($paymentMethods['online'] ?? []) as $key => $method)
                            <label class="inline-check"><input type="checkbox" name="methods[]" value="{{ $key }}" @checked(! empty($method['enabled'])) /> <span>{{ $method['label'] }}</span></label>
                        @endforeach
                    </div>
                </article>
            </div>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar metodos</button></div>
        </form>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Tasador</p><h2>IA opcional</h2></div></div>
        <div class="dashboard-grid dashboard-grid--two-up compact-grid">
            <article class="metric-card"><span>Motor base</span><strong>Activo</strong><p>Comparables locales y depreciacion CR.</p></article>
            <article class="metric-card"><span>API IA</span><strong>{{ $valuationAiConfigured ? 'Configurada' : 'Pendiente' }}</strong><p>{{ $valuationAiConfigured ? 'Lista para narrativa opcional.' : 'Se puede dejar apagada sin afectar el tasador.' }}</p></article>
        </div>
        <form method="POST" action="{{ route('admin.valuation-ai.update') }}" class="portal-form">
            @csrf
            <label class="inline-check"><input type="checkbox" name="valuation_ai_enabled" value="1" @checked($valuationAiEnabled) /> <span>Activar resumen IA en evaluaciones nuevas</span></label>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar ajuste</button></div>
        </form>
    </article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Planes</p><h2>Planes disponibles</h2></div></div>
        <div class="catalog-stack">
            @foreach ($plans as $plan)
                <article class="catalog-block">
                    <div class="catalog-block__header"><div><strong>{{ $plan->name }}</strong><p>{{ $plan->description }}</p></div><span class="status-badge">${{ number_format((float) $plan->price, 0) }}</span></div>
                    <p class="empty-copy">{{ $plan->max_active_listings ?? 'Ilimitadas' }} publicaciones · {{ $plan->photo_limit ?? 'Ilimitadas' }} fotos · {{ $plan->allows_video ? 'video' : 'sin video' }} · {{ $plan->allows_360 ? '360' : 'sin 360' }}</p>
                </article>
            @endforeach
        </div>
    </article>

    <article class="dashboard-panel" id="features">
        <div class="panel-heading"><div><p class="portal-kicker">Onboarding seller</p><h2>Features configurables</h2></div></div>
        <form method="POST" action="{{ route('admin.feature-options.store') }}" class="portal-form">
            @csrf
            <div class="form-grid">
                <label class="form-field"><span>Nombre del feature</span><input type="text" name="name" placeholder="Ej. Camara 360" required /></label>
                <label class="form-field"><span>Categoria</span><input type="text" name="category" placeholder="seguridad, confort, tecnologia" required /></label>
                <label class="form-field form-field--wide"><span>Descripcion</span><input type="text" name="description" placeholder="Texto corto para el seller" /></label>
                <label class="form-field"><span>Orden</span><input type="number" name="sort_order" min="0" max="9999" value="0" /></label>
            </div>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar feature</button></div>
        </form>
        <div class="catalog-stack mt-4">
            @forelse ($featureOptions as $category => $group)
                <article class="catalog-block">
                    <div class="catalog-block__header"><div><strong>{{ str($category)->replace('-', ' ')->title() }}</strong><p>{{ $group->count() }} opciones</p></div></div>
                    <div class="chip-grid">
                        @foreach ($group as $feature)
                            <div class="chip-card">
                                <div><strong>{{ $feature->name }}</strong><p>{{ $feature->description }}</p></div>
                                <form method="POST" action="{{ route('admin.feature-options.toggle', $feature) }}">@csrf @method('PATCH')<button type="submit" class="text-link">{{ $feature->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="empty-state"><strong>Sin features</strong><p>Todavia no has configurado features para el seller.</p></div>
            @endforelse
        </div>
    </article>
</section>
@endsection

