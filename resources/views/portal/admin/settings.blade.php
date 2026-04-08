@extends('layouts.portal')

@section('title', 'Ajustes | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Configuración del producto')
@section('portal-copy', 'Controla tema público, moneda, IA y métodos de pago desde un módulo limpio y más fácil de operar.')

@section('header-actions')
    <a href="{{ route('admin.features') }}" class="button button--ghost">Características</a>
    <a href="{{ route('admin.plans') }}" class="button button--solid">Planes</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}">Catálogo</a>
    <a href="{{ route('admin.features') }}">Características</a>
    <a href="{{ route('admin.plans') }}">Planes</a>
    <a href="{{ route('admin.news') }}">Noticias</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}" class="is-active">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>CRC por USD</span><strong>{{ number_format((float) data_get($exchangeQuote, 'usd_to_crc', 0), 2) }}</strong><p>Fuente {{ data_get($exchangeQuote, 'source', 'N/A') }}</p></article>
    <article class="metric-card"><span>USD por CRC</span><strong>{{ number_format((float) data_get($exchangeQuote, 'crc_to_usd', 0), 6) }}</strong><p>{{ data_get($exchangeQuote, 'fetched_at') ? \Illuminate\Support\Carbon::parse(data_get($exchangeQuote, 'fetched_at'))->format('d/m/Y H:i') : 'Sin dato' }}</p></article>
    <article class="metric-card"><span>Tema público</span><strong>{{ $publicTheme === 'dark' ? 'Modo oscuro' : 'Modo claro' }}</strong><p>Controla la apariencia del sitio principal.</p></article>
</section>

<section class="panel-grid panel-grid--admin-overview" id="exchange-rate">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Moneda</p><h2>Tipo de cambio CRC / USD</h2></div><form method="POST" action="{{ route('admin.exchange-rate.refresh') }}">@csrf<button type="submit" class="button button--solid">Actualizar tasa</button></form></div>
        <p class="empty-copy">Usa este valor para mostrar referencias en dólares dentro del catálogo y del panel comercial.</p>
    </article>

    <article class="dashboard-panel" id="public-theme">
        <div class="panel-heading"><div><p class="portal-kicker">Frontend</p><h2>Tema público</h2></div></div>
        <form method="POST" action="{{ route('admin.public-theme.update') }}" class="portal-form">
            @csrf
            <label class="form-field"><span>Tema del sitio</span><select name="public_theme"><option value="light" @selected($publicTheme === 'light')>Modo claro</option><option value="dark" @selected($publicTheme === 'dark')>Modo oscuro</option></select></label>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar tema</button></div>
        </form>
    </article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">IA</p><h2>Resumen narrado de valuación</h2></div></div>
        <form method="POST" action="{{ route('admin.valuation-ai.update') }}" class="portal-form">
            @csrf
            <label class="inline-check"><input type="checkbox" name="valuation_ai_enabled" value="1" @checked($valuationAiEnabled) /> <span>Activar resumen IA opcional en nuevas evaluaci?nes</span></label>
            <p class="empty-copy">{{ $valuationAiConfigured ? 'La integración está configurada y lista para usarse.' : 'La integración no está configurada todavía. El sistema seguirá usando el cálculo base.' }}</p>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar ajuste</button></div>
        </form>
    </article>

    <article class="dashboard-panel" id="payment-methods">
        <div class="panel-heading"><div><p class="portal-kicker">Cobros</p><h2>Métodos de pago</h2></div></div>
        <form method="POST" action="{{ route('admin.payment-methods.update') }}" class="portal-form">
            @csrf
            <div class="catalog-stack">
                <article class="catalog-block">
                    <strong>Métodos offline</strong>
                    <p class="empty-copy">Estos pagos quedan pendientes hasta validación manual.</p>
                    <div class="form-actions mt-4">
                        @foreach (($paymentMethods['offline'] ?? []) as $key => $method)
                            <label class="inline-check"><input type="checkbox" name="methods[]" value="{{ $key }}" @checked(! empty($method['enabled'])) /> <span>{{ $method['label'] }}</span></label>
                        @endforeach
                    </div>
                </article>
                <article class="catalog-block">
                    <strong>Métodos online</strong>
                    <p class="empty-copy">Activa o apaga pasarelas según tu operación comercial.</p>
                    <div class="form-actions mt-4">
                        @foreach (($paymentMethods['online'] ?? []) as $key => $method)
                            <label class="inline-check"><input type="checkbox" name="methods[]" value="{{ $key }}" @checked(! empty($method['enabled'])) /> <span>{{ $method['label'] }}</span></label>
                        @endforeach
                    </div>
                </article>
            </div>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar métodos</button></div>
        </form>
    </article>
</section>
@endsection
