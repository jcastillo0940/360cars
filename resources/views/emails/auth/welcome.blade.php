@extends('emails.layouts.brand')

@php
    $subject = 'Bienvenido a Movikaa';
    $eyebrow = 'Tu cuenta ya esta activa y lista para moverse.';
    $headline = 'Bienvenido a Movikaa';
    $subcopy = 'Creamos una experiencia de compra y venta con el mismo pulso premium del sitio. Ya puedes entrar y empezar.';
@endphp

@section('content')
    <p style="margin:0 0 16px; font-size:16px; line-height:1.7; color:#f3f4f6;">Hola {{ $user->name }},</p>
    <p style="margin:0 0 16px; font-size:15px; line-height:1.8; color:#c1c7d0;">
        Gracias por registrarte en Movikaa. Tu cuenta esta lista para publicar vehiculos, gestionar contactos y seguir oportunidades desde tu panel.
    </p>
    <p style="margin:0 0 22px; font-size:15px; line-height:1.8; color:#c1c7d0;">
        Mantuvimos la misma linea visual del sitio: limpia, oscura y enfocada en conversion para que cada punto de contacto se sienta consistente.
    </p>
    <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin:0 0 20px;">
        <tr>
            <td align="center" bgcolor="#ffb347" style="border-radius:18px;">
                <a href="{{ route('login') }}" style="display:inline-block; padding:16px 26px; font-size:16px; font-weight:800; color:#0c0d12; text-decoration:none;">Entrar a mi cuenta</a>
            </td>
        </tr>
    </table>
    <p style="margin:0; font-size:14px; line-height:1.8; color:#98a1ad;">Si no creaste esta cuenta, responde este correo y lo revisamos contigo.</p>
@endsection
