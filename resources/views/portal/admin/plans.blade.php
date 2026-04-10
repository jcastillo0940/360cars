@extends('layouts.portal')

@section('title', 'Planes | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Planes disponibles')
@section('portal-copy', 'Consulta la estructura comercial activa para vendedores en una vista dedicada y mucho más fácil de leer.')

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Catálogo de Planes</span>
        <strong>{{ $planStats['total'] }}</strong>
        <p>Estructuras registradas.</p>
    </article>
    <article class="metric-card">
        <span>Oferta Vigente</span>
        <strong>{{ $planStats['active'] }}</strong>
        <p>Planes listos para contratar.</p>
    </article>
    <article class="metric-card">
        <span>Planes Premium</span>
        <strong>{{ $planStats['paid'] }}</strong>
        <p>Planes con costo directo.</p>
    </article>
</section>

<section class="dashboard-panel reveal reveal--delay-1">
    <div class="panel-heading"><div><p class="portal-kicker">Comercial</p><h2>Planes Vigentes</h2></div></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Nivel de Plan</th><th>Inversión</th><th>Límites</th><th>Media & Extras</th><th>Estado</th></tr></thead>
            <tbody>
            @foreach ($plans as $plan)
                <tr>
                    <td>
                        <strong style="font-size: 1.1rem; color: var(--portal-primary);">{{ $plan->name }}</strong>
                        <p style="margin:0; font-size: 0.8rem; color: var(--portal-muted);">{{ $plan->description }}</p>
                    </td>
                    <td><strong style="font-size: 1.2rem;">${{ number_format((float) $plan->price, 0) }}</strong><span style="font-size: 0.7rem; color: var(--portal-muted);"> / pago</span></td>
                    <td>
                        <div style="font-size: 0.85rem;">
                            <strong>{{ $plan->max_active_listings ?? 'âˆž' }}</strong> <small>Autos</small><br>
                            <strong>{{ $plan->photo_limit ?? 'âˆž' }}</strong> <small>Fotos / auto</small>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                            <span class="pill" style="font-size: 0.65rem;">{{ $plan->allows_video ? 'âœ“ Video' : 'âœ• Video' }}</span>
                            <span class="pill" style="font-size: 0.65rem;">{{ $plan->allows_360 ? 'âœ“ 360' : 'âœ• 360' }}</span>
                            @if($plan->featured_days > 0) <span class="pill pill--success" style="font-size: 0.65rem;">{{ $plan->featured_days }} días TOP</span> @endif
                        </div>
                    </td>
                    <td><span class="status-badge {{ $plan->is_active ? 'status-badge--success' : '' }}">{{ $plan->is_active ? 'Visible' : 'Oculto' }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection

