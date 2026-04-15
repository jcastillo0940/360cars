<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Prueba SMTP Movikaa</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background: #f5f7fb; color: #111827; margin: 0; padding: 24px;">
    <div style="max-width: 680px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px;">
        <p style="margin: 0 0 12px; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #6b7280;">Movikaa Admin</p>
        <h1 style="margin: 0 0 16px; font-size: 24px; line-height: 1.2;">Prueba de correo SMTP</h1>
        <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.7;">{{ $messageBody }}</p>

        <div style="background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb; padding: 16px; margin: 20px 0;">
            <p style="margin: 0 0 8px;"><strong>Enviado por:</strong> {{ $senderName }}</p>
            <p style="margin: 0 0 8px;"><strong>Fecha:</strong> {{ $sentAt->format('d/m/Y H:i:s') }}</p>
            <p style="margin: 0 0 8px;"><strong>Mailer:</strong> {{ $mailConfig['default_mailer'] }}</p>
            <p style="margin: 0 0 8px;"><strong>Host:</strong> {{ $mailConfig['host'] ?: 'Sin definir' }}</p>
            <p style="margin: 0;"><strong>Puerto / esquema:</strong> {{ ($mailConfig['port'] ?: 'n/a').' / '.($mailConfig['scheme'] ?: 'sin esquema') }}</p>
        </div>

        <p style="margin: 0; font-size: 13px; color: #6b7280;">Si recibiste este correo, la configuracion actual pudo procesar un envio desde el panel administrativo.</p>
    </div>
</body>
</html>
