@extends('layouts.portal')

@section('title', 'Noticias | Movikaa')
@section('portal-eyebrow', 'Contenido editorial')
@section('portal-title', 'Noticias y blog')
@section('portal-copy', 'Crea, edita, publica y destaca artículos desde un módulo dedicado, con estructura de blog y lectura pública.')

@section('header-actions')
    <a href="{{ route('admin.news.create') }}" class="button button--solid">Nuevo artículo</a>
@endsection

@section('content')
<section class="dashboard-grid reveal">
    <article class="metric-card">
        <span>Artículos Totales</span>
        <strong>{{ $newsStats['total'] }}</strong>
        <p>En el repositorio editorial.</p>
    </article>
    <article class="metric-card">
        <span>Edición Pública</span>
        <strong>{{ $newsStats['published'] }}</strong>
        <p>Visibles en el blog principal.</p>
    </article>
    <article class="metric-card">
        <span>Destacados</span>
        <strong>{{ $newsStats['featured'] }}</strong>
        <p>Con prioridad en el feed.</p>
    </article>
</section>

<section class="dashboard-panel reveal reveal--delay-1">
    <div class="panel-heading"><div><p class="portal-kicker">Filtrar</p><h2>Búsqueda Editorial</h2></div></div>
    <form method="GET" action="{{ route('admin.news') }}" class="portal-form">
        <div class="form-grid" style="grid-template-columns: 2fr 1fr auto; align-items: flex-end; gap: 1rem;">
            <label class="form-field"><span>Título o Contenido</span><input type="text" name="q" value="{{ $newsFilters['q'] }}" placeholder="Ej. Consejos para vender..." /></label>
            <label class="form-field"><span>Estado</span><select name="status"><option value="">Todos</option><option value="draft" @selected($newsFilters['status']==='draft')>Borrador</option><option value="published" @selected($newsFilters['status']==='published')>Publicado</option></select></label>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="button button--solid">Buscar</button>
                <a href="{{ route('admin.news') }}" class="button button--ghost">Reset</a>
            </div>
        </div>
    </form>
</section>

<section class="dashboard-panel reveal reveal--delay-2" id="news-list" style="margin-top: 1.5rem;">
    <div class="panel-heading">
        <div><p class="portal-kicker">Gesti?n</p><h2>Contenido del Blog</h2></div>
        <a href="{{ route('news.index') }}" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;" target="_blank">Ver blog público</a>
    </div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Artículo</th><th>Estado</th><th>Autor</th><th>Publicado</th><th style="text-align: right;">Acciones</th></tr></thead>
            <tbody>
            @forelse ($newsPosts as $post)
                <tr>
                    <td>
                        <strong style="color: var(--portal-primary); font-size: 0.95rem;">{{ $post->title }}</strong>
                        <p style="margin:0; font-size: 0.7rem; color: var(--portal-muted);">/{{ $post->slug }}</p>
                    </td>
                    <td>
                        <span class="status-badge {{ $post->status === 'published' ? 'status-badge--success' : '' }}">
                            {{ $post->status === 'published' ? 'Publicado' : 'Borrador' }}
                        </span>
                        @if($post->is_featured) <span class="pill pill--success" style="font-size: 0.6rem; margin-left: 0.25rem;">Destacado</span> @endif
                    </td>
                    <td><span style="font-size: 0.85rem;">{{ $post->author?->name ?? 'Admin' }}</span></td>
                    <td><span style="font-size: 0.85rem; color: var(--portal-muted);">{{ optional($post->published_at)->format('d/m/Y') ?? 'Pendiente' }}</span></td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                            <a href="{{ route('admin.news.edit', $post) }}" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem;">Editar</a>
                            <form method="POST" action="{{ route('admin.news.destroy', $post) }}" onsubmit="return confirm('¿Eliminar artículo?')" style="display:inline;">@csrf @method('DELETE')<button type="submit" class="button button--ghost" style="padding: 0.25rem 0.5rem; min-height: 0; font-size: 0.75rem; color: var(--portal-warn);">Borrar</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; padding: 4rem;">No hay artículos todavía. Empieza a crear contenido para tu comunidad.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $newsPosts->links() }}</div>
</section>
@endsection

