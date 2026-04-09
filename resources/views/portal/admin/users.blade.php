@extends('layouts.portal')

@section('title', 'Usuarios | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Usuarios, roles y actividad')
@section('portal-copy', 'Consulta cuentas, roles y comportamiento reciente en una vista clara para operación y soporte.')

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Nuevos Miembros</span>
        <strong>{{ $newUsers }}</strong>
        <p>Altas registradas hoy.</p>
    </article>
    <article class="metric-card">
        <span>Inventario Global</span>
        <strong>{{ $vehicleCount }}</strong>
        <p>Publicaciones activas en total.</p>
    </article>
    <article class="metric-card">
        <span>Interés Generado</span>
        <strong>{{ $leadCount }}</strong>
        <p>Contactos realizados por compradores.</p>
    </article>
</section>

<section class="dashboard-panel reveal reveal--delay-1">
    <div class="panel-heading"><div><p class="portal-kicker">Filtrar</p><h2>Búsqueda de Miembros</h2></div></div>
    <form method="GET" action="{{ route('admin.users') }}" class="portal-form">
        <div class="form-grid" style="grid-template-columns: 2fr 1fr auto; align-items: flex-end; gap: 1rem;">
            <label class="form-field"><span>Nombre o Correo</span><input type="text" name="q" value="{{ $userFilters['q'] }}" placeholder="Ej. Juan Perez..." /></label>
            <label class="form-field"><span>Rol de Cuenta</span><select name="role"><option value="">Todos los roles</option><option value="buyer" @selected($userFilters['role']==='buyer')>Comprador</option><option value="seller" @selected($userFilters['role']==='seller')>Vendedor</option><option value="dealer" @selected($userFilters['role']==='dealer')>Dealer</option><option value="admin" @selected($userFilters['role']==='admin')>Admin</option></select></label>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="button button--solid">Buscar</button>
                <a href="{{ route('admin.users') }}" class="button button--ghost">Reset</a>
            </div>
        </div>
    </form>
</section>

<section class="dashboard-panel reveal reveal--delay-2">
    <div class="panel-heading">
        <div><p class="portal-kicker">Comunidad</p><h2>Usuarios Recientes</h2></div>
        <span class="status-badge">{{ $latestUsers->total() }} cuentas</span>
    </div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th style="width: 50px;"></th><th>Usuario / Email</th><th>Rol</th><th>Verificación</th><th style="text-align: right;">Registrado</th></tr></thead>
            <tbody>
            @forelse ($latestUsers as $user)
                <tr>
                    <td>
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--portal-soft); border: 1px solid var(--portal-border); display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--portal-ink); font-size: 0.8rem;">
                            {{ strtoupper(substr($user->name ?: $user->email, 0, 1)) }}
                        </div>
                    </td>
                    <td>
                        <strong>{{ $user->name ?: 'Usuario nuevo' }}</strong>
                        <p style="margin:0; font-size: 0.75rem; color: var(--portal-muted);">{{ $user->email }}</p>
                    </td>
                    <td><span class="pill" style="text-transform: capitalize;">{{ $user->account_type->value }}</span></td>
                    <td>
                        <span class="status-badge {{ $user->is_verified ? 'status-badge--success' : '' }}">
                            {{ $user->is_verified ? 'Verificado' : 'Pendiente' }}
                        </span>
                    </td>
                    <td style="text-align: right;"><span style="font-size: 0.85rem; color: var(--portal-muted);">{{ optional($user->created_at)->format('d/m/Y') }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; padding: 3rem;">No hay usuarios registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $latestUsers->links() }}</div>
</section>

<section class="dashboard-panel reveal reveal--delay-3" id="vehicles">
    <div class="panel-heading">
        <div><p class="portal-kicker">Inventario</p><h2>Últimos Autos Publicados</h2></div>
        <span class="status-badge">{{ $latestVehicles->total() }} registros</span>
    </div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Vehículo</th><th>Propietario</th><th>Estado</th><th style="text-align: right;">Ubicación</th></tr></thead>
            <tbody>
            @forelse ($latestVehicles as $vehicle)
                <tr>
                    <td>
                        <strong>{{ $vehicle->title }}</strong>
                        <p style="margin:0; font-size: 0.75rem; color: var(--portal-muted);">{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }}</p>
                    </td>
                    <td><span style="font-size: 0.85rem;">{{ $vehicle->owner?->email }}</span></td>
                    <td>
                        <span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : 'status-badge--warn' }}">
                            {{ $vehicle->status === 'published' ? 'Publicado' : 'Borrador' }}
                        </span>
                    </td>
                    <td style="text-align: right;"><span style="font-size: 0.85rem;">{{ $vehicle->city ?: 'No especificada' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; padding: 3rem;">No hay publicaciones recientes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $latestVehicles->links() }}</div>
</section>
@endsection
