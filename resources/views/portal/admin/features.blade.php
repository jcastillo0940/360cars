@extends('layouts.portal')

@section('title', 'Características | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Características configurables')
@section('portal-copy', 'Administra las opciones que aparecen como checklist en el flujo del vendedor desde una vista dedicada y mucho más clara.')

@section('header-actions')
    <a href="#features" class="button button--solid">Crear característica</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Características</span>
        <strong>{{ $featureStats['total'] }}</strong>
        <p>Inscritas en el sistema.</p>
    </article>
    <article class="metric-card">
        <span>Disponibles</span>
        <strong>{{ $featureStats['active'] }}</strong>
        <p>Opciones visibles para publicar.</p>
    </article>
    <article class="metric-card">
        <span>Segmentos</span>
        <strong>{{ $featureStats['categories'] }}</strong>
        <p>Categorías de equipamiento.</p>
    </article>
</section>

<section class="dashboard-panel reveal reveal--delay-1" id="features" style="margin-top: 1.5rem;">
    <div class="panel-heading">
        <div><p class="portal-kicker">Catálogo</p><h2>Características del Inventario</h2></div>
        <button onclick="document.getElementById('new-feature-form').scrollIntoView({behavior:'smooth'})" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Añadir nueva</button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-top: 1rem;">
        @foreach ($featureOptions->groupBy('category') as $category => $group)
            <article style="background: var(--portal-soft); border: 1px solid var(--portal-border); border-radius: 8px; padding: 1.5rem;">
                <h3 style="margin: 0 0 1rem 0; font-size: 1rem; color: var(--portal-primary); text-transform: uppercase; letter-spacing: 0.05em;">{{ str($category)->replace('-', ' ') }}</h3>
                <div class="list-stack">
                    @foreach ($group as $feature)
                        <div class="list-row" style="padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div style="flex: 1;">
                                <strong style="font-size: 0.9rem;">{{ $feature->name }}</strong>
                                <p style="margin:0; font-size: 0.7rem; color: var(--portal-muted);">Orden: {{ $feature->sort_order }}</p>
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <span class="status-badge {{ $feature->is_active ? 'status-badge--success' : '' }}" style="font-size: 0.6rem;">{{ $feature->is_active ? 'ON' : 'OFF' }}</span>
                                <form method="POST" action="{{ route('admin.feature-options.toggle', $feature) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost" style="padding: 0.2rem 0.4rem; min-height:0; font-size: 0.65rem;">Toggle</button></form>
                                <form method="POST" action="{{ route('admin.feature-options.destroy', $feature) }}" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button type="submit" class="button button--ghost" style="padding: 0.2rem 0.4rem; min-height:0; font-size: 0.65rem; color: var(--portal-warn);">&times;</button></form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="dashboard-panel reveal reveal--delay-2" id="new-feature-form" style="margin-top: 1.5rem;">
    <div class="panel-heading"><div><p class="portal-kicker">Configuración</p><h2>Nueva Característica</h2></div></div>
    <form method="POST" action="{{ route('admin.feature-options.store') }}" class="portal-form">
        @csrf
        <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr auto; align-items: flex-end; gap: 1rem;">
            <label class="form-field"><span>Nombre de la opción</span><input type="text" name="name" placeholder="Ej. Pantalla Táctil" required /></label>
            <label class="form-field"><span>Categoría</span><input type="text" name="category" list="feature-category-list" placeholder="Seguridad, Interior..." required /></label>
            <label class="form-field"><span>Prioridad (Orden)</span><input type="number" name="sort_order" min="0" value="0" /></label>
            <button type="submit" class="button button--solid">Crear Registro</button>
        </div>
        <datalist id="feature-category-list">
            @foreach ($featureCategories as $category)
                <option value="{{ $category }}"></option>
            @endforeach
        </datalist>
    </form>
</section>
@endsection

