@extends('layouts.portal')

@section('title', 'Ajustes | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Configuración del producto')
@section('portal-copy', 'Controla tema público, moneda, IA y métodos de pago desde un módulo limpio y más fácil de operar.')

@section('header-actions')
    <a href="{{ route('admin.features') }}" class="button button--ghost">Características</a>
    <a href="{{ route('admin.plans') }}" class="button button--solid">Planes</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Cotización CRC</span>
        <strong>{{ number_format((float) data_get($exchangeQuote, 'usd_to_crc', 0), 2) }}</strong>
        <p>Fuente: {{ data_get($exchangeQuote, 'source', 'BCCR') }}</p>
    </article>
    <article class="metric-card">
        <span>Conversión Inversa</span>
        <strong>{{ number_format((float) data_get($exchangeQuote, 'crc_to_usd', 0), 6) }}</strong>
        <p>USD por cada Colón.</p>
    </article>
    <article class="metric-card">
        <span>Apariencia Pública</span>
        <strong>{{ $publicTheme === 'dark' ? 'Dark Mode' : 'Light Mode' }}</strong>
        <p>Estado actual del frontend.</p>
    </article>
</section>

<section class="dashboard-grid reveal reveal--delay-1" style="margin-top: 1.5rem;" id="security-center">
    <article class="metric-card">
        <span>Honeypot</span>
        <strong>{{ data_get($securitySettings, 'honeypot.enabled') ? 'Activo' : 'Inactivo' }}</strong>
        <p>{{ count(data_get($securitySettings, 'honeypot.protected_forms', [])) }} formularios protegidos.</p>
    </article>
    <article class="metric-card">
        <span>ClamAV</span>
        <strong>
            @php($clamavReachable = data_get($securitySettings, 'clamav.reachable'))
            {{ $clamavReachable === true ? 'Conectado' : ($clamavReachable === false ? 'Sin conexion' : 'Sin verificar') }}
        </strong>
        <p>{{ data_get($securitySettings, 'clamav.status', 'Sin datos') }}</p>
    </article>
    <article class="metric-card">
        <span>IPs bloqueadas</span>
        <strong>{{ count(data_get($securitySettings, 'request_filters.blocked_ips', [])) }}</strong>
        <p>Listas aplicadas a nivel de aplicacion.</p>
    </article>
</section>

<section class="panel-grid reveal reveal--delay-1" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.5rem;">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Seguridad</p><h2>Centro de Seguridad</h2></div></div>
        <div style="display:grid; gap: 1rem;">
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <strong style="display:block;">Honeypot publico</strong>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); line-height:1.6;">
                    Estado: <strong>{{ data_get($securitySettings, 'honeypot.enabled') ? 'Activo' : 'Inactivo' }}</strong>,
                    randomizacion: <strong>{{ data_get($securitySettings, 'honeypot.randomize') ? 'Si' : 'No' }}</strong>,
                    espera minima: <strong>{{ data_get($securitySettings, 'honeypot.seconds') }}s</strong>.
                </p>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); line-height:1.6;">
                    Campo trampa: <code>{{ data_get($securitySettings, 'honeypot.field') }}</code>,
                    timestamp: <code>{{ data_get($securitySettings, 'honeypot.timestamp_field') }}</code>.
                </p>
            </div>
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <strong style="display:block;">Formularios protegidos</strong>
                <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:0.75rem;">
                    @foreach (data_get($securitySettings, 'honeypot.protected_forms', []) as $protectedForm)
                        <span style="display:inline-flex; padding:0.35rem 0.65rem; border-radius:999px; background:rgba(15,118,110,0.12); color:var(--portal-primary); font-size:0.78rem; font-weight:700;">{{ $protectedForm }}</span>
                    @endforeach
                </div>
            </div>
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <strong style="display:block;">ClamAV</strong>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); line-height:1.6;">
                    Estado: <strong>{{ data_get($securitySettings, 'clamav.status') }}</strong>
                </p>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); line-height:1.6;">
                    Socket preferido: <code>{{ data_get($securitySettings, 'clamav.preferred_socket') }}</code>
                </p>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); line-height:1.6; overflow-wrap:anywhere;">
                    Endpoint: <code>{{ data_get($securitySettings, 'clamav.socket') }}</code>
                </p>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); line-height:1.6;">
                    Escaneo activo: <strong>{{ data_get($securitySettings, 'clamav.skip_validation') ? 'No, esta en bypass' : 'Si' }}</strong>
                </p>
            </div>
        </div>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Filtros</p><h2>Bloqueos de Peticion</h2></div></div>
        <div style="display:grid; gap:1rem;">
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <small style="display:block; color:var(--portal-primary); font-weight:700;">IP blacklist</small>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted);">{{ count(data_get($securitySettings, 'request_filters.blocked_ips', [])) }} entradas cargadas.</p>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); overflow-wrap:anywhere;">{{ count(data_get($securitySettings, 'request_filters.blocked_ips', [])) ? implode(', ', data_get($securitySettings, 'request_filters.blocked_ips', [])) : 'Sin IPs bloqueadas manualmente.' }}</p>
            </div>
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <small style="display:block; color:var(--portal-primary); font-weight:700;">IP allowlist</small>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted);">{{ count(data_get($securitySettings, 'request_filters.allowed_ips', [])) }} entradas cargadas.</p>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); overflow-wrap:anywhere;">{{ count(data_get($securitySettings, 'request_filters.allowed_ips', [])) ? implode(', ', data_get($securitySettings, 'request_filters.allowed_ips', [])) : 'Sin allowlist forzada.' }}</p>
            </div>
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <small style="display:block; color:var(--portal-primary); font-weight:700;">User-Agents sospechosos</small>
                <p style="margin:0.45rem 0 0; color:var(--portal-muted); overflow-wrap:anywhere;">{{ implode(', ', data_get($securitySettings, 'request_filters.blocked_user_agents', [])) }}</p>
            </div>
        </div>
    </article>
