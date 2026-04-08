@extends('layouts.portal')

@section('title', 'Catálogo | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Marca y modelo en una sola vista')
@section('portal-copy', 'Crea registros más rápido y administra el catálogo desde una tabla clara, sin saltar entre bloques separados.')

@section('header-actions')
    <a href="#catalog-create" class="button button--solid">Crear registro</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}" class="is-active">Catálogo</a>
    <a href="{{ route('admin.features') }}">Características</a>
    <a href="{{ route('admin.plans') }}">Planes</a>
    <a href="{{ route('admin.news') }}">Noticias</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
<div class="portal-note-card">
    <span class="portal-kicker">Catálogo vivo</span>
    <strong>{{ $catalogStats['makes_total'] }} marcas</strong>
    <p>{{ $catalogStats['models_total'] }} modelos listos para filtros y publicaciones.</p>
</div>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Marcas activas</span><strong>{{ $catalogStats['makes_active'] }}</strong><p>De {{ $catalogStats['makes_total'] }} registradas.</p></article>
    <article class="metric-card"><span>Modelos activos</span><strong>{{ $catalogStats['models_active'] }}</strong><p>De {{ $catalogStats['models_total'] }} registrados.</p></article>
    <article class="metric-card"><span>Búsqueda actual</span><strong>{{ $catalogSearch !== '' ? $catalogSearch : 'General' }}</strong><p>Filtra por marca o modelo.</p></article>
</section>

<section class="panel-grid panel-grid--admin-overview" id="catalog-create">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Alta rápida</p><h2>Crear marca y primer modelo</h2></div></div>
        <form method="POST" action="{{ route('admin.catalog.entries.store') }}" class="portal-form">
            @csrf
            <div class="form-grid">
                <label class="form-field"><span>Marca nueva</span><input type="text" name="make_name" placeholder="Ej. Changan" required /></label>
                <label class="form-field"><span>Primer modelo</span><input type="text" name="model_name" placeholder="Ej. CS55 Plus (opcional)" /></label>
            </div>
            <div class="form-actions"><button type="submit" class="button button--solid">Crear registro</button></div>
        </form>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Modelo adicional</p><h2>Agregar modelo a una marca existente</h2></div></div>
        <form method="POST" action="{{ route('admin.catalog.models.store') }}" class="portal-form">
            @csrf
            <div class="form-grid">
                <label class="form-field"><span>Marca</span><select name="vehicle_make_id" required>@foreach ($catalogMakes as $make)<option value="{{ $make->id }}">{{ $make->name }}</option>@endforeach</select></label>
                <label class="form-field"><span>Modelo</span><input type="text" name="name" placeholder="Ej. Tiggo 7 Pro" required /></label>
            </div>
            <div class="form-actions"><button type="submit" class="button button--solid">Agregar modelo</button></div>
        </form>
    </article>
</section>

<section class="dashboard-panel" id="catalog-list">
    <div class="panel-heading"><div><p class="portal-kicker">Listado</p><h2>Catálogo activo</h2></div></div>
    <form method="GET" action="{{ route('admin.catalog') }}" class="portal-form portal-form--inline mt-4">
        <label class="form-field form-field--wide"><span>Marca o modelo</span><input type="text" name="q" value="{{ $catalogSearch }}" placeholder="Ej. Toyota, Corolla, Changan, Tiggo" /></label>
        <div class="form-actions"><button type="submit" class="button button--solid">Buscar</button><a href="{{ route('admin.catalog') }}" class="button button--ghost">Limpiar</a></div>
    </form>
    <div class="table-shell mt-4">
        <table class="portal-table">
            <thead><tr><th>Marca</th><th>Modelos</th><th>Activos</th><th>Estado marca</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse ($catalogMakes as $make)
                <tr>
                    <td><strong>{{ $make->name }}</strong><span>{{ $make->models->count() }} modelos registrados</span></td>
                    <td>{{ $make->models->pluck('name')->join(', ') ?: 'Sin modelos' }}</td>
                    <td>{{ $make->models->where('is_active', true)->count() }} / {{ $make->models->count() }}</td>
                    <td><span class="status-badge {{ $make->is_active ? 'status-badge--success' : '' }}">{{ $make->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                    <td><div class="table-actions"><form method="POST" action="{{ route('admin.catalog.makes.toggle', $make) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost">{{ $make->is_active ? 'Desactivar marca' : 'Activar marca' }}</button></form></div></td>
                </tr>
                @foreach ($make->models as $model)
                    <tr>
                        <td><span style="padding-left:1rem; display:inline-block;">↳ {{ $model->name }}</span></td>
                        <td>{{ $make->name }}</td>
                        <td>Modelo individual</td>
                        <td><span class="status-badge {{ $model->is_active ? 'status-badge--success' : '' }}">{{ $model->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                        <td><div class="table-actions"><form method="POST" action="{{ route('admin.catalog.models.toggle', $model) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost">{{ $model->is_active ? 'Desactivar' : 'Activar' }}</button></form></div></td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5">No encontramos resultados. Prueba con otra marca o limpia el filtro.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
