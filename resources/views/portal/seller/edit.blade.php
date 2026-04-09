@extends('layouts.portal')

@section('title', 'Editar anuncio vendedor | Movikaa')
@section('portal-eyebrow', 'Seller edit')
@section('portal-title', 'Editar publicacion')
@section('portal-copy', 'Ajusta precio, visibilidad, descripcion y media desde una vista dedicada que te deja trabajar el anuncio como una ficha comercial completa.')

@section('header-actions')
    <a href="{{ route('seller.listings') }}" class="button button--ghost">Volver a publicaciones</a>
    <a href="{{ route('catalog.show', $editingVehicle->slug) }}" class="button button--solid">Ver anuncio</a>
@endsection

@section('content')
    <div class="reveal">
        @include('portal.seller._form')
    </div>

    <section class="dashboard-panel reveal reveal--delay-3" style="margin-top: 2rem; border-color: var(--portal-danger);">
        <div class="panel-heading">
            <div>
                <p class="portal-kicker" style="color: var(--portal-danger);">Zona de Peligro</p>
                <h2>Eliminar publicación</h2>
                <p style="color: var(--portal-muted);">Esta acción es irreversible. Se eliminarán los datos, fotos y estadísticas del auto.</p>
            </div>
            <form method="POST" action="{{ route('seller.vehicles.destroy', $editingVehicle) }}" onsubmit="return confirm('¿Eliminar publicación definitivamente?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="button button--solid" style="background: var(--portal-danger); border-color: var(--portal-danger);">Eliminar permanentemente</button>
            </form>
        </div>
    </section>
@endsection