</section>

<section class="dashboard-panel reveal reveal--delay-1" style="margin-top: 1.5rem;">
    <div class="panel-heading">
        <div><p class="portal-kicker">Seguridad</p><h2>Configurar defensas</h2></div>
        <form method="POST" action="{{ route('admin.security-settings.clamav-test') }}">
            @csrf
            <button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Probar conexion ClamAV</button>
        </form>
    </div>
    <form method="POST" action="{{ route('admin.security-settings.update') }}" class="portal-form" id="security-form">
        @csrf
        <div class="form-grid">
            <label class="form-field">
                <span>Espera minima Honeypot (segundos)</span>
                <input type="number" min="1" max="10" name="honeypot_seconds" value="{{ old('honeypot_seconds', data_get($securitySettings, 'honeypot.seconds')) }}" />
            </label>
            <label class="form-field">
                <span>Socket preferido ClamAV</span>
                <select name="clamav_preferred_socket">
                    <option value="tcp_socket" @selected(old('clamav_preferred_socket', data_get($securitySettings, 'clamav.preferred_socket')) === 'tcp_socket')>TCP</option>
                    <option value="unix_socket" @selected(old('clamav_preferred_socket', data_get($securitySettings, 'clamav.preferred_socket')) === 'unix_socket')>Unix</option>
                </select>
            </label>
            <label class="form-field">
                <span>ClamAV TCP socket</span>
                <input type="text" name="clamav_tcp_socket" value="{{ old('clamav_tcp_socket', data_get($securitySettings, 'clamav.preferred_socket') === 'tcp_socket' ? data_get($securitySettings, 'clamav.socket') : config('clamav.tcp_socket')) }}" />
            </label>
            <label class="form-field">
                <span>ClamAV Unix socket</span>
                <input type="text" name="clamav_unix_socket" value="{{ old('clamav_unix_socket', data_get($securitySettings, 'clamav.preferred_socket') === 'unix_socket' ? data_get($securitySettings, 'clamav.socket') : config('clamav.unix_socket')) }}" />
            </label>
            <label class="form-field">
                <span>Timeout conexion ClamAV</span>
                <input type="number" min="1" max="30" name="clamav_socket_connect_timeout" value="{{ old('clamav_socket_connect_timeout', config('clamav.socket_connect_timeout')) }}" />
            </label>
            <label class="form-field">
                <span>Timeout lectura ClamAV</span>
                <input type="number" min="1" max="120" name="clamav_socket_read_timeout" value="{{ old('clamav_socket_read_timeout', config('clamav.socket_read_timeout')) }}" />
            </label>
            <label class="form-field form-field--wide">
                <span>IPs bloqueadas</span>
                <textarea name="blocked_ips" rows="4" placeholder="203.0.113.10&#10;198.51.100.0">{{ old('blocked_ips', implode(PHP_EOL, data_get($securitySettings, 'request_filters.blocked_ips', []))) }}</textarea>
            </label>
            <label class="form-field form-field--wide">
                <span>IPs permitidas</span>
                <textarea name="allowed_ips" rows="4" placeholder="Deja vacio para no forzar allowlist">{{ old('allowed_ips', implode(PHP_EOL, data_get($securitySettings, 'request_filters.allowed_ips', []))) }}</textarea>
            </label>
            <label class="form-field form-field--wide">
                <span>User-Agents bloqueados</span>
                <textarea name="blocked_user_agents" rows="4" placeholder="sqlmap&#10;nikto&#10;nmap">{{ old('blocked_user_agents', implode(PHP_EOL, data_get($securitySettings, 'request_filters.blocked_user_agents', []))) }}</textarea>
            </label>
        </div>
        <div class="form-actions" style="justify-content:space-between;align-items:center;">
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <label class="inline-check"><input type="checkbox" name="honeypot_enabled" value="1" @checked(old('honeypot_enabled', data_get($securitySettings, 'honeypot.enabled'))) /> <span>Activar honeypot</span></label>
                <label class="inline-check"><input type="checkbox" name="honeypot_randomize" value="1" @checked(old('honeypot_randomize', data_get($securitySettings, 'honeypot.randomize'))) /> <span>Randomizar campo trampa</span></label>
                <label class="inline-check"><input type="checkbox" name="clamav_skip_validation" value="1" @checked(old('clamav_skip_validation', data_get($securitySettings, 'clamav.skip_validation'))) /> <span>Bypass temporal de ClamAV</span></label>
            </div>
            <button type="submit" class="button button--solid">Guardar seguridad</button>
        </div>
    </form>
