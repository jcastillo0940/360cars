@extends('layouts.portal')

@section('title', 'Usuarios admin | Movikaa')
@section('portal-eyebrow', 'Admin usuarios')
@section('portal-title', 'Usuarios, roles y actividad')
@section('portal-copy', 'Buyer, seller, dealer y admin en una vista clara para operacion y soporte.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Overview</a>
    <a href="{{ route('admin.catalog') }}">Catalogo</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}" class="is-active">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Usuarios nuevos</span><strong>{{ $newUsers }}</strong><p>Altas del dia.</p></article>
    <article class="metric-card"><span>Total publicaciones</span><strong>{{ $vehicleCount }}</strong><p>Inventario creado por la comunidad.</p></article>
    <article class="metric-card"><span>Leads</span><strong>{{ $leadCount }}</strong><p>Interes comercial acumulado.</p></article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Filtros</p><h2>Buscar usuarios</h2></div></div>
    <form method="GET" action="{{ route('admin.users') }}" class="portal-form portal-form--inline">
        <div class="form-grid">
            <label class="form-field"><span>Buscar</span><input type="text" name="q" value="{{ $userFilters['q'] }}" placeholder="Nombre o correo" /></label>
            <label class="form-field"><span>Rol</span><select name="role"><option value="">Todos</option><option value="buyer" @selected($userFilters['role']==='buyer')>Buyer</option><option value="seller" @selected($userFilters['role']==='seller')>Seller</option><option value="dealer" @selected($userFilters['role']==='dealer')>Dealer</option><option value="admin" @selected($userFilters['role']==='admin')>Admin</option></select></label>
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
                    <td>{{ $user->account_type->value }}</td>
                    <td>{{ $user->is_verified ? 'Si' : 'No' }}</td>
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
    <div class="panel-heading"><div><p class="portal-kicker">Actividad</p><h2>Publicaciones recientes por owner</h2></div><span class="status-badge">{{ $latestVehicles->total() }} listings</span></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Vehiculo</th><th>Owner</th><th>Estado</th><th>Ciudad</th></tr></thead>
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
