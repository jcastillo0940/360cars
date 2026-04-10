@extends('layouts.portal')

@section('title', 'Vendedor | Movikaa')
@section('portal-eyebrow', 'Vendedor')
@section('portal-title', 'Panel comercial del vendedor')
@section('portal-copy', 'Publica, renueva, organiza tu inventario y da seguimiento a tus contactos desde una experiencia simple y enfocada en vender mejor.')

@section('header-actions')
    <a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Nuevo anuncio</a>
    @if (config('app.enable_payments'))
    <a href="{{ route('seller.billing') }}" class="button button--ghost">Plan y pagos</a>
    @endif
@endsection

@section('content')
@if ($draftCount > 0)
<section class="dashboard-panel reveal" style="background: var(--portal-primary-soft); border-color: var(--portal-primary); margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 2rem; padding: 1.5rem 2rem;">
    <div>
        <h3 style="margin: 0; font-family: var(--font-headline); color: var(--portal-primary); font-size: 1.25rem;">Tienes un auto sin publicar</h3>
        <p style="margin: 0.25rem 0 0; font-size: 0.95rem; color: var(--portal-muted);">Detectamos que dejaste un borrador a medias. ¿Deseas continuar editándolo para que salga al inventario?</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('seller.listings', ['status' => 'draft']) }}" class="button button--solid">Continuar editando</a>
    </div>
</section>
@endif

<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Anuncios Activos</span>
        <strong>{{ $activeListingsCount }}</strong>
        <p>{{ $publishedCount }} públicos, {{ $pausedCount }} pausados.</p>
    </article>
    <article class="metric-card">
        <span>Interés Total</span>
        <strong>{{ $leadCount }}</strong>
        <p>Contactos y consultas recibidas.</p>
    </article>
    <article class="metric-card">
        <span>Renovaciones</span>
        <strong>{{ $freeRenewableCount }}</strong>
        <p>Básicos listos para reposicionarse.</p>
    </article>
    <article class="metric-card">
        <span>Impacto Visual</span>
        <strong>{{ number_format($viewCount) }}</strong>
        <p>Vistas totales en el marketplace.</p>
    </article>
</section>

<section class="dashboard-grid dashboard-grid--three-up">
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Publicar</p><h2>Crea un nuevo anuncio</h2><p>Registra otra unidad y deja el contenido listo para salir al inventario.</p><a href="{{ route('seller.onboarding.create') }}" class="button button--solid">Crear anuncio</a></article>
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Inventario</p><h2>Gestiona tus publicaciones</h2><p>Filtra estados, revisa resultados y entra a editar cada publicación desde una vista más ordenada.</p><a href="{{ route('seller.listings') }}" class="button button--solid">Ver publicaciones</a></article>
    @if (config('app.enable_payments'))
    <article class="dashboard-panel panel-link-card"><p class="portal-kicker">Planes</p><h2>Visibilidad y pagos</h2><p>Consulta tu plan actual y revisa opciones para dar más alcance a tus anuncios.</p><a href="{{ route('seller.billing') }}" class="button button--solid">Ver planes</a></article>
    @endif
</section>
@endsection

@if(session('onboarding_finished'))
    <script>
        (function() {
            sessionStorage.removeItem('seller-onboarding-draft');
            if ('indexedDB' in window) {
                const request = window.indexedDB.open('movikaa-autosave', 1);
                request.onsuccess = function() {
                    const db = request.result;
                    if (db.objectStoreNames.contains('draft-files')) {
                        const transaction = db.transaction('draft-files', 'readwrite');
                        const store = transaction.objectStore('draft-files');
                        const keysRequest = store.getAllKeys();
                        keysRequest.onsuccess = function() {
                            (keysRequest.result || []).forEach(key => {
                                if (String(key).startsWith('seller-onboarding-draft:')) store.delete(key);
                            });
                        };
                    }
                };
            }
        })();
    </script>
@endif


