@extends('emails.layouts.brand')

@php
    $subject = 'Recupera tu acceso a Movikaa';
    $eyebrow = 'Te enviamos un enlace para restablecer tu contrasena.';
    $headline = '¿Olvidaste tu clave?';
    $subcopy = 'No te preocupes. Preparamos un acceso seguro para que recuperes tu cuenta sin salir de la linea visual de Movikaa.';
@endphp

@section('content')
    <p style="margin:0 0 16px; font-size:16px; line-height:1.7; color:#f3f4f6;">Hola {{ $user->name }},</p>
    <p style="margin:0 0 20px; font-size:15px; line-height:1.8; color:#c1c7d0;">
        Usa el siguiente boton para crear una nueva contrasena. El enlace expirara en {{ $expireMinutes }} minutos.
    </p>
    <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin:0 0 20px;">
        <tr>
            <td align="center" bgcolor="#ffb347" style="border-radius:18px;">
                <a href="{{ $actionUrl }}" style="display:inline-block; padding:16px 26px; font-size:16px; font-weight:800; color:#0c0d12; text-decoration:none;">Restablecer contrasena</a>
            </td>
        </tr>
    </table>
    <p style="margin:0 0 14px; font-size:14px; line-height:1.8; color:#98a1ad;">Si el boton no abre, copia y pega este enlace en tu navegador:</p>
    <p style="margin:0 0 20px; font-size:13px; line-height:1.8; color:#ffcc7a; word-break:break-all;">{{ $actionUrl }}</p>
    <p style="margin:0; font-size:14px; line-height:1.8; color:#98a1ad;">Si no solicitaste este cambio, puedes ignorar este mensaje con tranquilidad.</p>
@endsection

