@extends('layouts.portal')

@section('title', 'Planes vendedor | Movikaa')
@section('portal-eyebrow', 'Planes vendedor')
@section('portal-title', 'Visibilidad y pagos de tus anuncios')
@section('portal-copy', 'Elige un plan, revisa cuando vence tu ciclo actual y activa o programa el siguiente sin duplicar suscripciones activas.')

@section('content')
<section class="dashboard-grid reveal">
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
                Suscripción base (Básico).
            @endif
        </p>
    </article>
    <article class="metric-card">
        <span>Próximo ciclo</span>
        <strong>{{ $scheduledPlan?->name ?? 'Sin cambios' }}</strong>
        <p>
            @if ($scheduledSubscription?->starts_at)
                Activación el {{ $scheduledSubscription->starts_at->format('d/m/Y') }}.
            @else
                No hay planes programados.
            @endif
        </p>
    </article>
    <article class="metric-card">
        <span>Transacciones</span>
        <strong>{{ $transactions->where('status', 'pending')->count() }} pendientes</strong>
        <p>Pagos offline en revisión manual.</p>
    </article>
</section>

<div class="panel-grid reveal reveal--delay-1" style="grid-template-columns: 1fr 1fr; margin-top: 1.5rem; gap: 1.5rem;">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Paso 1</p><h2>Catálogo de Planes</h2></div></div>
        <div class="list-stack">
            @foreach ($plans as $plan)
                @php
                    $isCurrent = $subscription?->plan_id === $plan->id;
                    $isScheduled = $scheduledSubscription?->plan_id === $plan->id;
                @endphp
                <div class="list-row {{ ($selectedPlan?->id === $plan->id) ? 'list-row--active' : '' }}" style="padding: 1rem; border-radius: 8px; margin-bottom: 0.5rem; {{ ($selectedPlan?->id === $plan->id) ? 'border: 1px solid var(--portal-primary); background: var(--portal-soft);' : 'border: 1px solid var(--portal-border);' }}">
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <strong style="color: var(--portal-primary);">{{ $plan->name }}</strong>
                            <strong style="font-size: 1.1rem;">${{ number_format((float) $plan->price, 0) }}</strong>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--portal-muted); margin-top: 0.25rem;">{{ $plan->description }}</p>
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                             @if ($isCurrent) <span class="pill pill--success" style="font-size: 0.6rem;">PLAN ACTUAL</span> @endif
                             @if ($isScheduled) <span class="pill pill--warn" style="font-size: 0.6rem;">PROGRAMADO</span> @endif
                        </div>
                    </div>
                    <a href="{{ route('seller.billing', ['plan' => $plan->slug]) }}" class="button {{ ($selectedPlan?->id === $plan->id) ? 'button--solid' : 'button--ghost' }}" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem; margin-left:1rem;">{{ ($selectedPlan?->id === $plan->id) ? 'âœ“' : 'Ver' }}</a>
                </div>
            @endforeach
        </div>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Paso 2</p><h2>Confirmar y Pagar</h2></div></div>
        @if ($selectedPlan)
             @php
                $selectedIsCurrent = $subscription?->plan_id === $selectedPlan->id;
                $selectedIsScheduled = $scheduledSubscription?->plan_id === $selectedPlan->id;
            @endphp
            <div style="background: var(--portal-soft); border: 1px solid var(--portal-border); border-radius: 8px; padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--portal-border); padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div><strong>Plan: {{ $selectedPlan->name }}</strong><p style="font-size: 0.8rem; color: var(--portal-muted); margin-top:0.25rem;">{{ $selectedPlan->duration_days ?: 30 }} días de visibilidad.</p></div>
                    <strong style="font-size: 1.5rem; color: var(--portal-primary);">${{ number_format((float) $selectedPlan->price, 0) }}</strong>
                </div>

                @if ($selectedIsCurrent)
                    <p style="text-align: center; color: var(--portal-muted); font-size: 0.85rem; padding: 1rem;">Este plan es tu suscripción actual activa.</p>
                @elseif ($selectedIsScheduled)
                    <p style="text-align: center; color: var(--portal-muted); font-size: 0.85rem; padding: 1rem;">Ya tienes este plan programado para tu siguiente ciclo.</p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @if ($selectedPlan->price <= 0)
                            <form method="POST" action="{{ (config('app.enable_payments') ? route('seller.billing.free') : route('seller.dashboard')) }}">@csrf<input type="hidden" name="plan_slug" value="{{ $selectedPlan->slug }}"><button type="submit" class="button button--solid" style="width: 100%;">Activar Gratis</button></form>
                        @else
                            <p style="font-size: 0.75rem; color: var(--portal-muted); margin-bottom: 0.5rem;">Elige tu método de pago preferido:</p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                @foreach (($paymentMethods['offline'] ?? []) as $key => $method)
                                    @if (! empty($method['enabled']))
                                        <form method="POST" action="{{ (config('app.enable_payments') ? route('seller.billing.request-payment') : route('seller.dashboard')) }}">@csrf<input type="hidden" name="plan_slug" value="{{ $selectedPlan->slug }}"><input type="hidden" name="payment_method" value="{{ $key }}"><button type="submit" class="button button--ghost" style="width:100%; font-size: 0.75rem; padding: 0.5rem;">{{ $method['label'] }}</button></form>
                                    @endif
                                @endforeach
                                @if (! empty(data_get($paymentMethods, 'online.paypal.enabled')))
                                    <form method="POST" action="{{ (config('app.enable_payments') ? route('seller.billing.paypal.create-order') : route('seller.dashboard')) }}" style="grid-column: span 2;">@csrf<input type="hidden" name="plan_slug" value="{{ $selectedPlan->slug }}"><button type="submit" class="button button--solid" style="width: 100%; background: #ffc439; color: #111;" {{ $paypalConfigured ? '' : 'disabled' }}>PayPal</button></form>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @else
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; text-align: center; color: var(--portal-muted); border: 2px dashed var(--portal-border); border-radius: 8px;">
                Selecciona un plan a la izquierda para ver los detalles de activación.
            </div>
        @endif
    </article>
</div>

<section class="dashboard-panel reveal reveal--delay-2" style="margin-top: 1.5rem;">
    <div class="panel-heading"><div><p class="portal-kicker">Historial</p><h2>Pagos y Transacciones</h2></div></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Referencia</th><th>Plan</th><th>Estado</th><th>Monto</th><th>Método</th><th>Activación</th></tr></thead>
            <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td><strong style="font-size: 0.85rem;">{{ $transaction->external_reference ?: 'REF-'.str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</strong><p style="margin:0; font-size: 0.7rem; color: var(--portal-muted);">{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</p></td>
                    <td><span style="font-weight: 500;">{{ $transaction->plan?->name }}</span></td>
                    <td><span class="status-badge {{ $transaction->status === 'paid' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $transaction->status === 'paid' ? 'Completado' : 'Pendiente' }}</span></td>
                    <td><strong style="color: var(--portal-primary);">${{ number_format((float) $transaction->amount, 0) }}</strong></td>
                    <td><span style="font-size: 0.8rem; text-transform: capitalize;">{{ str($transaction->payment_method ?: $transaction->provider)->replace('_', ' ') }}</span></td>
                    <td><span style="font-size: 0.8rem; color: var(--portal-muted);">{{ data_get($transaction->payload, 'scheduled_change') ? 'Programado' : 'Inmediata' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center; padding: 4rem;">Aún no tienes actividad comercial en tu cuenta.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection



