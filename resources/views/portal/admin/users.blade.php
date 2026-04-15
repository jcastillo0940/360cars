@extends('layouts.portal')

@section('title', 'Usuarios | Movikaa')
@section('portal-eyebrow', 'Administracion')
@section('portal-title', 'CRUD de usuarios y vehiculos')
@section('portal-copy', 'Administra cuentas y publicaciones desde un solo panel con acciones de crear, editar, activar, pausar y eliminar.')

@section('content')
@php
    $editingUserModel = $editingUser ?? null;
    $editingVehicleModel = $editingVehicle ?? null;
@endphp

<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Nuevos Miembros</span>
        <strong>{{ $newUsers }}</strong>
        <p>Altas registradas hoy.</p>
    </article>
    <article class="metric-card">
        <span>Inventario Global</span>
        <strong>{{ $vehicleCount }}</strong>
        <p>Publicaciones en todo el sistema.</p>
    </article>
    <article class="metric-card">
        <span>Interes Generado</span>
        <strong>{{ $leadCount }}</strong>
        <p>Contactos realizados por compradores.</p>
    </article>
</section>

<section class="dashboard-panel reveal reveal--delay-1" id="user-form">
    <div class="panel-heading">
        <div>
            <p class="portal-kicker">Usuarios</p>
            <h2>{{ $editingUserModel ? 'Editar usuario' : 'Crear usuario' }}</h2>
        </div>
        @if ($editingUserModel)
            <a href="{{ route('admin.users') }}#user-form" class="button button--ghost">Nuevo usuario</a>
        @endif
    </div>

    <form method="POST" action="{{ $editingUserModel ? route('admin.users.update', $editingUserModel) : route('admin.users.store') }}" class="portal-form">
        @csrf
        @if ($editingUserModel)
            @method('PUT')
        @endif
        <div class="form-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">
            <label class="form-field"><span>Nombre</span><input type="text" name="name" value="{{ old('name', $editingUserModel->name ?? '') }}" required /></label>
            <label class="form-field"><span>Email</span><input type="email" name="email" value="{{ old('email', $editingUserModel->email ?? '') }}" required /></label>
            <label class="form-field"><span>Rol</span>
                <select name="account_type" required>
                    @foreach ($userRoleOptions as $role)
                        <option value="{{ $role }}" @selected(old('account_type', $editingUserModel?->account_type?->value ?? 'buyer') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-field"><span>Telefono</span><input type="text" name="phone" value="{{ old('phone', $editingUserModel->phone ?? '') }}" /></label>
            <label class="form-field"><span>WhatsApp</span><input type="text" name="whatsapp_phone" value="{{ old('whatsapp_phone', $editingUserModel->whatsapp_phone ?? '') }}" /></label>
            <label class="form-field"><span>Pais</span><input type="text" name="country_code" maxlength="2" value="{{ old('country_code', $editingUserModel->country_code ?? 'CR') }}" /></label>
            <label class="form-field"><span>Empresa</span><input type="text" name="company_name" value="{{ old('company_name', $editingUserModel->company_name ?? '') }}" /></label>
            <label class="form-field"><span>Agencia</span><input type="text" name="agency_name" value="{{ old('agency_name', $editingUserModel->agency_name ?? '') }}" /></label>
            <label class="form-field"><span>{{ $editingUserModel ? 'Nueva clave (opcional)' : 'Clave' }}</span><input type="password" name="password" {{ $editingUserModel ? '' : 'required' }} /></label>
        </div>
        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-top:1rem;">
            <label style="display:flex; align-items:center; gap:0.5rem;"><input type="hidden" name="is_verified" value="0"><input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $editingUserModel->is_verified ?? false))> Verificado</label>
            <label style="display:flex; align-items:center; gap:0.5rem;"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editingUserModel->is_active ?? true))> Activo</label>
        </div>
        <div style="display:flex; gap:0.75rem; margin-top:1rem; flex-wrap:wrap;">
            <button type="submit" class="button button--solid">{{ $editingUserModel ? 'Guardar usuario' : 'Crear usuario' }}</button>
            @if ($editingUserModel)
                <a href="{{ route('admin.users') }}#user-form" class="button button--ghost">Cancelar</a>
            @endif
        </div>
    </form>
</section>

<section class="dashboard-panel reveal reveal--delay-2">
    <div class="panel-heading"><div><p class="portal-kicker">Filtrar</p><h2>Busqueda de usuarios</h2></div></div>
    <form method="GET" action="{{ route('admin.users') }}" class="portal-form">
        <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr auto; align-items: flex-end; gap: 1rem;">
            <label class="form-field"><span>Nombre, correo o telefono</span><input type="text" name="q" value="{{ $userFilters['q'] }}" placeholder="Ej. Juan Perez" /></label>
            <label class="form-field"><span>Rol</span><select name="role"><option value="">Todos</option>@foreach ($userRoleOptions as $role)<option value="{{ $role }}" @selected($userFilters['role'] === $role)>{{ ucfirst($role) }}</option>@endforeach</select></label>
            <label class="form-field"><span>Estado</span><select name="status"><option value="">Todos</option><option value="active" @selected($userFilters['status'] === 'active')>Activos</option><option value="inactive" @selected($userFilters['status'] === 'inactive')>Inactivos</option></select></label>
            <div style="display:flex; gap:0.5rem;"><button type="submit" class="button button--solid">Buscar</button><a href="{{ route('admin.users') }}#users-list" class="button button--ghost">Reset</a></div>
        </div>
    </form>
</section>

<section class="dashboard-panel reveal reveal--delay-3" id="users-list">
    <div class="panel-heading">
        <div><p class="portal-kicker">Comunidad</p><h2>Usuarios</h2></div>
        <span class="status-badge">{{ $latestUsers->total() }} cuentas</span>
    </div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Verificacion</th><th>Registro</th><th style="text-align:right;">Acciones</th></tr></thead>
            <tbody>
            @forelse ($latestUsers as $user)
                <tr>
                    <td>
                        <strong>{{ $user->name ?: 'Usuario nuevo' }}</strong>
                        <p style="margin:0; font-size:0.75rem; color:var(--portal-muted);">{{ $user->email }}</p>
                        @if ($user->phone)
                            <p style="margin:0; font-size:0.75rem; color:var(--portal-muted);">{{ $user->phone }}</p>
                        @endif
                    </td>
                    <td><span class="pill" style="text-transform: capitalize;">{{ $user->account_type->value }}</span></td>
                    <td><span class="status-badge {{ $user->is_active ? 'status-badge--success' : 'status-badge--warn' }}">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                    <td><span class="status-badge {{ $user->is_verified ? 'status-badge--success' : '' }}">{{ $user->is_verified ? 'Verificado' : 'Pendiente' }}</span></td>
                    <td><span style="font-size:0.85rem; color:var(--portal-muted);">{{ optional($user->created_at)->format('d/m/Y') }}</span></td>
                    <td>
                        <div style="display:flex; justify-content:flex-end; gap:0.5rem; flex-wrap:wrap;">
                            <a href="{{ route('admin.users', array_merge(request()->query(), ['edit_user' => $user->id])) }}#user-form" class="button button--ghost" style="padding:0.35rem 0.6rem; min-height:0;">Editar</a>
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost" style="padding:0.35rem 0.6rem; min-height:0;">{{ $user->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                            @if (auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar usuario y sus registros relacionados?');">@csrf @method('DELETE')<button type="submit" class="button button--ghost" style="padding:0.35rem 0.6rem; min-height:0; color:var(--portal-warn);">Eliminar</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center; padding:3rem;">No hay usuarios registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $latestUsers->links() }}</div>
</section>