</section>

<section class="panel-grid reveal reveal--delay-1" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.5rem;">
    <article class="dashboard-panel">
        <div class="panel-heading">
            <div><p class="portal-kicker">Economía</p><h2>Divisa Sistema</h2></div>
            <form method="POST" action="{{ route('admin.exchange-rate.refresh') }}">
                @csrf
                <button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Forzar actualización</button>
            </form>
        </div>
        <p style="color: var(--portal-muted); font-size: 0.85rem; margin-top: 1rem;">
            Configura el valor de referencia para el marketplace. Última actualización: <strong>{{ data_get($exchangeQuote, 'fetched_at') ? \Illuminate\Support\Carbon::parse(data_get($exchangeQuote, 'fetched_at'))->format('d/m/Y H:i') : 'Nunca' }}</strong>
        </p>
    </article>

    <article class="dashboard-panel" id="public-theme">
        <div class="panel-heading"><div><p class="portal-kicker">Estética</p><h2>Diseño del Frontend</h2></div></div>
        <form method="POST" action="{{ route('admin.public-theme.update') }}" class="portal-form">
            @csrf
            <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                <label class="form-field" style="flex: 1;"><span>Modo Visual</span><select name="public_theme"><option value="light" @selected($publicTheme === 'light')>Claridad (Light)</option><option value="dark" @selected($publicTheme === 'dark')>Minimalista (Dark)</option></select></label>
                <button type="submit" class="button button--solid">Guardar</button>
            </div>
        </form>
    </article>
</section>

