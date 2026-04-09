@extends('layouts.portal')

@section('title', 'Administración | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Centro de control del marketplace')
@section('portal-copy', 'Supervisa inventario, pagos, usuarios y configuraciones clave desde un panel claro, comercial y fácil de operar.')

@section('header-actions')
    <a href="{{ route('admin.catalog') }}" class="button button--solid">Ver catálogo</a>
    <a href="{{ route('admin.settings') }}" class="button button--ghost">Ajustes</a>
@endsection



@section('content')
<section class="dashboard-grid">
    <article class="metric-card reveal">
        <span>GMV @if($selectedYear) ({{ $selectedYear }}) @endif</span>
        <strong>${{ number_format($gmv, 0) }}</strong>
        <p>Pagos confirmados acumulados.</p>
    </article>
    <article class="metric-card reveal reveal--delay-1">
        <span>Conversión Lead/Auto</span>
        <strong>{{ round(($leadCount / max(1, $publishedVehicleCount)), 1) }}</strong>
        <p>Promedio de interesados por unidad.</p>
    </article>
    <article class="metric-card reveal reveal--delay-2">
        <span>Crecimiento Hoy</span>
        <strong>+{{ $newUsers }}</strong>
        <p>Nuevos registros de usuarios.</p>
    </article>
    <article class="metric-card reveal reveal--delay-3">
        <span>Inventario Activo</span>
        <strong>{{ $publishedVehicleCount }}</strong>
        <p>Vehículos listados actualmente.</p>
    </article>
</section>

<section class="panel-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-top: 1.5rem;">
    <article class="dashboard-panel reveal">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Rendimiento Mensual</p>
                <h2>Tendencia de Ingresos</h2>
            </div>
            <span class="pill">{{ $selectedYear }}</span>
        </div>
        <div class="mini-bars">
            @foreach ($paymentTrendChart as $item)
                <div class="mini-bars__item">
                    <div class="mini-bars__columns">
                        <div class="mini-bars__bar mini-bars__bar--yellow" style="height: {{ $item['height'] }}%"></div>
                    </div>
                    <strong>{{ $item['label'] }}</strong>
                </div>
            @endforeach
        </div>
    </article>

    <article class="dashboard-panel reveal reveal--delay-1">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Crecimiento Mensual</p>
                <h2>Publicaciones Nuevas</h2>
            </div>
            <span class="pill">{{ $selectedYear }}</span>
        </div>
        <div class="mini-bars">
            @foreach ($inventoryTrendChart as $item)
                <div class="mini-bars__item">
                    <div class="mini-bars__columns">
                        <div class="mini-bars__bar mini-bars__bar--blue" style="height: {{ $item['height'] }}%"></div>
                    </div>
                    <strong>{{ $item['label'] }}</strong>
                </div>
            @endforeach
        </div>
    </article>
</section>

<section class="panel-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
    <article class="dashboard-panel reveal">
        <div class="panel-heading">
            <div><h2>Distribución por Marca</h2></div>
        </div>
        <div class="list-stack">
            @foreach ($topBrands as $brand)
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <strong>{{ $brand['label'] }}</strong>
                        <span class="pill">{{ $brand['count'] }} autos ({{ $brand['percentage'] }}%)</span>
                    </div>
                    <div style="width: 100%; height: 8px; background: var(--portal-soft); border-radius: 99px; overflow: hidden;">
                        <div style="width: {{ $brand['percentage'] }}%; height: 100%; background: var(--portal-primary);"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </article>

    <article class="dashboard-panel reveal reveal--delay-1">
        <div class="panel-heading">
            <div><h2>Transacciones del Período</h2></div>
            <a href="{{ route('admin.payments') }}" class="text-link">Ver todas</a>
        </div>
        <div class="table-shell" style="max-height: 280px; overflow-y: auto;">
            <table class="portal-table">
                <thead><tr><th>Ref</th><th>Plan</th><th>Monto</th></tr></thead>
                <tbody>
                @forelse ($latestTransactions as $transaction)
                    <tr>
                        <td><strong>{{ $transaction->external_reference }}</strong><span>{{ $transaction->user?->email }}</span></td>
                        <td>{{ $transaction->plan?->name }}</td>
                        <td>${{ number_format((float) $transaction->amount, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Sin actividad reciente.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="dashboard-grid dashboard-grid--four-up" style="margin-top: 1.5rem;">
    <article class="dashboard-panel panel-link-card"><h3>Planes</h3><p>Oferta comercial.</p><a href="{{ route('admin.plans') }}" class="button button--solid">Ver</a></article>
    <article class="dashboard-panel panel-link-card"><h3>Catálogo</h3><p>Marcas/Modelos.</p><a href="{{ route('admin.catalog') }}" class="button button--solid">Ver</a></article>
    <article class="dashboard-panel panel-link-card"><h3>Noticias</h3><p>Blog oficial.</p><a href="{{ route('admin.news') }}" class="button button--solid">Ver</a></article>
    <article class="dashboard-panel panel-link-card"><h3>Ajustes</h3><p>Sistema global.</p><a href="{{ route('admin.settings') }}" class="button button--solid">Ver</a></article>
</section>
@endsection
