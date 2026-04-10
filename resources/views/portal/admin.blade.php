@extends('layouts.portal')

@section('title', 'Admin Portal | Movikaa')
@section('portal-eyebrow', 'Administración')
@section('portal-title', 'Panel de Control Principal')

@section('header-actions')
    <button type="button" class="button button--solid" data-tab-trigger="moderation">Pendientes</button>
@endsection

@section('sidebar')
    <nav class="portal-nav" data-tabs>
        <p class="muted-label" style="padding: 0 1rem; margin-bottom: 0.5rem;">Principal</p>
        <a href="#overview" class="is-active" data-tab-trigger="overview">
            <span class="nav-icon">ðŸ“ˆ</span>
            <span>Resumen General</span>
        </a>
        <a href="#payments" data-tab-trigger="payments">
            <span class="nav-icon">ðŸ’°</span>
            <span>Pagos y Finanzas</span>
        </a>
        <a href="#moderation" data-tab-trigger="moderation">
            <span class="nav-icon">ðŸ›¡ï¸</span>
            <span>Moderación de Autos</span>
        </a>

        <p class="muted-label" style="padding: 0 1rem; margin-top: 1.5rem; margin-bottom: 0.5rem;">Gestión</p>
        <a href="#users" data-tab-trigger="users">
            <span class="nav-icon">ðŸ‘¥</span>
            <span>Control de Usuarios</span>
        </a>
        <a href="#catalog" data-tab-trigger="catalog">
            <span class="nav-icon">ðŸ“š</span>
            <span>Catálogo (Marcas/Mod)</span>
        </a>

        <p class="muted-label" style="padding: 0 1rem; margin-top: 1.5rem; margin-bottom: 0.5rem;">Sistema</p>
        <a href="#config" data-tab-trigger="config">
            <span class="nav-icon">âš™ï¸</span>
            <span>Configuración Global</span>
        </a>
    </nav>
    
    <div class="portal-note">
        <p class="muted-label">Estado del Inventario</p>
        <p><strong>{{ $publishedVehicleCount }}</strong> unidades publicadas.</p>
        <p><strong>{{ $paidTransactionsCount }}</strong> ventas cerradas.</p>
    </div>
@endsection

