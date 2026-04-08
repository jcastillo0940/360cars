@extends('layouts.portal')

@section('title', 'Planes | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Planes disponibles')
@section('portal-copy', 'Consulta la estructura comercial activa para vendedores en una vista dedicada y mucho más fácil de leer.')

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}">Catálogo</a>
    <a href="{{ route('admin.features') }}">Características</a>
    <a href="{{ route('admin.plans') }}" class="is-active">Planes</a>
    <a href="{{ route('admin.news') }}">Noticias</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Total</span><strong>{{ $planStats['total'] }}</strong><p>Planes registrados.</p></article>
    <article class="metric-card"><span>Activos</span><strong>{{ $planStats['active'] }}</strong><p>Disponibles para contratar.</p></article>
    <article class="metric-card"><span>Pagos</span><strong>{{ $planStats['paid'] }}</strong><p>Con precio mayor a cero.</p></article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Oferta</p><h2>Planes visibles</h2></div></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Plan</th><th>Precio</th><th>Publicaciones</th><th>Fotos</th><th>Extras</th><th>Estado</th></tr></thead>
            <tbody>
            @foreach ($plans as $plan)
                <tr>
                    <td><strong>{{ $plan->name }}</strong><span>{{ $plan->description }}</span></td>
                    <td>${{ number_format((float) $plan->price, 0) }}</td>
                    <td>{{ $plan->max_active_listings ?? 'Ilimitadas' }}</td>
                    <td>{{ $plan->photo_limit ?? 'Ilimitadas' }}</td>
                    <td>{{ $plan->allows_video ? 'Video' : 'Sin video' }} · {{ $plan->allows_360 ? '360' : 'Sin 360' }}</td>
                    <td><span class="status-badge {{ $plan->is_active ? 'status-badge--success' : '' }}">{{ $plan->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
