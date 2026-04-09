@extends('layouts.portal')

@section('title', 'Catálogo | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Marca y modelo en una sola vista')
@section('portal-copy', 'Crea registros más rápido y administra el catálogo desde una tabla clara, sin saltar entre bloques separados.')

@section('header-actions')
    <a href="#catalog-create" class="button button--solid">Crear registro</a>
@endsection

@section('content')
<!-- Full-width Catalog Header -->
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; gap: 1rem;">
    <div>
        <h2 style="margin: 0;">Catálogo de Vehículos</h2>
        <p style="color: var(--portal-muted); margin: 0; font-size: 0.9rem;">
            Existen <strong>{{ $catalogStats['makes_total'] }}</strong> marcas y <strong>{{ $catalogStats['models_total'] }}</strong> modelos operativos.
        </p>
    </div>
    
    <div style="display: flex; gap: 0.75rem;">
        <form method="GET" action="{{ route('admin.catalog') }}" class="header-search" style="margin:0; width: 300px;">
            <input type="text" name="q" value="{{ $catalogSearch }}" placeholder="Buscar por marca o modelo..." class="header-search__input">
        </form>
        <button onclick="document.getElementById('catalog-forms').scrollIntoView({behavior:'smooth'})" class="button button--solid">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Nuevo Registro
        </button>
    </div>
</div>

<!-- Full-width List -->
<article class="dashboard-panel reveal">
    <div class="table-shell" style="max-height: 500px; overflow-y: auto;">
        <table class="portal-table">
            <thead>
                <tr>
                    <th style="width: 40px;"></th>
                    <th>Marca</th>
                    <th>Modelos Registrados</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($catalogMakes as $make)
                <tr style="background: rgba(255,255,255,0.01);">
                    <td>
                        <div style="width: 32px; height: 32px; border-radius: 4px; background: rgba(246, 162, 26, 0.1); border: 1px solid rgba(246, 162, 26, 0.2); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--portal-primary); font-size: 0.9rem;">
                            {{ substr($make->name, 0, 1) }}
                        </div>
                    </td>
                    <td><strong>{{ $make->name }}</strong></td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @forelse($make->models as $model)
                                <span class="pill" style="font-size: 0.75rem; background: var(--portal-soft); border-color: rgba(255,255,255,0.05);">
                                    {{ $model->name }}
                                    <form method="POST" action="{{ route('admin.catalog.models.toggle', $model) }}" style="display:inline; margin-left: 4px;">
                                        @csrf @method('PATCH')
                                        <button type="submit" style="background:none; border:none; color: {{ $model->is_active ? 'var(--portal-primary)' : 'var(--portal-muted)' }}; cursor:pointer; padding:0; font-size: 0.6rem;">●</button>
                                    </form>
                                </span>
                            @empty
                                <span style="font-style: italic; color: var(--portal-muted); font-size: 0.85rem;">Sin modelos.</span>
                            @endforelse
                        </div>
                    </td>
                    <td><span class="status-badge {{ $make->is_active ? 'status-badge--success' : '' }}">{{ $make->is_active ? 'Visible' : 'Oculta' }}</span></td>
                    <td style="text-align: right;">
                        <form method="POST" action="{{ route('admin.catalog.makes.toggle', $make) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.8rem;">
                                {{ $make->is_active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; padding: 3rem;">No se encontraron resultados para "{{ $catalogSearch }}"</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</article>

<!-- Bottom Forms (Compact Grid) -->
<section id="catalog-forms" class="panel-grid" style="grid-template-columns: repeat(2, 1fr); margin-top: 2rem;">
    <article class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Registro Global</p>
                <h2>Nueva Marca</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.catalog.entries.store') }}" class="portal-form">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-field">
                    <span>Marca</span>
                    <input type="text" name="make_name" placeholder="Ej. BMW..." required />
                </div>
                <div class="form-field">
                    <span>Modelo Inicial</span>
                    <input type="text" name="model_name" placeholder="Opcional..." />
                </div>
            </div>
            <button type="submit" class="button button--solid" style="width: 100%; margin-top: 1rem;">Crear Marca</button>
        </form>
    </article>

    <article class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Mantenimiento</p>
                <h2>Nuevo Modelo</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.catalog.models.store') }}" class="portal-form">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-field">
                    <span>Elegir Marca</span>
                    <select name="vehicle_make_id" required>
                        @foreach ($catalogMakes as $make)
                            <option value="{{ $make->id }}">{{ $make->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <span>Nombre Modelo</span>
                    <input type="text" name="name" placeholder="Ej. X5..." required />
                </div>
            </div>
            <button type="submit" class="button button--solid" style="width: 100%; margin-top: 1rem;">Añadir Modelo</button>
        </form>
    </article>
</section>
@endsection
