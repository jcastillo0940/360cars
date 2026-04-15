<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <meta name="supported-color-schemes" content="dark light">
    <title>{{ $subject ?? 'Movikaa' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#07080b; background-image:radial-gradient(circle at top, rgba(255,179,71,0.16), transparent 28%), radial-gradient(circle at bottom left, rgba(255,255,255,0.08), transparent 24%); color:#f3f4f6; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; width:100%;">
        <tr>
            <td align="center" style="padding:34px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; max-width:680px; margin:0 auto;">
                    <tr>
                        <td align="center" style="padding:0 0 18px;">
                            <img src="https://movikaa.co/img/logo.png" alt="Movikaa" width="140" style="display:block; width:140px; max-width:140px; height:auto; margin:0 auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #232634; border-radius:34px; background:linear-gradient(180deg, rgba(11,11,15,0.98) 0%, rgba(10,11,16,0.98) 100%); box-shadow:0 28px 90px rgba(0,0,0,0.48); padding:22px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                @isset($eyebrow)
                                    <tr>
                                        <td style="padding:0 0 18px;">
                                            <div style="display:inline-block; background:rgba(0,160,120,0.16); border:1px solid rgba(0,160,120,0.45); color:#d1fae5; border-radius:18px; padding:14px 18px; font-size:14px; font-weight:700; line-height:1.4;">
                                                {{ $eyebrow }}
                                            </div>
                                        </td>
                                    </tr>
                                @endisset
                                <tr>
                                    <td style="padding:10px 18px 8px; text-align:center;">
                                        <h1 style="margin:0; font-size:42px; line-height:1.08; font-weight:800; letter-spacing:-0.03em; color:#ffffff;">
                                            {{ $headline ?? 'Movikaa' }}
                                        </h1>
                                    </td>
                                </tr>
                                @isset($subcopy)
                                    <tr>
                                        <td style="padding:0 36px 24px; text-align:center;">
                                            <p style="margin:0; font-size:16px; line-height:1.7; color:#b6bcc8;">
                                                {{ $subcopy }}
                                            </p>
                                        </td>
                                    </tr>
                                @endisset
                                <tr>
                                    <td style="padding:0 18px 10px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.06); border-radius:24px;">
                                            <tr>
                                                <td style="padding:28px 24px;">
                                                    @yield('content')
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:18px 24px 8px; text-align:center;">
                                        <p style="margin:0; font-size:12px; line-height:1.8; color:#7f8694;">Movikaa · Marketplace automotriz</p>
                                        <p style="margin:6px 0 0; font-size:12px; line-height:1.8; color:#646c7b;">Si necesitas ayuda, responde este correo.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
