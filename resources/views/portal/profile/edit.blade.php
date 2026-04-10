@extends('layouts.portal')

@section('title', 'Editar Perfil | Movikaa')
@section('portal-eyebrow', 'Configuración de cuenta')
@section('portal-title', 'Editar Perfil')

@section('header-actions')
    <a href="{{ url()->previous() }}" class="button button--ghost">Volver</a>
@endsection

@section('content')
<div class="profile-container" style="max-width: 900px;">
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="dashboard-grid dashboard-grid--two-up" style="gap: 2rem; align-items: start;">
            <!-- Left Column: Avatar and Info -->
            <div class="dashboard-panel" style="padding: 2rem;">
                <div class="panel-heading" style="margin-bottom: 2rem; border-bottom: 1px solid var(--portal-border); padding-bottom: 1rem;">
                    <h2>Imagen de Perfil</h2>
                </div>
                
                <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                    <div style="position: relative; width: 150px; height: 150px; margin-bottom: 1.5rem;">
                        <img src="{{ $user->avatar_path ? storage_url($user->avatar_path) : '/img/default-avatar.png' }}" 
                             id="avatar-preview"
                             style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: var(--portal-shadow);">
                        <label for="avatar-input" style="position: absolute; bottom: 5px; right: 5px; width: 40px; height: 40px; background: var(--portal-primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        </label>
                        <input type="file" id="avatar-input" name="avatar" style="display: none;" onchange="previewImage(this)">
                    </div>
                    <p style="font-size: 0.8rem; color: var(--portal-muted);">Formatos aceptados: JPG, PNG. Máximo 2MB.</p>
                </div>

                <div style="margin-top: 2.5rem;">
                    <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--portal-on-surface);">Biografía / Descripción</label>
                    <textarea name="bio" rows="4" style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--portal-border); background: var(--portal-bg); color: var(--portal-on-surface); font-size: 0.9rem; resize: vertical;" placeholder="Cuéntanos un poco sobre ti...">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>

            <!-- Right Column: Personal Data -->
            <div class="dashboard-panel" style="padding: 2rem;">
                <div class="panel-heading" style="margin-bottom: 2rem; border-bottom: 1px solid var(--portal-border); padding-bottom: 1rem;">
                    <h2>Datos Personales</h2>
                </div>

                <div style="display: grid; gap: 1.5rem;">
                    <div>
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--portal-on-surface);">Nombre completo</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                               style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--portal-border); background: var(--portal-bg); color: var(--portal-on-surface);" required>
                        @error('name') <p style="color: red; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--portal-on-surface);">Cédula o Pasaporte</label>
                        <input type="text" name="tax_id" value="{{ old('tax_id', $user->tax_id) }}" 
                               style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--portal-border); background: var(--portal-bg); color: var(--portal-on-surface);" placeholder="Número de identificación">
                        @error('tax_id') <p style="color: red; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--portal-on-surface);">Correo electrónico</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                               style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--portal-border); background: var(--portal-bg); color: var(--portal-on-surface);" required>
                        @error('email') <p style="color: red; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--portal-on-surface);">Teléfono</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                                   style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--portal-border); background: var(--portal-bg); color: var(--portal-on-surface);">
                        </div>
                        <div>
                            <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--portal-on-surface);">WhatsApp</label>
                            <input type="text" name="whatsapp_phone" value="{{ old('whatsapp_phone', $user->whatsapp_phone) }}" 
                                   style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--portal-border); background: var(--portal-bg); color: var(--portal-on-surface);">
                        </div>
                    </div>

                    <div style="margin-top: 1rem; padding-top: 2rem; border-top: 1px dashed var(--portal-border);">
                        <p class="portal-kicker">Seguridad</p>
                        <p style="font-size: 0.85rem; color: var(--portal-muted); margin-bottom: 1.5rem;">Deja en blanco si no deseas cambiar la contraseña.</p>
                        
                        <div style="display: grid; gap: 1rem;">
                            <div>
                                <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--portal-on-surface);">Nueva Contraseña</label>
                                <input type="password" name="password" 
                                       style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--portal-border); background: var(--portal-bg); color: var(--portal-on-surface);">
                            </div>
                            <div>
                                <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--portal-on-surface);">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" 
                                       style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--portal-border); background: var(--portal-bg); color: var(--portal-on-surface);">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 3rem;">
                    <button type="submit" class="button button--solid" style="width: 100%; padding: 1rem; border-radius: 14px; font-weight: 800; font-size: 1rem;">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
