@extends('layouts.portal')

@section('title', 'Test SMTP | Movikaa')
@section('portal-eyebrow', 'Administracion')
@section('portal-title', 'Prueba de correo SMTP')
@section('portal-copy', 'Valida desde el panel si el servidor esta enviando correos con la configuracion activa y detecta rapido cuando la app sigue usando el canal de log.')

@section('header-actions')
    <a href="{{ route('admin.settings') }}" class="button button--ghost">Ajustes</a>
    <a href="{{ route('admin.plans') }}" class="button button--solid">Planes</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Mailer activo</span>
        <strong>{{ strtoupper($mailConfig['default_mailer'] ?: 'n/a') }}</strong>
        <p>{{ $mailConfig['default_mailer'] === 'smtp' ? 'La app intentara salir por SMTP.' : 'La app no esta usando SMTP ahora mismo.' }}</p>
    </article>
    <article class="metric-card">
        <span>Servidor SMTP</span>
        <strong>{{ $mailConfig['host'] !== '' ? $mailConfig['host'] : 'Sin definir' }}</strong>
        <p>Puerto {{ $mailConfig['port'] !== '' ? $mailConfig['port'] : 'n/a' }} {{ $mailConfig['scheme'] !== '' ? '· '.$mailConfig['scheme'] : '' }}</p>
    </article>
    <article class="metric-card">
        <span>Remitente</span>
        <strong>{{ $mailConfig['from_address'] !== '' ? $mailConfig['from_address'] : 'Sin definir' }}</strong>
        <p>{{ $mailConfig['from_name'] !== '' ? $mailConfig['from_name'] : 'Sin nombre configurado' }}</p>
    </article>
</section>

<section class="panel-grid reveal reveal--delay-1" style="grid-template-columns: 1.15fr 0.85fr; margin-top: 1.5rem;">
    <article class="dashboard-panel" id="mail-test-form">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Diagnostico</p>
                <h2>Enviar correo de prueba</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.mail-test.send') }}" class="portal-form" style="margin-top: 1rem;">
            @csrf
            <label class="form-field">
                <span>Destino</span>
                <input type="email" name="email" value="{{ old('email', $defaultRecipient) }}" required placeholder="destino@correo.com">
            </label>

            <label class="form-field">
                <span>Asunto</span>
                <input type="text" name="subject" value="{{ old('subject', 'Prueba SMTP Movikaa') }}" maxlength="120" placeholder="Prueba SMTP Movikaa">
            </label>

            <label class="form-field">
                <span>Mensaje</span>
                <textarea name="message" rows="6" maxlength="2000" placeholder="Escribe una nota corta para identificar esta prueba.">{{ old('message', 'Este correo confirma si la configuracion SMTP actual de Movikaa esta funcionando correctamente.') }}</textarea>
            </label>

            <button type="submit" class="button button--solid" style="width: 100%;">Enviar prueba</button>
        </form>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Revision rapida</p>
                <h2>Configuracion cargada</h2>
            </div>
        </div>

        <div style="display: grid; gap: 0.85rem; margin-top: 1rem;">
            <div style="background: var(--portal-soft); border: 1px solid var(--portal-border); border-radius: 12px; padding: 1rem;">
                <small style="display:block; color: var(--portal-muted); margin-bottom: 0.35rem;">MAIL_MAILER</small>
                <strong>{{ $mailConfig['default_mailer'] ?: 'Sin definir' }}</strong>
            </div>
            <div style="background: var(--portal-soft); border: 1px solid var(--portal-border); border-radius: 12px; padding: 1rem;">
                <small style="display:block; color: var(--portal-muted); margin-bottom: 0.35rem;">MAIL_HOST</small>
                <strong>{{ $mailConfig['host'] ?: 'Sin definir' }}</strong>
            </div>
            <div style="background: var(--portal-soft); border: 1px solid var(--portal-border); border-radius: 12px; padding: 1rem;">
                <small style="display:block; color: var(--portal-muted); margin-bottom: 0.35rem;">MAIL_PORT / MAIL_SCHEME</small>
                <strong>{{ ($mailConfig['port'] ?: 'n/a').' / '.($mailConfig['scheme'] ?: 'sin esquema') }}</strong>
            </div>
            <div style="background: var(--portal-soft); border: 1px solid var(--portal-border); border-radius: 12px; padding: 1rem;">
                <small style="display:block; color: var(--portal-muted); margin-bottom: 0.35rem;">MAIL_USERNAME</small>
                <strong>{{ $mailConfig['username'] ?: 'Sin definir' }}</strong>
            </div>
        </div>

        @if ($mailConfig['default_mailer'] !== 'smtp')
            <p style="margin-top: 1rem; color: #f59e0b; font-size: 0.92rem; line-height: 1.5;">
                Advertencia: el mailer activo no es <code>smtp</code>. Si haces una prueba ahora, Laravel puede escribir el correo en el log en lugar de enviarlo realmente.
            </p>
        @endif
    </article>
</section>
@endsection
