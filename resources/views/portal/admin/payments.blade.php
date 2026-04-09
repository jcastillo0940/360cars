@extends('layouts.portal')

@section('title', 'Pagos | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Monitoreo comercial y verificación')
@section('portal-copy', 'Supervisa solicitudes de pago, pagos confirmados y activa manualmente los planes cuando el cobro llegue por medios offline.')

@section('header-actions')
    <a href="{{ route('admin.settings') }}#payment-methods" class="button button--ghost">Métodos de pago</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Transacciones pagadas</span>
        <strong>{{ $paidTransactionsCount }}</strong>
        <p>Cerradas con éxito.</p>
    </article>
    <article class="metric-card">
        <span>GMV Acumulado</span>
        <strong>${{ number_format($gmv, 0) }}</strong>
        <p>Ingresos brutos por suscripciones.</p>
    </article>
    <article class="metric-card">
        <span>Planes Vigentes</span>
        <strong>{{ $activeSubscriptions->total() }}</strong>
        <p>Usuarios con servicio activo.</p>
    </article>
</sectio<section class="dashboard-panel reveal reveal--delay-1">
    <div class="panel-heading"><div><p class="portal-kicker">Filtrar</p><h2>Búsqueda de transacciones</h2></div></div>
    <form method="GET" action="{{ route('admin.payments') }}" class="portal-form">
        <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr auto; align-items: flex-end; gap: 1rem;">
            <label class="form-field"><span>Referencia o Usuario</span><input type="text" name="q" value="{{ $paymentFilters['q'] }}" placeholder="Ej. PAYPAL-123..." /></label>
            <label class="form-field"><span>Estado</span><select name="status"><option value="">Todos</option><option value="pending" @selected($paymentFilters['status']==='pending')>Pendiente</option><option value="paid" @selected($paymentFilters['status']==='paid')>Pagado</option><option value="failed" @selected($paymentFilters['status']==='failed')>Rechazado</option></select></label>
            <label class="form-field"><span>Origen</span><select name="provider"><option value="">Todos</option><option value="paypal" @selected($paymentFilters['provider']==='paypal')>PayPal</option><option value="offline" @selected($paymentFilters['provider']==='offline')>Offline</option></select></label>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="button button--solid">Aplicar</button>
                <a href="{{ route('admin.payments') }}" class="button button--ghost">Reset</a>
            </div>
        </div>
    </form>
</section>

<section class="dashboard-panel reveal reveal--delay-2">
    <div class="panel-heading">
        <div><p class="portal-kicker">Finanzas</p><h2>Movimientos Recientes</h2></div>
        <span class="status-badge">{{ $latestTransactions->total() }} registros</span>
    </div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Referencia / Fecha</th><th>Suscriptor</th><th>Plan</th><th>Estado</th><th>Monto</th><th style="text-align: right;">Acciones</th></tr></thead>
            <tbody>
            @forelse ($latestTransactions as $transaction)
                <tr>
                    <td>
                        <strong>{{ $transaction->external_reference ?: 'S/N' }}</strong>
                        <p style="margin:0; font-size: 0.75rem; color: var(--portal-muted);">{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</p>
                    </td>
                    <td><span style="font-size: 0.85rem;">{{ $transaction->user?->email }}</span></td>
                    <td><span class="pill">{{ $transaction->plan?->name }}</span></td>
                    <td>
                        <span class="status-badge {{ $transaction->status === 'paid' ? 'status-badge--success' : ($transaction->status === 'pending' ? 'status-badge--warn' : '') }}">
                            {{ $transaction->status === 'paid' ? 'Pagado' : ($transaction->status === 'pending' ? 'Pendiente' : 'Fallido') }}
                        </span>
                    </td>
                    <td><strong style="color: var(--portal-ink);">${{ number_format((float) $transaction->amount, 0) }}</strong></td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                            @if ($transaction->status === 'pending')
                                <form method="POST" action="{{ route('admin.payments.approve', $transaction) }}">@csrf @method('PATCH')<button type="submit" class="button button--solid" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Aprobar</button></form>
                                <form method="POST" action="{{ route('admin.payments.reject', $transaction) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Rechazar</button></form>
                            @else
                                <span style="font-size: 0.75rem; color: var(--portal-muted);">Procesado</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center; padding: 3rem;">No hay transacciones registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $latestTransactions->links() }}</div>
</section>

<section class="dashboard-panel reveal reveal--delay-3">
    <div class="panel-heading">
        <div><p class="portal-kicker">Activos</p><h2>Planes en Vigencia</h2></div>
        <span class="status-badge status-badge--success">{{ $activeSubscriptions->total() }} suscripciones</span>
    </div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Usuario</th><th>Plan Contratado</th><th>Estado</th><th>Fecha Inicio</th><th>Vence en</th></tr></thead>
            <tbody>
            @forelse ($activeSubscriptions as $subscription)
                <tr>
                    <td><strong>{{ $subscription->user?->email }}</strong></td>
                    <td>{{ $subscription->plan?->name }}</td>
                    <td><span class="status-badge status-badge--success">{{ $subscription->status }}</span></td>
                    <td>{{ optional($subscription->starts_at)->format('d/m/Y') }}</td>
                    <td>
                        <span style="color: {{ $subscription->ends_at < now() ? 'var(--portal-warn)' : 'inherit' }}">
                            {{ optional($subscription->ends_at)->format('d/m/Y') ?? 'Sin límite' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; padding: 3rem;">No hay suscripciones activas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $activeSubscriptions->links() }}</div>
</section>
@endsection
