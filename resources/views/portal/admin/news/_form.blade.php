@php
    $post = $newsPost ?? null;
@endphp
<form method="POST" action="{{ $action }}" class="portal-form" id="editor">
    @csrf
    @if (!empty($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="form-field form-field--wide"><span>Título</span><input type="text" name="title" value="{{ old('title', $post?->title) }}" required /></label>
        <label class="form-field"><span>Slug</span><input type="text" name="slug" value="{{ old('slug', $post?->slug) }}" placeholder="Se genera si lo dejas vacío" /></label>
        <label class="form-field"><span>Estado</span><select name="status"><option value="draft" @selected(old('status', $post?->status ?? 'draft') === 'draft')>Borrador</option><option value="published" @selected(old('status', $post?->status) === 'published')>Publicado</option></select></label>
        <label class="form-field"><span>Fecha de publicación</span><input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post?->published_at)->format('Y-m-d\TH:i')) }}" /></label>
        <label class="form-field form-field--wide"><span>Imagen principal URL</span><input type="url" name="cover_image_url" value="{{ old('cover_image_url', $post?->cover_image_url) }}" placeholder="https://..." /></label>
        <label class="form-field form-field--wide"><span>Resumen</span><textarea name="excerpt" rows="3" placeholder="Resumen corto para tarjetas y SEO.">{{ old('excerpt', $post?->excerpt) }}</textarea></label>
        <label class="form-field form-field--wide"><span>Contenido</span><textarea name="content" rows="16" placeholder="Escribe aquí el contenido completo del artículo." required>{{ old('content', $post?->content) }}</textarea></label>
        <label class="form-field form-field--wide"><span>Meta título</span><input type="text" name="meta_title" value="{{ old('meta_title', $post?->meta_title) }}" placeholder="Opcional para SEO" /></label>
        <label class="form-field form-field--wide"><span>Meta descripción</span><textarea name="meta_description" rows="3" placeholder="Opcional para SEO.">{{ old('meta_description', $post?->meta_description) }}</textarea></label>
    </div>

    <div class="form-actions" style="justify-content:space-between;align-items:center;gap:1rem;">
        <label class="inline-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post?->is_featured)) /> <span>Destacar este artículo</span></label>
        <div class="form-actions">
            <a href="{{ route('admin.news') }}" class="button button--ghost">Volver</a>
            <button type="submit" class="button button--solid">{{ $submitLabel }}</button>
        </div>
    </div>
</form>

