@extends('layouts.portal')

@section('title', 'Ajustes | Movikaa')
@section('portal-eyebrow', 'AdministraciÃ³n')
@section('portal-title', 'ConfiguraciÃ³n del producto')
@section('portal-copy', 'Controla tema pÃºblico, moneda, IA y mÃ©todos de pago desde un mÃ³dulo limpio y mÃ¡s fÃ¡cil de operar.')

@section('header-actions')
    <a href="{{ route('admin.features') }}" class="button button--ghost">CaracterÃ­sticas</a>
    <a href="{{ route('admin.plans') }}" class="button button--solid">Planes</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>CotizaciÃ³n CRC</span>
        <strong>{{ number_format((float) data_get($exchangeQuote, 'usd_to_crc', 0), 2) }}</strong>
        <p>Fuente: {{ data_get($exchangeQuote, 'source', 'BCCR') }}</p>
    </article>
    <article class="metric-card">
        <span>ConversiÃ³n Inversa</span>
        <strong>{{ number_format((float) data_get($exchangeQuote, 'crc_to_usd', 0), 6) }}</strong>
        <p>USD por cada ColÃ³n.</p>
    </article>
    <article class="metric-card">
        <span>Apariencia PÃºblica</span>
        <strong>{{ $publicTheme === 'dark' ? 'Dark Mode' : 'Light Mode' }}</strong>
        <p>Estado actual del frontend.</p>
    </article>
</section>

<section class="panel-grid reveal reveal--delay-1" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.5rem;">
    <article class="dashboard-panel">
        <div class="panel-heading">
            <div><p class="portal-kicker">EconomÃ­a</p><h2>Divisa Sistema</h2></div>
            <form method="POST" action="{{ route('admin.exchange-rate.refresh') }}">
                @csrf
                <button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Forzar actualizaciÃ³n</button>
            </form>
        </div>
        <p style="color: var(--portal-muted); font-size: 0.85rem; margin-top: 1rem;">
            Configura el valor de referencia para el marketplace. Ãšltima actualizaciÃ³n: <strong>{{ data_get($exchangeQuote, 'fetched_at') ? \Illuminate\Support\Carbon::parse(data_get($exchangeQuote, 'fetched_at'))->format('d/m/Y H:i') : 'Nunca' }}</strong>
        </p>
    </article>

    <article class="dashboard-panel" id="public-theme">
        <div class="panel-heading"><div><p class="portal-kicker">EstÃ©tica</p><h2>DiseÃ±o del Frontend</h2></div></div>
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
        <div class="panel-heading"><div><p class="portal-kicker">Inteligencia</p><h2>Motor de ValuaciÃ³n</h2></div></div>
        <form method="POST" action="{{ route('admin.valuation-ai.update') }}" class="portal-form">
            @csrf
            <div style="background: var(--portal-soft); padding: 1rem; border-radius: 8px; border: 1px solid var(--portal-border); margin-bottom: 1rem;">
                <label class="inline-check">
                    <input type="checkbox" name="valuation_ai_enabled" value="1" @checked($valuationAiEnabled) />
                    <span>Habilitar Asistente IA</span>
                </label>
                <p style="font-size: 0.75rem; color: var(--portal-muted); margin-top: 0.5rem; margin-left: 1.5rem;">
                    {{ $valuationAiConfigured ? 'Ã¢Å“â€œ ConfiguraciÃ³n validada.' : 'Ã¢Å¡Â  Pendiente de configurar API Key.' }}
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
            <button type="submit" class="button button--solid" style="width: 100%;">Sincronizar mÃ©todos</button>
        </form>
    </article>
    @endif
</section>
@endsection



