# Arquitectura Inicial 360Cars

## Vision

360Cars nace como un marketplace C2C automotriz, pero con soporte desde el dia uno para agencias y monetizacion escalable. Esta base se enfoca en el backend y deja listo el dominio principal para crecimiento por fases.

## Modulos implementados

### Identidad y confianza

- `users`: compradores, vendedores, agencias y futuros admins
- campos de verificacion, rating y trazabilidad comercial
- autenticacion API con Sanctum
- autorizacion por rol via middleware `role`

### Catalogo automotriz

- `vehicle_makes`
- `vehicle_models`
- `lifestyle_categories`

### Inventario

- `vehicles`
- `vehicle_media`
- `vehicle_lifestyle_category`

El inventario ya soporta:

- filtros clasicos
- etiquetas de precio
- soporte base para video y 360 a nivel de schema
- geolocalizacion basica
- metadatos para IA y recomendaciones
- CRUD privado de publicaciones
- ownership estricto por usuario
- subida multiple de imagenes
- staging temporal de uploads
- jobs asincronos para procesar media
- conversion automatica a WebP y thumbnail optimizado
- reordenamiento de galeria
- seleccion de imagen principal
- publicar y pausar anuncios
- reglas por plan para limitar media y anuncios activos
- beneficios comerciales aplicados sobre featured y expiracion

### Cola y media

- `ProcessVehicleImageUpload` procesa cada imagen en segundo plano
- `vehicle_media.processing_status` expone `pending`, `complete` y `failed`
- `config/media.php` controla disco final, disco temporal y cola
- `config/filesystems.php` incluye disco `r2` para Cloudflare R2

### Monetizacion

- `plans`
- `subscriptions`
- `transactions`

El backend ya permite:

- consultar planes disponibles
- activar una suscripcion base via API
- registrar una transaccion de compra base
- resolver el plan efectivo del usuario
- aplicar restricciones y beneficios del plan a cada publicacion
- crear y capturar ordenes PayPal con Orders v2
- verificar y procesar webhooks PayPal
- activar automaticamente la suscripcion tras captura completada

### Engagement y conversion

- `vehicle_favorites`
- `comparisons`
- `comparison_vehicle`
- `saved_searches`

### Confianza, finanzas y negocio

- `vehicle_registry_checks`
- `vehicle_valuations`
- `trade_in_offers`
- `credit_applications`
- `reviews`

### Mensajeria

- `conversations`
- `conversation_participants`
- `messages`

## API de planes, pagos y publicaciones

### Publica

- `GET /api/v1/plans`
- `GET /api/v1/vehicles`
- `GET /api/v1/vehicles/{slug}`
- `POST /api/v1/paypal/webhook`

### Privada

- `GET /api/v1/my/subscription`
- `POST /api/v1/my/subscription`
- `POST /api/v1/my/subscription/paypal/create-order`
- `POST /api/v1/my/subscription/paypal/capture-order`
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

## Integraciones previstas

- Tilopay y Sinpe Movil
- Registro Nacional CR con cache por placa
- decodificador VIN
- Cloudflare R2 para media ya soportado a nivel de disco
- pipeline de optimizacion a WebP y AVIF con colas
- motor de recomendaciones y busqueda por imagen

## Roadmap tecnico inmediato

1. Tilopay.
2. Sinpe Movil.
3. Integraciones de confianza y scoring automotriz.
4. Leads de credito, trade-in y alertas.
5. Conectar frontend Next.js consumiendo esta API.
