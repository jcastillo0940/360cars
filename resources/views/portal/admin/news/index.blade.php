@extends('layouts.portal')

@section('title', 'Noticias | Movikaa')
@section('portal-eyebrow', 'Contenido editorial')
@section('portal-title', 'Noticias y blog')
@section('portal-copy', 'Crea, edita, publica y dest?ca artículos desde un módulo dedicado, con estructura de blog y lectura pública.')

@section('header-actions')
    <a href="{{ route('admin.news.create') }}" class="button button--solid">Nuevo artículo</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}">Catálogo</a>
    <a href="{{ route('admin.features') }}">Características</a>
    <a href="{{ route('admin.plans') }}">Planes</a>
    <a href="{{ route('admin.news') }}" class="is-active">Noticias</a>
    <a href="{{ route('admin.payments') }}">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="dashboard-grid">
    <article class="metric-card"><span>Total</span><strong>{{ $newsStats['total'] }}</strong><p>Artículos registrados.</p></article>
    <article class="metric-card"><span>Publicados</span><strong>{{ $newsStats['published'] }}</strong><p>Visibles en el blog público.</p></article>
    <article class="metric-card"><span>Dest?cados</span><strong>{{ $newsStats['featured'] }}</strong><p>Con prioridad visual en el listado.</p></article>
</section>

<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Filtros</p><h2>Buscar artículos</h2></div></div>
    <form method="GET" action="{{ route('admin.news') }}" class="portal-form portal-form--inline">
        <div class="form-grid">
            <label class="form-field"><span>Buscar</span><input type="text" name="q" value="{{ $newsFilters['q'] }}" placeholder="Título, slug o resumen" /></label>
            <label class="form-field"><span>Estado</span><select name="status"><option value="">Todos</option><option value="draft" @selected($newsFilters['status']==='draft')>Borrador</option><option value="published" @selected($newsFilters['status']==='published')>Publicado</option></select></label>
        </div>
        <div class="form-actions"><button type="submit" class="button button--solid">Filtrar</button><a href="{{ route('admin.news') }}" class="button button--ghost">Limpiar</a></div>
    </form>
</section>

<section class="dashboard-panel" id="news-list">
    <div class="panel-heading"><div><p class="portal-kicker">Listado</p><h2>Artículos creados</h2></div><a href="{{ route('news.index') }}" class="text-link" target="_blank" rel="noreferrer">Ver blog público</a></div>
    <div class="table-shell">
        <table class="portal-table">
            <thead><tr><th>Artículo</th><th>Estado</th><th>Autor</th><th>Fecha</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse ($newsPosts as $post)
                <tr>
                    <td><strong>{{ $post->title }}</strong><span>{{ $post->slug }}</span></td>
                    <td><span class="status-badge {{ $post->status === 'published' ? 'status-badge--success' : '' }}">{{ $post->status === 'published' ? 'Publicado' : 'Borrador' }}</span>@if($post->is_featured)<span class="status-badge" style="margin-left:.5rem;">Dest?cado</span>@endif</td>
                    <td>{{ $post->author?->name ?? 'Equipo Movikaa' }}</td>
                    <td>{{ optional($post->published_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                    <td><div class="table-actions"><a href="{{ route('admin.news.edit', $post) }}" class="button button--ghost">Editar</a>@if($post->status === 'published')<a href="{{ route('news.show', $post->slug) }}" class="button button--ghost" target="_blank" rel="noreferrer">Ver</a>@endif<form method="POST" action="{{ route('admin.news.destroy', $post) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este artículo?')">@csrf @method('DELETE')<button type="submit" class="button button--ghost-danger">Eliminar</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="5">Todavía no hay artículos. Crea el primero para activar el blog público.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-shell">{{ $newsPosts->links() }}</div>
</section>
@endsection