<section class="panel-grid reveal reveal--delay-2" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.5rem;">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Inteligencia</p><h2>Motor de Valuación</h2></div></div>
        <form method="POST" action="{{ route('admin.valuation-ai.update') }}" class="portal-form">
            @csrf
            <div style="background: var(--portal-soft); padding: 1rem; border-radius: 8px; border: 1px solid var(--portal-border); margin-bottom: 1rem;">
                <label class="inline-check">
                    <input type="checkbox" name="valuation_ai_enabled" value="1" @checked($valuationAiEnabled) />
                    <span>Habilitar Asistente IA</span>
                </label>
                <p style="font-size: 0.75rem; color: var(--portal-muted); margin-top: 0.5rem; margin-left: 1.5rem;">
                    {{ $valuationAiConfigured ? '✓ Configuración validada.' : '⚠  Pendiente de configurar API Key.' }}
                </p>
            </div>
            <button type="submit" class="button button--solid" style="width: 100%;">Actualizar motor</button>
        </form>
    </article>

    @if (config('app.enable_payments'))
    <article class="dashboard-panel" id="payment-methods">
        <div class="panel-heading"><div><p class="portal-kicker">Pagos</p><h2>Pasarelas de Cobro</h2></div></div>
        <form method="POST" action="{{ (config('app.enable_payments') ? route('admin.payment-methods.update') : route('admin.settings')) }}" class="portal-form">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div style="background: var(--portal-soft); padding: 1rem; border-radius: 8px; border: 1px solid var(--portal-border);">
                    <small style="display:block; margin-bottom: 0.5rem; color: var(--portal-primary); font-weight: 600;">OFFLINE</small>
                    @foreach (($paymentMethods['offline'] ?? []) as $key => $method)
                        <label class="inline-check" style="margin-bottom: 0.25rem;"><input type="checkbox" name="methods[]" value="{{ $key }}" @checked(! empty($method['enabled'])) /> <span style="font-size: 0.8rem;">{{ $method['label'] }}</span></label>
                    @endforeach
                </div>
                <div style="background: var(--portal-soft); padding: 1rem; border-radius: 8px; border: 1px solid var(--portal-border);">
                    <small style="display:block; margin-bottom: 0.5rem; color: var(--portal-primary); font-weight: 600;">ONLINE (API)</small>
                    @foreach (($paymentMethods['online'] ?? []) as $key => $method)
                        <label class="inline-check" style="margin-bottom: 0.25rem;"><input type="checkbox" name="methods[]" value="{{ $key }}" @checked(! empty($method['enabled'])) /> <span style="font-size: 0.8rem;">{{ $method['label'] }}</span></label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="button button--solid" style="width: 100%;">Sincronizar métodos</button>
        </form>
    </article>
    @endif
</section>

<section class="panel-grid reveal reveal--delay-2" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.5rem;">
    <article class="dashboard-panel" id="seo-settings">
        <div class="panel-heading"><div><p class="portal-kicker">SEO</p><h2>Defaults Globales</h2></div></div>
        <form method="POST" action="{{ route('admin.seo-settings.update') }}" class="portal-form">
            @csrf
            <div class="form-grid">
                <label class="form-field form-field--wide"><span>Título por defecto</span><input type="text" name="default_title" value="{{ old('default_title', $seoSettings['default_title']) }}" /></label>
                <label class="form-field"><span>Sufijo de marca</span><input type="text" name="title_suffix" value="{{ old('title_suffix', $seoSettings['title_suffix']) }}" /></label>
                <label class="form-field"><span>Imagen OG por defecto</span><input type="text" name="default_og_image" value="{{ old('default_og_image', $seoSettings['default_og_image']) }}" placeholder="https://..." /></label>
                <label class="form-field form-field--wide"><span>Meta descripción por defecto</span><textarea name="default_description" rows="3">{{ old('default_description', $seoSettings['default_description']) }}</textarea></label>
                <label class="form-field form-field--wide"><span>Google Site Verification</span><input type="text" name="google_site_verification" value="{{ old('google_site_verification', $seoSettings['google_site_verification']) }}" placeholder="Token de Search Console" /></label>
                <label class="form-field"><span>IndexNow Key</span><input type="text" name="indexnow_key" value="{{ old('indexnow_key', $seoSettings['indexnow_key']) }}" placeholder="Clave para Bing/IndexNow" /></label>
                <label class="form-field"><span>IndexNow Endpoint</span><input type="text" name="indexnow_endpoint" value="{{ old('indexnow_endpoint', $seoSettings['indexnow_endpoint']) }}" placeholder="https://api.indexnow.org/indexnow" /></label>
            </div>
            <div class="form-actions" style="justify-content:space-between;align-items:center;">
                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    <label class="inline-check"><input type="checkbox" name="index_filtered_inventory" value="1" @checked(old('index_filtered_inventory', $seoSettings['index_filtered_inventory'])) /> <span>Permitir indexación de inventario filtrado</span></label>
                    <label class="inline-check"><input type="checkbox" name="indexnow_enabled" value="1" @checked(old('indexnow_enabled', $seoSettings['indexnow_enabled'])) /> <span>Notificar cambios por IndexNow</span></label>
                </div>
                <button type="submit" class="button button--solid">Guardar SEO</button>
            </div>
        </form>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">SEO</p><h2>Vista rápida</h2></div></div>
        <div style="display:grid; gap: 1rem;">
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <small style="display:block; color:#5f6368;">{{ config('app.url') ?: 'https://tudominio.com' }}</small>
                <strong style="display:block; color:#1a0dab; font-size:1.1rem; margin-top:0.3rem;">{{ $seoSettings['default_title'] }}</strong>
                <p style="margin:0.45rem 0 0; color:#4d5156; line-height:1.6;">{{ $seoSettings['default_description'] }}</p>
            </div>
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <p style="margin:0; font-size:0.85rem; color:var(--portal-muted);">Sitemap principal</p>
                <a href="{{ route('sitemap.index') }}" target="_blank" rel="noreferrer" style="font-weight:700; color:var(--portal-primary);">{{ route('sitemap.index') }}</a>
            </div>
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <p style="margin:0; font-size:0.85rem; color:var(--portal-muted);">Robots</p>
                <a href="{{ asset('robots.txt') }}" target="_blank" rel="noreferrer" style="font-weight:700; color:var(--portal-primary);">{{ asset('robots.txt') }}</a>
            </div>
            <div style="padding: 1rem; border-radius: 14px; border: 1px solid var(--portal-border); background: var(--portal-soft);">
                <p style="margin:0; font-size:0.85rem; color:var(--portal-muted);">IndexNow Key URL</p>
                <a href="{{ route('indexnow.key') }}" target="_blank" rel="noreferrer" style="font-weight:700; color:var(--portal-primary);">{{ route('indexnow.key') }}</a>
            </div>
        </div>
    </article>
