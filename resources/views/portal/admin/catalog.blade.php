@extends('layouts.portal')

@section('title', 'Catalogo admin | Movikaa')
@section('portal-eyebrow', 'Admin catalogo')
@section('portal-title', 'Administra marcas y modelos')
@section('portal-copy', 'Esta vista ya incluye busqueda para que trabajar con cientos de marcas y modelos no se vuelva una pantalla interminable.')

@section('header-actions')
    <a href="#catalog-create" class="button button--solid">Crear elementos</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Overview</a>
    <a href="{{ route('admin.catalog') }}" class="is-active">Catalogo</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
<div class="portal-note-card">
    <span class="portal-kicker">Busqueda rapida</span>
    <strong>{{ $catalogStats['makes_total'] }} marcas</strong>
    <p>{{ $catalogStats['models_total'] }} modelos listos para filtrar, crear o desactivar.</p>
</div>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Marcas activas</span><strong>{{ $catalogStats['makes_active'] }}</strong><p>De {{ $catalogStats['makes_total'] }} registradas.</p></article>
    <article class="metric-card"><span>Modelos activos</span><strong>{{ $catalogStats['models_active'] }}</strong><p>De {{ $catalogStats['models_total'] }} registrados.</p></article>
    <article class="metric-card"><span>Busqueda actual</span><strong>{{ $catalogSearch !== '' ? $catalogSearch : 'General' }}</strong><p>Filtra por marca o modelo desde esta misma vista.</p></article>
</section>

<section class="panel-grid panel-grid--admin-overview" id="catalog-create">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Buscar</p><h2>Filtrar catalogo</h2></div></div>
        <form method="GET" action="{{ route('admin.catalog') }}" class="portal-form portal-form--inline">
            <label class="form-field form-field--wide"><span>Marca o modelo</span><input type="text" name="q" value="{{ $catalogSearch }}" placeholder="Ej. Toyota, Corolla, Changan, Tiggo" /></label>
            <div class="form-actions"><button type="submit" class="button button--solid">Buscar</button><a href="{{ route('admin.catalog') }}" class="button button--ghost">Limpiar</a></div>
        </form>
    </article>
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Nueva marca</p><h2>Crear marca</h2></div></div>
        <form method="POST" action="{{ route('admin.catalog.makes.store') }}" class="portal-form">
            @csrf
            <label class="form-field"><span>Nombre</span><input type="text" name="name" placeholder="Ej. Changan" required /></label>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar marca</button></div>
        </form>
    </article>
</section>

<section class="panel-grid panel-grid--admin-overview">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Nuevo modelo</p><h2>Crear modelo</h2></div></div>
        <form method="POST" action="{{ route('admin.catalog.models.store') }}" class="portal-form">
            @csrf
            <div class="form-grid">
                <label class="form-field"><span>Marca</span><select name="vehicle_make_id" required>@foreach ($catalogMakes as $make)<option value="{{ $make->id }}">{{ $make->name }}</option>@endforeach</select></label>
                <label class="form-field"><span>Modelo</span><input type="text" name="name" placeholder="Ej. Tiggo 7 Pro" required /></label>
            </div>
            <div class="form-actions"><button type="submit" class="button button--solid">Guardar modelo</button></div>
        </form>
    </article>

    <article class="dashboard-panel" id="catalog-list">
        <div class="panel-heading"><div><p class="portal-kicker">Listado</p><h2>Catalogo activo</h2></div></div>
        <div class="catalog-stack">
            @forelse ($catalogMakes as $make)
                <article class="catalog-block">
                    <div class="catalog-block__header">
                        <div>
                            <strong>{{ $make->name }}</strong>
                            <p>{{ $make->models->where('is_active', true)->count() }} modelos activos de {{ $make->models->count() }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.catalog.makes.toggle', $make) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost">{{ $make->is_active ? 'Desactivar marca' : 'Activar marca' }}</button></form>
                    </div>
                    <div class="chip-grid">
                        @forelse ($make->models as $model)
                            <div class="chip-card">
                                <div><strong>{{ $model->name }}</strong><p>{{ $model->is_active ? 'Activo' : 'Inactivo' }}</p></div>
                                <form method="POST" action="{{ route('admin.catalog.models.toggle', $model) }}">@csrf @method('PATCH')<button type="submit" class="text-link">{{ $model->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                            </div>
                        @empty
                            <p class="empty-copy">Sin modelos registrados.</p>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="empty-state"><strong>No encontramos resultados.</strong><p>Prueba con otra marca o limpia el filtro.</p></div>
            @endforelse
        </div>
    </article>
</section>
@endsection
