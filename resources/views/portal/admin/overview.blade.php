@extends('layouts.portal')

@section('title', 'Admin | Movikaa')
@section('portal-eyebrow', 'Admin overview')
@section('portal-title', 'Centro de control del marketplace')
@section('portal-copy', 'Resumen ejecutivo de planes seller, publicaciones, usuarios y salud operativa. Cada modulo vive en su propia vista para trabajar con menos friccion.')

@section('header-actions')
    <a href="{{ route('admin.catalog') }}" class="button button--solid">Catalogo</a>
    <a href="{{ route('admin.settings') }}" class="button button--ghost">Configuracion</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Overview</a>
    <a href="{{ route('admin.catalog') }}" class="{{ request()->routeIs('admin.catalog') ? 'is-active' : '' }}">Catalogo</a>
    <a href="{{ route('admin.payments') }}" class="{{ request()->routeIs('admin.payments') ? 'is-active' : '' }}">Pagos</a>
    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'is-active' : '' }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'is-active' : '' }}">Ajustes</a>
</nav>
<div class="portal-note-card">
    <span class="portal-kicker">Mercado activo</span>
    <strong>{{ $publishedVehicleCount }} publicaciones</strong>
    <p>{{ $paidTransactionsCount }} pagos confirmados y {{ $leadCount }} leads acumulados.</p>
</div>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>GMV acumulado</span><strong>${{ number_format($gmv, 0) }}</strong><p>Calculado desde transacciones pagadas de planes seller.</p></article>
    <article class="metric-card"><span>Usuarios nuevos hoy</span><strong>{{ $newUsers }}</strong><p>Altas creadas en la jornada actual.</p></article>
    <article class="metric-card"><span>Moderacion pendiente</span><strong>{{ $pendingModeration }}</strong><p>Drafts o pausados esperando decision.</p></article>
    <article class="metric-card"><span>Vehiculos activos</span><strong>{{ $publishedVehicleCount }}</strong><p>Inventario publicado y visible para contacto buyer-seller.</p></article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Ingreso mensual</p><h2>Tendencia de pagos</h2></div></div>
        <div class="mini-bars">
            @foreach ($paymentTrendChart as $item)
                <div class="mini-bars__item">
                    <div class="mini-bars__columns mini-bars__columns--single">
                        <span class="mini-bars__bar mini-bars__bar--yellow" style="height: {{ $item['height'] }}%"></span>
                    </div>
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
                    <div class="mini-bars__columns mini-bars__columns--single">
                        <span class="mini-bars__bar mini-bars__bar--blue" style="height: {{ $item['height'] }}%"></span>
                    </div>
                    <strong>{{ $item['label'] }}</strong>
                    <small>{{ $item['value'] }} autos</small>
                </div>
            @endforeach
        </div>
    </article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Pagos recientes</p><h2>Pagos recientes</h2></div><a href="{{ route('admin.payments') }}" class="text-link">Ver modulo</a></div>
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
                    <tr><td colspan="5">No hay transacciones registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Moderacion</p><h2>Ultimos listings</h2></div><a href="{{ route('admin.users') }}" class="text-link">Ver usuarios</a></div>
        <div class="table-shell">
            <table class="portal-table">
                <thead><tr><th>Listing</th><th>Owner</th><th>Estado</th><th>Precio</th></tr></thead>
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
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Planes</p><h2>Planes disponibles</h2><p>Consulta la oferta comercial activa para sellers y el impacto de cada paquete en visibilidad.</p><a href="{{ route('admin.payments') }}" class="button button--solid">Ver planes y pagos</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Catalogo</p><h2>Marcas y modelos</h2><p>Busca, crea y activa el catalogo desde una vista pensada para escalar.</p><a href="{{ route('admin.catalog') }}" class="button button--solid">Ir al catalogo</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Usuarios</p><h2>Buyer y seller</h2><p>Visualiza cuentas, actividad reciente y las publicaciones que mueven conversion.</p><a href="{{ route('admin.users') }}" class="button button--solid">Ver usuarios</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Ajustes</p><h2>Frontend, IA y moneda</h2><p>Controla tema publico, tasa de cambio, planes y extras del onboarding.</p><a href="{{ route('admin.settings') }}" class="button button--solid">Abrir ajustes</a></article>
</section>
@endsection
