<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenido a Movikaa</title>
</head>
<body style="margin:0; padding:0; background:#0b0b0f; color:#e5e7eb; font-family:Arial, sans-serif;">
    <div style="max-width:640px; margin:0 auto; padding:32px 20px;">
        <div style="background:#111827; border:1px solid #1f2937; border-radius:24px; padding:32px;">
            <p style="margin:0 0 12px; font-size:12px; letter-spacing:0.18em; text-transform:uppercase; color:#f59e0b;">Movikaa</p>
            <h1 style="margin:0 0 16px; font-size:28px; line-height:1.2; color:#ffffff;">Tu cuenta ya esta lista</h1>
            <p style="margin:0 0 16px; font-size:16px; line-height:1.7;">Hola {{ $user->name }}, gracias por registrarte en Movikaa.</p>
            <p style="margin:0 0 24px; font-size:16px; line-height:1.7;">Ya puedes entrar a tu panel para publicar vehiculos, gestionar tus datos y seguir tus oportunidades.</p>
            <a href="{{ route('login') }}" style="display:inline-block; background:#f59e0b; color:#111827; text-decoration:none; padding:14px 22px; border-radius:14px; font-weight:700;">Entrar a mi cuenta</a>
            <p style="margin:24px 0 0; font-size:14px; line-height:1.7; color:#9ca3af;">Si no creaste esta cuenta, responde a este correo o contacta al equipo de soporte.</p>
        </div>
    </div>
</body>
</html>
