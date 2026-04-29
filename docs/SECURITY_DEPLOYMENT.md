# Seguridad Operativa

## Objetivo

Esta guia resume la configuracion de produccion para las defensas que ya estan integradas en el proyecto:

- `spatie/laravel-honeypot` para formularios publicos
- `sunspikes/clamav-validator` para escaneo de archivos subidos
- bloqueo basico por IP y User-Agent via `config/security.php`

## Variables de produccion

Configura estas variables en el servidor o panel de despliegue:

```dotenv
APP_ENV=production

HONEYPOT_ENABLED=true
HONEYPOT_RANDOMIZE=true
HONEYPOT_SECONDS=2

CLAMAV_PREFERRED_SOCKET=tcp_socket
CLAMAV_TCP_SOCKET=tcp://127.0.0.1:3310
CLAMAV_SOCKET_CONNECT_TIMEOUT=5
CLAMAV_SOCKET_READ_TIMEOUT=30
CLAMAV_CLIENT_EXCEPTIONS=false
CLAMAV_SKIP_VALIDATION=false

SECURITY_BLOCKED_IPS=
SECURITY_ALLOWED_IPS=
SECURITY_BLOCKED_USER_AGENTS=sqlmap,curl/,nikto,nmap,masscan
```

## Checklist de ClamAV en Ubuntu

1. Instalar el daemon y el actualizador:

```bash
sudo apt-get update
sudo apt-get install -y clamav-daemon clamav-freshclam
```

2. Actualizar firmas:

```bash
sudo systemctl stop clamav-freshclam || true
sudo freshclam
sudo systemctl start clamav-freshclam
```

3. Habilitar servicios al arranque:

```bash
sudo systemctl enable --now clamav-daemon clamav-freshclam
```

4. Verificar que `clamd` este escuchando:

```bash
sudo systemctl status clamav-daemon --no-pager
ss -ltnp | grep 3310
```

5. Confirmar conectividad desde PHP/Laravel:

```bash
php artisan about
php -m | grep sockets
```

## Recomendaciones de despliegue

1. No actives `CLAMAV_CLIENT_EXCEPTIONS=true` en produccion salvo que estes depurando un incidente.
2. Manten `CLAMAV_SKIP_VALIDATION=false` en produccion. Si lo dejas en `true`, las cargas pasaran sin escaneo.
3. Si el servidor usa socket unix en vez de TCP, cambia `CLAMAV_PREFERRED_SOCKET=unix_socket` y ajusta `CLAMAV_UNIX_SOCKET`.
4. Si usas balanceador o Cloudflare delante del servidor, valida que Laravel este recibiendo la IP real antes de poblar `SECURITY_BLOCKED_IPS` o `SECURITY_ALLOWED_IPS`.
5. Revisa periodicamente los logs de Laravel para detectar falsos positivos o timeouts de escaneo.

## Validacion funcional

Despues del despliegue:

1. Abre `/register` o `/login` y confirma que el formulario siga enviando normalmente.
2. Prueba una carga de imagen valida desde seller onboarding o media manager.
3. Confirma que una peticion extremadamente rapida o automatizada al formulario reciba rechazo del honeypot.
4. Si ClamAV no responde, revisa `CLAMAV_TCP_SOCKET`, el estado del servicio y la extension `sockets` de PHP.
