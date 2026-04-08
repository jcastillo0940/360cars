@extends('layouts.portal')

@section('title', 'Administración | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Centro de control del marketplace')
@section('portal-copy', 'Supervisa inventario, pagos, usuarios y configuraciones clave desde un panel claro, comercial y fácil de operar.')

@section('header-actions')
    <a href="{{ route('admin.catalog') }}" class="button button--solid">Ver catálogo</a>
    <a href="{{ route('admin.settings') }}" class="button button--ghost">Ajustes</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}" class="{{ request()->routeIs('admin.catalog') ? 'is-active' : '' }}">Catálogo</a>
    <a href="{{ route('admin.features') }}" class="{{ request()->routeIs('admin.features') ? 'is-active' : '' }}">Características</a>
    <a href="{{ route('admin.plans') }}" class="{{ request()->routeIs('admin.plans') ? 'is-active' : '' }}">Planes</a>
    <a href="{{ route('admin.news') }}" class="{{ request()->routeIs('admin.news*') ? 'is-active' : '' }}">Noticias</a>
    <a href="{{ route('admin.payments') }}" class="{{ request()->routeIs('admin.payments') ? 'is-active' : '' }}">Pagos</a>
    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'is-active' : '' }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'is-active' : '' }}">Configuración</a>
</nav>
<div class="portal-note-card">
    <span class="portal-kicker">Mercado activo</span>
    <strong>{{ $publishedVehicleCount }} publicaciones</strong>
    <p>{{ $paidTransactionsCount }} pagos confirmados y {{ $leadCount }} contactos generados.</p>
</div>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>GMV acumulado</span><strong>${{ number_format($gmv, 0) }}</strong><p>Calculado a partir de pagos confirmados.</p></article>
    <article class="metric-card"><span>Usuarios nuevos hoy</span><strong>{{ $newUsers }}</strong><p>Cuentas creadas durante la jornada actual.</p></article>
    <article class="metric-card"><span>Moderación pendiente</span><strong>{{ $pendingModeration }}</strong><p>Publicaciones que aún requieren revisión.</p></article>
    <article class="metric-card"><span>Vehículos activos</span><strong>{{ $publishedVehicleCount }}</strong><p>Inventario visible para compradores y vendedores.</p></article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Ingresos</p><h2>Tendencia de pagos</h2></div></div>
        <div class="mini-bars">
            @foreach ($paymentTrendChart as $item)
                <div class="mini-bars__item">
                    <div class="mini-bars__columns mini-bars__columns--single"><span class="mini-bars__bar mini-bars__bar--yellow" style="height: {{ $item['height'] }}%"></span></div>
                    <strong>{{ $item['label'] }}</strong>
                    <small>${{ number_format((float) $item['value'], 0) }}</small>
                </div>
            @endforeach
        </div>
    </article>
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Inventario</p><h2>Tendencia de publicaciones</h2></div></div>
        <div class="mini-bars">
            @foreach ($inventoryTrendChart as $item)
                <div class="mini-bars__item">
                    <div class="mini-bars__columns mini-bars__columns--single"><span class="mini-bars__bar mini-bars__bar--blue" style="height: {{ $item['height'] }}%"></span></div>
                    <strong>{{ $item['label'] }}</strong>
                    <small>{{ $item['value'] }} autos</small>
                </div>
            @endforeach
        </div>
    </article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Pagos recientes</p><h2>Movimientos recientes</h2></div><a href="{{ route('admin.payments') }}" class="text-link">Ver módulo</a></div>
        <div class="table-shell">
            <table class="portal-table">
                <thead><tr><th>Orden</th><th>Usuario</th><th>Plan</th><th>Estado</th><th>Monto</th></tr></thead>
                <tbody>
                @forelse ($latestTransactions as $transaction)
                    <tr>
                        <td><strong>{{ $transaction->external_reference }}</strong><span>{{ $transaction->provider }}</span></td>
                        <td>{{ $transaction->user?->email }}</td>
                        <td>{{ $transaction->plan?->name }}</td>
                        <td><span class="status-badge {{ $transaction->status === 'paid' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $transaction->status }}</span></td>
                        <td>${{ number_format((float) $transaction->amount, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Todavía no hay pagos registrados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Moderación</p><h2>Últim?s publicaciones</h2></div><a href="{{ route('admin.users') }}" class="text-link">Ver usuarios</a></div>
        <div class="table-shell">
            <table class="portal-table">
                <thead><tr><th>Publicación</th><th>Propietario</th><th>Estado</th><th>Precio</th></tr></thead>
                <tbody>
                @forelse ($latestVehicles as $vehicle)
                    <tr>
                        <td><strong>{{ $vehicle->title }}</strong><span>{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }}</span></td>
                        <td>{{ $vehicle->owner?->email }}</td>
                        <td><span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $vehicle->status }}</span></td>
                        <td>{{ \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote)['primary_formatted'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No hay publicaciones recientes.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="dashboard-grid dashboard-grid--three-up">
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Planes</p><h2>Oferta comercial</h2><p>Consulta la estructura activa para vendedores y revisa cada plan con más contexto.</p><a href="{{ route('admin.plans') }}" class="button button--solid">Ver planes</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Catálogo</p><h2>Marcas y modelos</h2><p>Administra altas, est?dos y crecimiento del catálogo desde una sola vista.</p><a href="{{ route('admin.catalog') }}" class="button button--solid">Abrir catálogo</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Usuarios</p><h2>Compradores y vendedores</h2><p>Consulta cuentas, actividad reciente y comportamiento comercial en un solo módulo.</p><a href="{{ route('admin.users') }}" class="button button--solid">Ver usuarios</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Ajustes</p><h2>Configuración general</h2><p>Controla tema público, tasa de cambio y parámetros clave del sitio.</p><a href="{{ route('admin.settings') }}" class="button button--solid">Abrir ajustes</a></article>
</section>
@endsection
