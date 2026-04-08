@extends('layouts.portal')

@section('title', 'Características | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Características configurables')
@section('portal-copy', 'Administra las opciones que aparecen como checklist en el flujo del vendedor desde una vista dedicada y mucho más clara.')

@section('header-actions')
    <a href="#features" class="button button--solid">Crear característica</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}">Catálogo</a>
    <a href="{{ route('admin.features') }}" class="is-active">Características</a>
    <a href="{{ route('admin.plans') }}">Planes</a>
    <a href="{{ route('admin.news') }}">Noticias</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Total</span><strong>{{ $featureStats['total'] }}</strong><p>Características registradas.</p></article>
    <article class="metric-card"><span>Activas</span><strong>{{ $featureStats['active'] }}</strong><p>Visibles en el flujo de publicación.</p></article>
    <article class="metric-card"><span>Categorías</span><strong>{{ $featureStats['categories'] }}</strong><p>Grupos actualmente utilizados.</p></article>
</section>

<section class="panel-grid panel-grid--admin-overview" id="features">
    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Nueva opción</p><h2>Crear característica</h2></div></div>
        <form method="POST" action="{{ route('admin.feature-options.store') }}" class="portal-form">
            @csrf
            <div class="form-grid">
                <label class="form-field"><span>Nombre</span><input type="text" name="name" placeholder="Ej. Cámara 360" required /></label>
                <label class="form-field"><span>Categoría</span><input type="text" name="category" list="feature-category-list" placeholder="equipamiento" required /></label>
                <label class="form-field form-field--wide"><span>Descripción</span><input type="text" name="description" placeholder="Opcional" /></label>
                <label class="form-field"><span>Orden</span><input type="number" name="sort_order" min="0" max="9999" value="0" /></label>
            </div>
            <datalist id="feature-category-list">
                @foreach ($featureCategories as $category)
                    <option value="{{ $category }}"></option>
                @endforeach
            </datalist>
            <div class="form-actions"><button type="submit" class="button button--solid">Crear característica</button></div>
        </form>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading"><div><p class="portal-kicker">Listado</p><h2>Checklist activo</h2></div></div>
        <div class="catalog-stack mt-4">
            @forelse ($featureOptions->groupBy('category') as $category => $group)
                <article class="catalog-block">
                    <div class="catalog-block__header"><div><strong>{{ str($category)->replace('-', ' ')->title() }}</strong><p>{{ $group->count() }} opciones</p></div></div>
                    <div class="table-shell mt-4">
                        <table class="portal-table">
                            <thead><tr><th>Nombre</th><th>Descripción</th><th>Orden</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                            @foreach ($group as $feature)
                                <tr>
                                    <td><strong>{{ $feature->name }}</strong><span>{{ $feature->slug }}</span></td>
                                    <td>{{ $feature->description ?: 'Sin descripción' }}</td>
                                    <td>{{ $feature->sort_order }}</td>
                                    <td><span class="status-badge {{ $feature->is_active ? 'status-badge--success' : '' }}">{{ $feature->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <form method="POST" action="{{ route('admin.feature-options.toggle', $feature) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost">{{ $feature->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                                            <form method="POST" action="{{ route('admin.feature-options.destroy', $feature) }}" onsubmit="return confirm('¿Seguro que deseas eliminar est? característica?')">@csrf @method('DELETE')<button type="submit" class="button button--ghost-danger">Eliminar</button></form>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <form method="POST" action="{{ route('admin.feature-options.update', $feature) }}" class="portal-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-grid">
                                                <label class="form-field"><span>Nombre</span><input type="text" name="name" value="{{ $feature->name }}" required /></label>
                                                <label class="form-field"><span>Categoría</span><input type="text" name="category" value="{{ $feature->category }}" list="feature-category-list" required /></label>
                                                <label class="form-field form-field--wide"><span>Descripción</span><input type="text" name="description" value="{{ $feature->description }}" placeholder="Opcional" /></label>
                                                <label class="form-field"><span>Orden</span><input type="number" name="sort_order" min="0" max="9999" value="{{ $feature->sort_order }}" /></label>
                                            </div>
                                            <div class="form-actions"><button type="submit" class="button button--solid">Guardar cambios</button></div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <div class="empty-state"><strong>Sin características</strong><p>Todavía no has configurado opciones para el checklist del vendedor.</p></div>
            @endforelse
        </div>
    </article>
</section>
@endsection
