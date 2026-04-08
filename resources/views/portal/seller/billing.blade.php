@extends('layouts.portal')

@section('title', 'Planes vendedor | Movikaa')
@section('portal-eyebrow', 'Planes vendedor')
@section('portal-title', 'Visibilidad y pagos de tus anuncios')
@section('portal-copy', 'Elige un plan, revisa cuando vence tu ciclo actual y activa o programa el siguiente sin duplicar suscripciones activas.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('seller.dashboard') }}">Resumen</a>
    <a href="{{ route('seller.listings') }}">Publicaciones</a>
    <a href="{{ route('seller.onboarding.create') }}">Nuevo anuncio</a>
    <a href="{{ route('seller.media') }}">Media</a>
    <a href="{{ route('seller.messages') }}">Mensajes</a>
    <a href="{{ route('seller.billing') }}" class="is-active">Planes y pagos</a>
    <a href="{{ route('buyer.dashboard') }}">Actividad comprador</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card">
        <span>Plan vigente</span>
        <strong>{{ $currentPlan->name }}</strong>
        <p>
            @if ($subscription)
                Activo desde {{ optional($subscription->starts_at)->format('d/m/Y') }}
                @if ($subscription->ends_at)
                    hasta {{ $subscription->ends_at->format('d/m/Y') }}
                @endif
            @else
                Sin suscripci?n pagada activa. Tu cuenta usa la base del plan B?sico.
            @endif
        </p>
    </article>
    <article class="metric-card">
        <span>Proximo movimiento</span>
        <strong>{{ $scheduledPlan?->name ?? 'Sin cambio programado' }}</strong>
        <p>
            @if ($scheduledSubscription?->starts_at)
                Entrar? en vigor el {{ $scheduledSubscription->starts_at->format('d/m/Y') }}.
            @else
                No tienes otro plan pendiente para el siguiente ciclo.
            @endif
        </p>
    </article>
    <article class="metric-card">
        <span>Estado comercial</span>
        <strong>{{ $transactions->where('status', 'pending')->count() }} pendientes</strong>
        <p>Las solicitudes offline las revisa el owner. PayPal se confirma automaticamente al capturarse.</p>
    </article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Paso 1</p>
                <h2>Selecciona tu plan</h2>
            </div>
            @if ($subscription?->ends_at)
                <span class="status-badge">Vence {{ $subscription->ends_at->format('d/m/Y') }}</span>
            @endif
        </div>
        <div class="catalog-stack">
            @foreach ($plans as $plan)
                @php
                    $isCurrent = $subscription?->plan_id === $plan->id;
                    $isScheduled = $scheduledSubscription?->plan_id === $plan->id;
                @endphp
                <article class="catalog-block {{ ($selectedPlan?->id === $plan->id) ? 'catalog-block--selected' : '' }}">
                    <div class="catalog-block__header">
                        <div>
                            <strong>{{ $plan->name }}</strong>
                            <p>{{ $plan->description }}</p>
                        </div>
                        <span class="status-badge">${{ number_format((float) $plan->price, 0) }}</span>
                    </div>
                    <p class="empty-copy">{{ $plan->max_active_listings ?? 'Ilimitadas' }} publicaciones | {{ $plan->photo_limit ?? 'Ilimitadas' }} fotos | {{ $plan->allows_video ? 'video' : 'sin video' }} | {{ $plan->allows_360 ? '360' : 'sin 360' }}</p>
                    <div class="table-actions mt-4">
                        @if ($isCurrent)
                            <span class="status-badge status-badge--success">Plan actual</span>
                        @elseif ($isScheduled)
                            <span class="status-badge status-badge--warn">Programado</span>
                        @endif
                        <a href="{{ route('seller.billing', ['plan' => $plan->slug]) }}" class="button {{ ($selectedPlan?->id === $plan->id) ? 'button--solid' : 'button--ghost' }}">{{ ($selectedPlan?->id === $plan->id) ? 'Plan seleccionado' : 'Seleccionar plan' }}</a>
                    </div>
                </article>
            @endforeach
        </div>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Paso 2</p>
                <h2>Checkout del plan</h2>
            </div>
        </div>

        @if ($selectedPlan)
            @php
                $selectedIsCurrent = $subscription?->plan_id === $selectedPlan->id;
                $selectedIsScheduled = $scheduledSubscription?->plan_id === $selectedPlan->id;
                $selectedStartsLater = $subscription && $subscription->ends_at && ! $selectedIsCurrent;
            @endphp

            <div class="catalog-stack">
                <article class="catalog-block">
                    <div class="catalog-block__header">
                        <div>
                            <strong>{{ $selectedPlan->name }}</strong>
                            <p>{{ $selectedPlan->description }}</p>
                        </div>
                        <span class="status-badge">${{ number_format((float) $selectedPlan->price, 0) }}</span>
                    </div>
                    <div class="vendedor-insight-grid mt-4">
                        <div><strong>Duraci?n</strong><p>{{ $selectedPlan->duration_days ? $selectedPlan->duration_days.' d?as' : 'Ciclo abierto' }}</p></div>
                        <div><strong>Fotos</strong><p>{{ $selectedPlan->photo_limit ?? 'Ilimitadas' }}</p></div>
                        <div><strong>Publicaciones</strong><p>{{ $selectedPlan->max_active_listings ?? 'Ilimitadas' }}</p></div>
                        <div><strong>Beneficios</strong><p>{{ $selectedPlan->allows_video ? 'Video' : 'Sin video' }} {{ $selectedPlan->allows_360 ? '+ 360' : '' }}</p></div>
                    </div>
                </article>

                <article class="catalog-block">
                    <strong>Estado de activacion</strong>
                    @if ($selectedIsCurrent)
                        <p class="empty-copy">Ya tienes este plan activo. No necesitas volver a pagarlo ni activarlo.</p>
                    @elseif ($selectedIsScheduled)
                        <p class="empty-copy">Este plan ya est? programado para entrar en vigor al finalizar tu ciclo actual.</p>
                    @elseif ($selectedStartsLater)
                        <p class="empty-copy">Si lo eliges ahora, quedara programado para iniciar al finalizar tu plan actual el {{ $subscription->ends_at->format('d/m/Y') }}. Nunca tendr?s dos planes activos al mismo tiempo.</p>
                    @else
                        <p class="empty-copy">Se activara de inmediato y empezara a contar desde hoy.</p>
                    @endif
                </article>

                @if (! $selectedIsCurrent && ! $selectedIsScheduled)
                    <article class="catalog-block">
                        <strong>Completa el pago o la solicitud</strong>
                        <p class="empty-copy">Elige un solo metodo de pago. Los metodos offline quedan pendientes para revision del owner.</p>
                        <div class="seller-toolbar mt-4">
                            @if ($selectedPlan->price <= 0)
                                <form method="POST" action="{{ route('seller.billing.free') }}">
                                    @csrf
                                    <input type="hidden" name="plan_slug" value="{{ $selectedPlan->slug }}">
                                    <button type="submit" class="button button--solid">Activar plan gratis</button>
                                </form>
                            @else
                                @foreach (($paymentMethods['offline'] ?? []) as $key => $method)
                                    @if (! empty($method['enabled']))
                                        <form method="POST" action="{{ route('seller.billing.request-payment') }}">
                                            @csrf
                                            <input type="hidden" name="plan_slug" value="{{ $selectedPlan->slug }}">
                                            <input type="hidden" name="payment_method" value="{{ $key }}">
                                            <button type="submit" class="button button--ghost">{{ $method['label'] }}</button>
                                        </form>
                                    @endif
                                @endforeach

                                @if (! empty(data_get($paymentMethods, 'online.paypal.enabled')))
                                    <form method="POST" action="{{ route('seller.billing.paypal.create-order') }}">
                                        @csrf
                                        <input type="hidden" name="plan_slug" value="{{ $selectedPlan->slug }}">
                                        <button type="submit" class="button button--solid" {{ $paypalConfigured ? '' : 'disabled' }}>Pagar con PayPal</button>
                                    </form>
                                @endif

                                @if (! empty(data_get($paymentMethods, 'online.tilopay.enabled')))
                                    <form method="POST" action="{{ route('seller.billing.request-payment') }}">
                                        @csrf
                                        <input type="hidden" name="plan_slug" value="{{ $selectedPlan->slug }}">
                                        <input type="hidden" name="payment_method" value="tilopay">
                                        <button type="submit" class="button button--ghost">Solicitar Tilopay</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </article>
                @endif
            </div>
        @endif
    </article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Historial</p><h2>Solicitudes y pagos recientes</h2></div></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Referencia</th><th>Plan</th><th>Estado</th><th>Monto</th><th>Metodo</th><th>Aplicacion</th></tr></thead>
            <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td><strong>{{ $transaction->external_reference }}</strong><span>{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</span></td>
                    <td>{{ $transaction->plan?->name }}</td>
                    <td><span class="status-badge {{ $transaction->status === 'paid' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $transaction->status === 'paid' ? 'Confirmado' : ($transaction->status === 'pending' ? 'Pendiente' : ucfirst($transaction->status)) }}</span></td>
                    <td>${{ number_format((float) $transaction->amount, 0) }}</td>
                    <td>{{ str($transaction->payment_method ?: $transaction->provider)->replace('_', ' ')->title() }}</td>
                    <td>
                        @if (data_get($transaction->payload, 'scheduled_change'))
                            Al finalizar el ciclo actual
                        @else
                            Inmediata
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Todav?a no hay transacciones registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection


