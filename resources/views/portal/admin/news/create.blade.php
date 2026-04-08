@extends('layouts.portal')

@section('title', 'Nuevo artículo | Movikaa')
@section('portal-eyebrow', 'Contenido editorial')
@section('portal-title', 'Crear artículo')
@section('portal-copy', 'Redacta un nuevo contenido para el blog público y decide si queda en borrador o publicado de inmediato.')

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
<section class="dashboard-panel">
    <div class="panel-heading"><div><p class="portal-kicker">Editor</p><h2>Nuevo artículo</h2></div></div>
    @include('portal.admin.news._form', ['action' => route('admin.news.store'), 'method' => 'POST', 'submitLabel' => 'Crear artículo', 'newsPost' => null])
</section>
@endsection
