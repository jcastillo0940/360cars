@extends('layouts.portal')

@section('title', 'Editar anuncio seller | Movikaa')
@section('portal-eyebrow', 'Seller edit')
@section('portal-title', 'Editar publicacion')
@section('portal-copy', 'Ajusta precio, visibilidad, descripcion y media desde una vista dedicada que te deja trabajar el anuncio como una ficha comercial completa.')

@section('header-actions')
    <a href="{{ route('seller.listings') }}" class="button button--ghost">Volver a publicaciones</a>
    <a href="{{ route('catalog.show', $editingVehicle->slug) }}" class="button button--solid">Ver anuncio</a>
@endsection

@section('sidebar')
<nav class="portal-nav">
    <a href="{{ route('seller.dashboard') }}">Overview</a>
    <a href="{{ route('seller.listings') }}" class="is-active">Publicaciones</a>
    <a href="{{ route('seller.onboarding.create') }}">Nuevo anuncio</a>
    <a href="{{ route('seller.media') }}">Media</a>
    <a href="{{ route('seller.messages') }}">Mensajes</a>
    <a href="{{ route('seller.billing') }}">Billing</a>
    <a href="{{ route('buyer.dashboard') }}">Actividad buyer</a>
</nav>
<div class="portal-note-card">
    <span class="portal-kicker">Editando</span>
    <strong>{{ $editingVehicle->title }}</strong>
    <p>{{ $editingVehicle->make?->name }} · {{ $editingVehicle->model?->name }} · {{ $editingVehicle->year }}</p>
    <span class="status-badge mt-4">{{ ucfirst($editingVehicle->publication_tier) }}</span>
</div>
@endsection

@section('content')
    @include('portal.seller._form')

    <section class="dashboard-panel">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker">Acciones rapidas</p>
                <h2>Estado y media</h2>
            </div>
        </div>
        <div class="seller-toolbar seller-toolbar--spread">
            <div class="seller-toolbar__meta">
                @if ($editingVehicle->status !== 'published')
                    <form method="POST" action="{{ route('seller.vehicles.publish', $editingVehicle) }}">@csrf @method('PATCH')<button type="submit" class="button button--solid">Publicar ahora</button></form>
                @else
                    <form method="POST" action="{{ route('seller.vehicles.pause', $editingVehicle) }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost">Pausar</button></form>
                @endif
                <a href="{{ route('seller.media') }}" class="button button--ghost">Gestionar media</a>
            </div>
            <form method="POST" action="{{ route('seller.vehicles.destroy', $editingVehicle) }}" onsubmit="return confirm('Eliminar publicacion?');">@csrf @method('DELETE')<button type="submit" class="button button--ghost">Eliminar</button></form>
        </div>
    </section>
@endsection