<section class="dashboard-panel reveal reveal--delay-4" id="vehicle-form">
    <div class="panel-heading">
        <div>
            <p class="portal-kicker">Vehiculos</p>
            <h2>{{ $editingVehicleModel ? 'Editar vehiculo' : 'Crear vehiculo' }}</h2>
        </div>
        @if ($editingVehicleModel)
            <a href="{{ route('admin.users') }}#vehicle-form" class="button button--ghost">Nuevo vehiculo</a>
        @endif
    </div>

    <form method="POST" action="{{ $editingVehicleModel ? route('admin.vehicles.update', $editingVehicleModel) : route('admin.vehicles.store') }}" class="portal-form">
        @csrf
        @if ($editingVehicleModel)
            @method('PUT')
        @endif
        <div class="form-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">
            <label class="form-field"><span>Propietario</span>
                <select name="user_id" required>
                    @foreach ($vehicleOwners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) old('user_id', $editingVehicleModel->user_id ?? '') === (string) $owner->id)>{{ $owner->name }} · {{ $owner->email }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-field"><span>Marca</span>
                <select name="vehicle_make_id" required>
                    @foreach ($catalogMakes as $make)
                        <option value="{{ $make->id }}" @selected((int) old('vehicle_make_id', $editingVehicleModel->vehicle_make_id ?? 0) === $make->id)>{{ $make->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-field"><span>Modelo</span>
                <select name="vehicle_model_id" required>
                    @foreach ($catalogMakes as $make)
                        @foreach ($make->models as $model)
                            <option value="{{ $model->id }}" @selected((int) old('vehicle_model_id', $editingVehicleModel->vehicle_model_id ?? 0) === $model->id)>{{ $make->name }} · {{ $model->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </label>
            <label class="form-field"><span>Titulo</span><input type="text" name="title" value="{{ old('title', $editingVehicleModel->title ?? '') }}" required /></label>
            <label class="form-field"><span>Ano</span><input type="number" name="year" value="{{ old('year', $editingVehicleModel->year ?? now()->year) }}" required /></label>
            <label class="form-field"><span>Precio</span><input type="number" step="0.01" name="price" value="{{ old('price', $editingVehicleModel->price ?? '') }}" required /></label>
            <label class="form-field"><span>Moneda</span><input type="text" name="currency" maxlength="3" value="{{ old('currency', $editingVehicleModel->currency ?? 'CRC') }}" /></label>
            <label class="form-field"><span>Ciudad</span><input type="text" name="city" value="{{ old('city', $editingVehicleModel->city ?? '') }}" /></label>
            <label class="form-field"><span>Condicion</span><select name="condition" required><option value="used" @selected(old('condition', $editingVehicleModel->condition ?? 'used') === 'used')>Usado</option><option value="new" @selected(old('condition', $editingVehicleModel->condition ?? 'used') === 'new')>Nuevo</option></select></label>
            <label class="form-field"><span>Carroceria</span><input type="text" name="body_type" value="{{ old('body_type', $editingVehicleModel->body_type ?? '') }}" required /></label>
            <label class="form-field"><span>Combustible</span><input type="text" name="fuel_type" value="{{ old('fuel_type', $editingVehicleModel->fuel_type ?? '') }}" required /></label>
            <label class="form-field"><span>Transmision</span><input type="text" name="transmission" value="{{ old('transmission', $editingVehicleModel->transmission ?? '') }}" required /></label>
            <label class="form-field"><span>Estado</span><select name="status" required>@foreach ($vehicleStatusOptions as $status)<option value="{{ $status }}" @selected(old('status', $editingVehicleModel->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
            <label class="form-field"><span>Plan</span><select name="publication_tier" required>@foreach ($vehicleTierOptions as $tier)<option value="{{ $tier }}" @selected(old('publication_tier', $editingVehicleModel->publication_tier ?? 'basic') === $tier)>{{ ucfirst($tier) }}</option>@endforeach</select></label>
        </div>
        <label class="form-field" style="margin-top:1rem;"><span>Descripcion</span><textarea name="description" rows="4" required>{{ old('description', $editingVehicleModel->description ?? '') }}</textarea></label>
        <div style="display:flex; gap:0.75rem; margin-top:1rem; flex-wrap:wrap;">
            <button type="submit" class="button button--solid">{{ $editingVehicleModel ? 'Guardar vehiculo' : 'Crear vehiculo' }}</button>
            @if ($editingVehicleModel)
                <a href="{{ route('admin.users') }}#vehicle-form" class="button button--ghost">Cancelar</a>
            @endif
        </div>
    </form>
</section>

<section class="dashboard-panel reveal reveal--delay-5" id="vehicles">
    <div class="panel-heading">
        <div><p class="portal-kicker">Inventario</p><h2>Vehiculos</h2></div>
        <span class="status-badge">{{ $latestVehicles->total() }} registros</span>
    </div>
    <form method="GET" action="{{ route('admin.users') }}" class="portal-form" style="margin-bottom:1rem;">
        <div class="form-grid" style="grid-template-columns: 2fr 1fr auto; align-items:flex-end; gap:1rem;">
            <label class="form-field"><span>Vehiculo, ciudad o propietario</span><input type="text" name="vehicle_q" value="{{ $vehicleFilters['q'] }}" /></label>
            <label class="form-field"><span>Estado</span><select name="vehicle_status"><option value="">Todos</option>@foreach ($vehicleStatusOptions as $status)<option value="{{ $status }}" @selected($vehicleFilters['status'] === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
            <div style="display:flex; gap:0.5rem;"><button type="submit" class="button button--solid">Buscar</button><a href="{{ route('admin.users') }}#vehicles" class="button button--ghost">Reset</a></div>
        </div>
    </form>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Vehiculo</th><th>Propietario</th><th>Estado</th><th>Ubicacion</th><th style="text-align:right;">Acciones</th></tr></thead>
            <tbody>
            @forelse ($latestVehicles as $vehicle)
                <tr>
                    <td>
                        <strong>{{ $vehicle->title }}</strong>
                        <p style="margin:0; font-size:0.75rem; color:var(--portal-muted);">{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }} · {{ $vehicle->year }}</p>
                        <p style="margin:0; font-size:0.75rem; color:var(--portal-muted);">{{ number_format((float) $vehicle->price, 2) }} {{ $vehicle->currency }}</p>
                    </td>
                    <td><span style="font-size:0.85rem;">{{ $vehicle->owner?->email }}</span></td>
                    <td><span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : 'status-badge--warn' }}">{{ ucfirst($vehicle->status) }}</span></td>
                    <td><span style="font-size:0.85rem;">{{ $vehicle->city ?: 'No especificada' }}</span></td>
                    <td>
                        <div style="display:flex; justify-content:flex-end; gap:0.5rem; flex-wrap:wrap;">
                            <a href="{{ route('admin.users', array_merge(request()->query(), ['edit_vehicle' => $vehicle->id])) }}#vehicle-form" class="button button--ghost" style="padding:0.35rem 0.6rem; min-height:0;">Editar</a>
                            <form method="POST" action="{{ route('admin.vehicles.toggle', $vehicle) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost" style="padding:0.35rem 0.6rem; min-height:0;">{{ $vehicle->status === 'published' ? 'Pausar' : 'Publicar' }}</button></form>
                            <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}" onsubmit="return confirm('¿Eliminar vehiculo?');">@csrf @method('DELETE')<button type="submit" class="button button--ghost" style="padding:0.35rem 0.6rem; min-height:0; color:var(--portal-warn);">Eliminar</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center; padding:3rem;">No hay publicaciones recientes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $latestVehicles->links() }}</div>
</section>
@endsection