</section>

<section class="dashboard-panel reveal reveal--delay-2" id="seo-redirects" style="margin-top: 1.5rem;">
    <div class="panel-heading"><div><p class="portal-kicker">SEO</p><h2>Redirecciones 301 / 302</h2></div></div>
    <form method="POST" action="{{ route('admin.redirects.store') }}" class="portal-form" style="margin-bottom: 1.5rem;">
        @csrf
        <div class="form-grid">
            <label class="form-field"><span>Desde</span><input type="text" name="from_path" placeholder="/ruta-antigua" required /></label>
            <label class="form-field"><span>Hacia</span><input type="text" name="to_url" placeholder="https://dominio.com/nueva-ruta" required /></label>
            <label class="form-field"><span>Código</span><select name="status_code"><option value="301">301 Permanente</option><option value="302">302 Temporal</option></select></label>
        </div>
        <div class="form-actions" style="justify-content:space-between;align-items:center;">
            <label class="inline-check"><input type="checkbox" name="is_active" value="1" checked /> <span>Activar al guardar</span></label>
            <button type="submit" class="button button--solid">Crear redirección</button>
        </div>
    </form>

    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Desde</th><th>Hacia</th><th>Código</th><th>Hits</th><th>Estado</th><th style="text-align:right;">Acciones</th></tr></thead>
            <tbody>
            @forelse ($seoRedirects as $redirect)
                <tr>
                    <td><code>{{ $redirect->from_path }}</code></td>
                    <td style="max-width: 320px; overflow-wrap:anywhere;">{{ $redirect->to_url }}</td>
                    <td>{{ $redirect->status_code }}</td>
                    <td>{{ number_format($redirect->hit_count) }}</td>
                    <td>{{ $redirect->is_active ? 'Activa' : 'Inactiva' }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                            <form method="POST" action="{{ route('admin.redirects.update', $redirect) }}" style="display:flex; gap:0.5rem; align-items:center;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="from_path" value="{{ $redirect->from_path }}" />
                                <input type="hidden" name="to_url" value="{{ $redirect->to_url }}" />
                                <input type="hidden" name="status_code" value="{{ $redirect->status_code }}" />
                                @if ($redirect->is_active)
                                    <input type="hidden" name="is_active" value="1" />
                                    <button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Guardar</button>
                                @else
                                    <input type="hidden" name="is_active" value="1" />
                                    <button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Activar</button>
                                @endif
                            </form>
                            <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}" onsubmit="return confirm('¿Eliminar redirección?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem; color: var(--portal-warn);">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center; padding: 2rem;">Todavía no hay redirecciones registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
