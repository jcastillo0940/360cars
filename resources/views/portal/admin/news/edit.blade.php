@extends('layouts.portal')

@section('title', 'Editar artículo | Movikaa')
@section('portal-eyebrow', 'Contenido editorial')
@section('portal-title', 'Editar artículo')
@section('portal-copy', 'Actualiza contenido, SEO, portada y estado de publicación desde una sola vista.')

@section('header-actions')
    @if ($newsPost->status === 'published')
        <a href="{{ route('news.show', $newsPost->slug) }}" class="button button--ghost" target="_blank" rel="noreferrer">Ver artículo</a>
    @endif
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('admin.dashboard') }}">Resumen</a>
    <a href="{{ route('admin.catalog') }}">Catálogo</a>
    <a href="{{ route('admin.features') }}">Características</a>
    <a href="{{ route('admin.plans') }}">Planes</a>
    <a href="{{ route('admin.news') }}" class="is-active">Noticias</a>
    <a href="{{ (config('app.enable_payments') ? route('admin.payments') : route('admin.dashboard')) }}">Pagos</a>
    <a href="{{ route('admin.users') }}">Usuarios</a>
    <a href="{{ route('admin.settings') }}">Ajustes</a>
</nav>
@endsection

@section('content')
<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Editor</p><h2>{{ $newsPost->title }}</h2></div></div>
    @include('portal.admin.news._form', ['action' => route('admin.news.update', $newsPost), 'method' => 'PUT', 'submitLabel' => 'Guardar cambios', 'newsPost' => $newsPost])
</section>
@endsection

