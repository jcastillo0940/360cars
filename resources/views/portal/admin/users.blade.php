@extends('layouts.portal')

@section('title', 'Usuarios | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Usuarios, roles y actividad')
@section('portal-copy', 'Consulta cuentas, roles y comportamiento reciente en una vista clara para operación y soporte.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}">Catálogo</a>
    <a href="{{ route('admin.features') }}">Características</a>
    <a href="{{ route('admin.plans') }}">Planes</a>
    <a href="{{ route('admin.news') }}">Noticias</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}" class="is-active">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Usuarios nuevos</span><strong>{{ $newUsers }}</strong><p>Altas del día.</p></article>
    <article class="metric-card"><span>Total publicaciones</span><strong>{{ $vehicleCount }}</strong><p>Inventario creado por la comunidad.</p></article>
    <article class="metric-card"><span>Contactos</span><strong>{{ $leadCount }}</strong><p>Interés comercial acumulado.</p></article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Filtros</p><h2>Buscar usuarios</h2></div></div>
    <form method="GET" action="{{ route('admin.users') }}" class="portal-form portal-form--inline">
        <div class="form-grid">
            <label class="form-field"><span>Buscar</span><input type="text" name="q" value="{{ $userFilters['q'] }}" placeholder="Nombre o correo" /></label>
            <label class="form-field"><span>Rol</span><select name="role"><option value="">Todos</option><option value="buyer" @selected($userFilters['role']==='buyer')>Comprador</option><option value="seller" @selected($userFilters['role']==='seller')>Vendedor</option><option value="dealer" @selected($userFilters['role']==='dealer')>Dealer</option><option value="admin" @selected($userFilters['role']==='admin')>Administrador</option></select></label>
        </div>
        <div class="form-actions"><button type="submit" class="button button--solid">Filtrar</button><a href="{{ route('admin.users') }}" class="button button--ghost">Limpiar</a></div>
    </form>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Cuentas</p><h2>Usuarios recientes</h2></div><span class="status-badge">{{ $latestUsers->total() }} cuentas</span></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Usuario</th><th>Rol</th><th>Verificado</th><th>Alta</th></tr></thead>
            <tbody>
            @forelse ($latestUsers as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></td>
                    <td>{{ ucfirst($user->account_type->value) }}</td>
                    <td>{{ $user->is_verified ? 'Sí' : 'No' }}</td>
                    <td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No hay usuarios registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $latestUsers->links() }}</div>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Actividad</p><h2>Publicaciones recientes por propietario</h2></div><span class="status-badge">{{ $latestVehicles->total() }} registros</span></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Vehículo</th><th>Propietario</th><th>Estado</th><th>Ciudad</th></tr></thead>
            <tbody>
            @forelse ($latestVehicles as $vehicle)
                <tr>
                    <td><strong>{{ $vehicle->title }}</strong><span>{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }}</span></td>
                    <td>{{ $vehicle->owner?->email }}</td>
                    <td><span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $vehicle->status }}</span></td>
                    <td>{{ $vehicle->city }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No hay publicaciones recientes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $latestVehicles->links() }}</div>
</section>
@endsection