@section('content')
<div data-tabs>
    {{-- TAB: OVERVIEW --}}
    <div data-tab-panel="overview" class="reveal">
        <section class="dashboard-grid">
            <article class="metric-card">
                <span>GMV Total</span>
                <strong>${{ number_format($gmv, 0) }}</strong>
                <p>Calculado desde transacciones pagadas.</p>
            </article>
            <article class="metric-card">
                <span>Usuarios Hoy</span>
                <strong>{{ $newUsers }}</strong>
                <p>Nuevos registros en las últimas 24h.</p>
            </article>
            <article class="metric-card">
                <span>Moderación Pendiente</span>
                <strong class="{{ $pendingModeration > 0 ? 'text-warning' : '' }}">{{ $pendingModeration }}</strong>
                <p>Listings esperando revisión.</p>
            </article>
        </section>

        <section class="dashboard-panel mt-4">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Marketplace Health</p>
                    <h2>Salud Operativa</h2>
                </div>
            </div>
            <div class="control-strip">
                <article class="control-card">
                    <span class="muted-label">Total Vehículos</span>
                    <strong>{{ $vehicleCount }}</strong>
                    <p>{{ $publishedVehicleCount }} están activos.</p>
                </article>
                <article class="control-card">
                    <span class="muted-label">Leads Generados</span>
                    <strong>{{ $leadCount }}</strong>
                    <p>Interacciones directas.</p>
                </article>
                <article class="control-card">
                    <span class="muted-label">Suscripciones</span>
                    <strong>{{ $activeSubscriptions->count() }}</strong>
                    <p>Con renovación activa.</p>
                </article>
            </div>
        </section>
    </div>

    {{-- TAB: PAYMENTS --}}
    <div data-tab-panel="payments" class="reveal" hidden>
        <article class="dashboard-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Finanzas</p>
                    <h2>Transacciones Recientes</h2>
                </div>
            </div>
            <div class="table-shell">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Orden / Ref</th>
                            <th>Usuario</th>
                            <th>Plan</th>
                            <th>Estado</th>
                            <th>Monto</th>
                            <th>Método</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestTransactions as $transaction)
                            <tr>
                                <td><strong>{{ $transaction->external_reference }}</strong><span>{{ $transaction->provider }}</span></td>
                                <td>{{ $transaction->user?->email }}</td>
                                <td>{{ $transaction->plan?->name }}</td>
                                <td><span class="status-badge {{ $transaction->status === 'paid' ? 'status-badge--success' : 'status-badge--warn' }}">{{ $transaction->status }}</span></td>
                                <td>${{ number_format((float) $transaction->amount, 0) }}</td>
                                <td>{{ $transaction->payment_method }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-state">No hay transacciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    {{-- TAB: MODERATION --}}
    <div data-tab-panel="moderation" class="reveal" hidden>
        <article class="dashboard-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Listings</p>
                    <h2>Control de Calidad e Inventario</h2>
                </div>
            </div>
            <div class="table-shell">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Vendedor</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Fotos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestVehicles as $vehicle)
                            <tr>
                                <td><strong>{{ $vehicle->title }}</strong><span>{{ $vehicle->make?->name }} · {{ $vehicle->model?->name }}</span></td>
                                <td>{{ $vehicle->owner?->email }}</td>
                                <td><span class="status-badge {{ $vehicle->status === 'published' ? 'status-badge--success' : ($vehicle->status === 'draft' ? 'status-badge--warn' : '') }}">{{ $vehicle->status }}</span></td>
                                <td><strong>{{ \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote)['primary_formatted'] }}</strong></td>
                                <td>{{ $vehicle->media()->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    {{-- TAB: USERS --}}
    <div data-tab-panel="users" class="reveal" hidden>
        <article class="dashboard-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Usuarios</p>
                    <h2>Gestión de Cuentas</h2>
                </div>
            </div>
            <div class="table-shell">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Nombre / Email</th>
                            <th>Tipo de Cuenta</th>
                            <th>Verif.</th>
                            <th>Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestUsers as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></td>
                                <td><span class="pill">{{ $user->account_type->value }}</span></td>
                                <td>{{ $user->is_verified ? 'âœ“' : 'âœ—' }}</td>
                                <td>{{ optional($user->created_at)->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    {{-- TAB: CATALOG --}}
    <div data-tab-panel="catalog" class="reveal" hidden>
        <section class="dashboard-grid">
            <div class="dashboard-panel">
                <div class="panel-heading"><h2>Nueva Marca</h2></div>
                <form method="POST" action="{{ route('admin.dashboard') }}" class="portal-form">
                    @csrf
                    <div class="form-field">
                        <label>Nombre</label>
                        <input type="text" name="name" placeholder="Ej. Toyota" required />
                    </div>
                    <button type="submit" class="button button--solid">Añadir Marca</button>
                </form>
            </div>
            <div class="dashboard-panel">
                <div class="panel-heading"><h2>Nuevo Modelo</h2></div>
                <form method="POST" action="{{ route('admin.dashboard') }}" class="portal-form">
                    @csrf
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Marca</label>
                            <select name="vehicle_make_id" required>
                                @foreach ($catalogMakes as $make)
                                    <option value="{{ $make->id }}">{{ $make->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Modelo</label>
                            <input type="text" name="name" placeholder="Ej. Corolla" required />
                        </div>
                    </div>
                    <button type="submit" class="button button--solid">Añadir Modelo</button>
                </form>
            </div>
        </section>

        <section class="dashboard-panel mt-4">
            <div class="panel-heading"><h2>Inventario de Marcas</h2></div>
            <div class="feature-checklist">
                @foreach ($catalogMakes as $make)
                    <div>
                        <div>
                            <strong>{{ $make->name }}</strong>
                            <p>{{ $make->models->count() }} modelos registrados.</p>
                        </div>
                        <form method="POST" action="{{ route('admin', $make) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="button button--ghost">{{ $make->is_active ? 'Desactivar' : 'Activar' }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- TAB: CONFIG --}}
    <div data-tab-panel="config" class="reveal" hidden>
        <section class="form-grid">
            {{-- EXCHANGE RATE --}}
            <article class="dashboard-panel">
                <div class="panel-heading"><h2>Tipo de Cambio</h2></div>
                <div class="control-strip">
                    <div class="control-card">
                        <span class="muted-label">USD a CRC</span>
                        <strong>{{ number_format((float) data_get($exchangeQuote, 'usd_to_crc', 0), 2) }}</strong>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.dashboard') }}">
                    @csrf
                    <button type="submit" class="button button--ghost">Forzar Actualización</button>
                </form>
            </article>

            {{-- THEME & UI --}}
            <article class="dashboard-panel">
                <div class="panel-heading"><h2>Tema Visual</h2></div>
                <form method="POST" action="{{ route('admin.dashboard') }}" class="portal-form">
                    @csrf
                    <div class="form-field">
                        <label>Modo de color (Home)</label>
                        <select name="public_theme">
                            <option value="light" @selected($publicTheme === 'light')>Claridad (Light)</option>
                            <option value="dark" @selected($publicTheme === 'dark')>Minimalismo (Dark)</option>
                        </select>
                    </div>
                    <button type="submit" class="button button--solid">Guardar Preferencia</button>
                </form>
            </article>

            {{-- VALUATION AI --}}
            <article class="dashboard-panel form-field--wide">
                <div class="panel-heading"><h2>Motor de Valuación IA</h2></div>
                <form method="POST" action="{{ route('admin.dashboard') }}" class="portal-form">
                    @csrf
                    <div class="form-field">
                        <label class="inline-check">
                            <input type="checkbox" name="valuation_ai_enabled" value="1" @checked($valuationAiEnabled) />
                            <span>Habilitar sugerencias basadas en IA para vendedores</span>
                        </label>
                    </div>
                    <p class="form-field small">Configuración actual: {{ $valuationAiConfigured ? 'âœ“ Conectado a API' : 'âœ— Sin API Key' }}</p>
                    <button type="submit" class="button button--solid">Guardar Configuración IA</button>
                </form>
            </article>
        </section>
    </div>
</div>

<script>
    // Simple inline logic if standard tabs don't fire or to handle initial state/hash
    document.addEventListener('DOMContentLoaded', () => {
        const triggers = document.querySelectorAll('[data-tab-trigger]');
        const panels = document.querySelectorAll('[data-tab-panel]');
        
        triggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const target = trigger.getAttribute('data-tab-trigger');
                
                // Update Sidebar
                document.querySelectorAll('.portal-nav a').forEach(a => a.classList.remove('is-active'));
                trigger.closest('a')?.classList.add('is-active');
                
                // Update Panels
                panels.forEach(panel => {
                    panel.hidden = panel.getAttribute('data-tab-panel') !== target;
                });
            });
        });
        
        // Handle hash if present
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            const initialTrigger = document.querySelector(`[data-tab-trigger="${hash}"]`);
            if (initialTrigger) initialTrigger.click();
        }
    });
</script>
@endsection





