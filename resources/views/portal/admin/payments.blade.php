@extends('layouts.portal')

@section('title', 'Pagos | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Monitoreo comercial y verificación')
@section('portal-copy', 'Supervisa solicitudes de pago, pagos confirmados y activa manualmente los planes cuando el cobro llegue por medios offline.')

@section('header-actions')
    <a href="{{ route('admin.settings') }}#payment-methods" class="button button--ghost">Métodos de pago</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}">Catálogo</a>
    <a href="{{ route('admin.features') }}">Características</a>
    <a href="{{ route('admin.plans') }}">Planes</a>
    <a href="{{ route('admin.news') }}">Noticias</a>
    <a href="{{ route('admin.payments') }}" class="is-active">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Transacciones pagadas</span><strong>{{ $paidTransactionsCount }}</strong><p>Flujo comercial confirmado.</p></article>
    <article class="metric-card"><span>GMV</span><strong>${{ number_format($gmv, 0) }}</strong><p>Total acumulado por planes del vendedor.</p></article>
    <article class="metric-card"><span>Suscripciones activas</span><strong>{{ $activeSubscriptions->total() }}</strong><p>Usuarios con plan vigente.</p></article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Filtros</p><h2>Buscar pagos</h2></div></div>
    <form method="GET" action="{{ route('admin.payments') }}" class="portal-form portal-form--inline">
        <div class="form-grid">
            <label class="form-field"><span>Buscar</span><input type="text" name="q" value="{{ $paymentFilters['q'] }}" placeholder="Referencia o correo" /></label>
            <label class="form-field"><span>Estado</span><select name="status"><option value="">Todos</option><option value="pending" @selected($paymentFilters['status']==='pending')>Pendiente</option><option value="paid" @selected($paymentFilters['status']==='paid')>Pagado</option><option value="failed" @selected($paymentFilters['status']==='failed')>Rechazado</option></select></label>
            <label class="form-field"><span>Origen</span><select name="provider"><option value="">Todos</option><option value="paypal" @selected($paymentFilters['provider']==='paypal')>PayPal</option><option value="offline" @selected($paymentFilters['provider']==='offline')>Offline</option><option value="tilopay" @selected($paymentFilters['provider']==='tilopay')>Tilopay</option><option value="internal" @selected($paymentFilters['provider']==='internal')>Interno</option></select></label>
        </div>
        <div class="form-actions"><button type="submit" class="button button--solid">Filtrar</button><a href="{{ route('admin.payments') }}" class="button button--ghost">Limpiar</a></div>
    </form>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Pagos</p><h2>Pagos y solicitudes recientes</h2></div><span class="status-badge">{{ $latestTransactions->total() }} registros</span></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Referencia</th><th>Usuario</th><th>Plan</th><th>Estado</th><th>Monto</th><th>Método</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse ($latestTransactions as $transaction)
                <tr>
                    <td><strong>{{ $transaction->external_reference }}</strong><span>{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</span></td>
                    <td>{{ $transaction->user?->email }}</td>
                    <td>{{ $transaction->plan?->name }}</td>
                    <td><span class="status-badge {{ $transaction->status === 'paid' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $transaction->status === 'paid' ? 'Pagado' : ($transaction->status === 'pending' ? 'Pendiente' : ucfirst($transaction->status)) }}</span></td>
                    <td>${{ number_format((float) $transaction->amount, 0) }}</td>
                    <td>{{ str($transaction->payment_method ?: $transaction->provider)->replace('_', ' ')->title() }}</td>
                    <td>
                        <div class="table-actions">
                            @if ($transaction->status === 'pending')
                                <form method="POST" action="{{ route('admin.payments.approve', $transaction) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Aprobar</button></form>
                                <form method="POST" action="{{ route('admin.payments.reject', $transaction) }}">@csrf @method('PATCH')<button type="submit" class="text-link">Rechazar</button></form>
                            @else
                                <span class="status-badge">Sin acción</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No hay transacciones registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $latestTransactions->links() }}</div>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Suscripciones</p><h2>Planes activos</h2></div><span class="status-badge">{{ $activeSubscriptions->total() }} activas</span></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Usuario</th><th>Plan</th><th>Estado</th><th>Inicio</th><th>Vence</th></tr></thead>
            <tbody>
            @forelse ($activeSubscriptions as $subscription)
                <tr>
                    <td>{{ $subscription->user?->email }}</td>
                    <td>{{ $subscription->plan?->name }}</td>
                    <td><span class="status-badge status-badge--success">{{ $subscription->status }}</span></td>
                    <td>{{ optional($subscription->starts_at)->format('d/m/Y') }}</td>
                    <td>{{ optional($subscription->ends_at)->format('d/m/Y') ?? 'Sin fecha' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No hay suscripciones activas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $activeSubscriptions->links() }}</div>
</section>
@endsection
