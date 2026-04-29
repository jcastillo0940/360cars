# Movikaa

Backend inicial en Laravel 13 para un marketplace automotriz C2C enfocado en Costa Rica.

## Stack base

- Laravel 13
- PHP 8.3
- MySQL en WampServer
- API REST inicial en `api/v1`
- Laravel Sanctum para autenticacion por tokens
- Procesamiento asincrono de imagenes con Jobs + Queue
- Storage compatible con Cloudflare R2 (S3 compatible)
- Reglas comerciales por planes, suscripciones y transacciones base
- Integracion real con PayPal Orders v2 + webhook de reconciliacion
- Preparado para integrar frontend React / Next.js en una siguiente fase

## Lo que ya quedó montado

- Configuracion local para MySQL con base de datos `cars360`
- Estructura de usuarios para compradores, vendedores particulares y agencias
- Catalogo de marcas, modelos y categorias de estilo de vida
- Inventario de vehiculos con media, precios, estado y metadata comercial
- CRUD real de publicaciones con ownership y roles
- Reglas por plan: limites de fotos, publicaciones activas y tiers permitidos
- Activacion comercial de planes con suscripcion y transaccion base
- Beneficios del plan aplicados a publicaciones: expiracion, featured, restricciones de video/360
- Checkout PayPal: create order + capture order + activacion automatica de suscripcion
- Webhook PayPal con verificacion de firma y reconciliacion de capturas
- Acciones de publicar, pausar, reordenar galeria y elegir imagen principal
- Subida con staging temporal y procesamiento asincrono en cola
- Estados de media: `pending`, `complete`, `failed`
- Soporte listo para `public` local o `r2` como disco final
- Favoritos, comparador y busquedas guardadas
- Planes, suscripciones y transacciones
- Modulos base para historial registral, tasacion, trade-in y preaprobacion
- Chat interno con conversaciones y mensajes
- Autenticacion API con Sanctum y middleware de roles
- Seeders con datos demo para probar el sistema

## Endpoints principales

### Auth

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`
- `DELETE /api/v1/auth/logout-all`
- `GET /api/v1/auth/tokens`
- `DELETE /api/v1/auth/tokens/{tokenId}`

### Planes y suscripciones

- `GET /api/v1/plans`
- `GET /api/v1/my/subscription`
- `POST /api/v1/my/subscription`
- `POST /api/v1/my/subscription/paypal/create-order`
- `POST /api/v1/my/subscription/paypal/capture-order`
- `POST /api/v1/paypal/webhook`

### Catalogo publico

- `GET /api/v1/vehicles`
- `GET /api/v1/vehicles/{slug}`

### Publicaciones privadas

Requieren `auth:sanctum` y `role:seller,dealer,admin`.

- `GET /api/v1/my/publication-capabilities`
- `GET /api/v1/my/vehicles`
- `POST /api/v1/my/vehicles`
- `GET /api/v1/my/vehicles/{id}`
- `PUT|PATCH /api/v1/my/vehicles/{id}`
- `PATCH /api/v1/my/vehicles/{id}/publish`
- `PATCH /api/v1/my/vehicles/{id}/pause`
- `DELETE /api/v1/my/vehicles/{id}`
- `POST /api/v1/my/vehicles/{id}/media`
- `PATCH /api/v1/my/vehicles/{id}/media/reorder`
- `PATCH /api/v1/my/vehicles/{id}/media/{mediaId}/primary`
- `DELETE /api/v1/my/vehicles/{id}/media/{mediaId}`

## Flujo PayPal

1. El frontend llama `POST /api/v1/my/subscription/paypal/create-order` con `plan_slug`.
2. El backend crea una orden PayPal y devuelve `paypal_order_id` + `approve_url`.
3. El usuario aprueba en PayPal.
4. El frontend llama `POST /api/v1/my/subscription/paypal/capture-order` con `paypal_order_id`.
5. El backend captura la orden y activa la suscripcion si PayPal devuelve `COMPLETED`.
6. Adicionalmente, `POST /api/v1/paypal/webhook` reconcilia eventos como `PAYMENT.CAPTURE.COMPLETED`.

## Operacion local

```bash
php artisan migrate:fresh --seed
php artisan serve
php artisan queue:work --queue=media,default
php artisan test
```

## Variables clave para media

- `MEDIA_QUEUE_CONNECTION=database`
- `MEDIA_QUEUE_NAME=media`
- `VEHICLE_MEDIA_DISK=public`
- `VEHICLE_MEDIA_STAGING_DISK=local`

## Variables PayPal

- `PAYPAL_MODE=sandbox`
- `PAYPAL_CLIENT_ID=...`
- `PAYPAL_CLIENT_SECRET=...`
- `PAYPAL_WEBHOOK_ID=...`
- `PAYPAL_BRAND_NAME=Movikaa`
- `PAYPAL_RETURN_URL=https://tu-frontend.com/paypal/return`
- `PAYPAL_CANCEL_URL=https://tu-frontend.com/paypal/cancel`

## Variables para Cloudflare R2

- `VEHICLE_MEDIA_DISK=r2`
- `AWS_ACCESS_KEY_ID=...`
- `AWS_SECRET_ACCESS_KEY=...`
- `AWS_BUCKET=...`
- `AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com`
- `AWS_DEFAULT_REGION=auto`
- `AWS_USE_PATH_STYLE_ENDPOINT=true`
- `R2_PUBLIC_URL=https://cdn.tudominio.com`

## Credenciales demo

- `seller@movikaa.local` / `password`
- `dealer@movikaa.local` / `password`
- `buyer@movikaa.local` / `password`

## Seguridad operativa

- Honeypot publico con `spatie/laravel-honeypot`
- Escaneo antivirus de uploads con `sunspikes/clamav-validator`
- Guia de despliegue: `docs/SECURITY_DEPLOYMENT.md`

## Siguiente fase sugerida

1. Tilopay.
2. Sinpe Movil.
3. Registro Nacional CR, VIN decoder y pricing engine.
4. Notificaciones y alertas por caida de precio / nuevas coincidencias.
5. Frontend SSR con Next.js y Tailwind.
