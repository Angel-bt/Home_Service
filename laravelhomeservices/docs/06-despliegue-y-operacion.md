# 06 · Despliegue y operación

## Requisitos del servidor

- **PHP 8.3 o superior** con extensiones `pdo_mysql`, `redis` (phpredis), `mbstring`, `openssl`, `fileinfo`, `gd` o `imagick`.
- **MySQL 8** (o MariaDB compatible).
- **Redis** (caché, sesiones, colas, contador de visitas y bloqueos del programador de tareas).
- **Composer 2** y **Node 18+** (solo para compilar los assets; se puede compilar en otra máquina y subir `public/css`, `public/js` y `public/mix-manifest.json`).
- Servidor web (Nginx o Apache) apuntando a `laravelhomeservices/public`.
- **https** obligatorio (cookies `secure`, HSTS).

## Primer despliegue

```bash
cd laravelhomeservices
composer install --no-dev --optimize-autoloader
npm ci && npm run prod
cp .env.production.example .env      # y completa los valores (tabla de abajo)
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=ServiceCategorySeeder --force   # categorías iniciales (luego edítalas en el admin)
php artisan optimize                  # cachea config, rutas, eventos y vistas
```

Permisos de escritura para el usuario del servidor web en `storage/`, `bootstrap/cache/` y `public/images/` (ahí se guardan las imágenes subidas).

No ejecutes `DemoMarketplaceSeeder` en producción: se niega a correr con `APP_ENV=production`.

Crea el administrador: registra una cuenta normal y cambia su `utype` a `ADM` en la base de datos.

## Actualizaciones posteriores

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run prod
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan queue:restart
php artisan up
```

> **Al desplegar la actualización a Laravel 13** (commit `d2e701a`) las sesiones pasan a JSON: **todos los usuarios quedan con la sesión cerrada una vez**. Es esperado.

## Variables de entorno

Plantilla completa: `.env.production.example`. Las más importantes:

| Variable | Valor recomendado | Notas |
|---|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | Nunca `APP_DEBUG=true` en producción. |
| `APP_URL` | `https://tu-dominio` | Se usa para enlaces y correos. |
| `APP_LOCALE` | `es` | |
| `APP_TIMEZONE` | `America/Guayaquil` | Hora de Ecuador continental (UTC−5) para fechas de solicitudes, pagos y vencimientos. |
| `DB_*` | — | Usuario con permisos solo sobre su base. |
| `CACHE_DRIVER`, `SESSION_DRIVER`, `QUEUE_CONNECTION` | `redis` | |
| `SESSION_SERIALIZATION` | `json` | Ver [05-seguridad.md](05-seguridad.md). |
| `SESSION_SECURE_COOKIE` | `true` | Requiere https. |
| `REDIS_*` | — | |
| `MAIL_*` | SMTP real | Para bienvenida y avisos. |
| `LEAD_NOTIFICATION_ADDRESS` | correo de la administración | Vacío = no se envía el aviso de nuevos contactos. |
| `MARKETPLACE_CONTACT_PHONE`, `MARKETPLACE_CONTACT_EMAIL` | contactos públicos | Se muestran en el encabezado, el pie y la página de contacto. |
| `FREE_SERVICE_LIMIT` | `3` | Servicios del plan gratis. |
| `MARKETPLACE_CACHE_TTL` | `3600` | Segundos de caché de la portada. |
| `GOOGLE_MAPS_API_KEY` | opcional | Activa el mapa de "Cambiar ubicación" (API Places). |
| `BILLING_*` | — | Precio del plan Pro y datos bancarios. Ver [04-pagos.md](04-pagos.md). |
| `PAYPHONE_ENABLED` | `false` | El pago con tarjeta está desactivado. |

Después de cambiar el `.env` en producción: `php artisan config:cache`.

## Tareas programadas (cron)

Una sola línea de cron en el servidor:

```cron
* * * * * cd /ruta/a/laravelhomeservices && php artisan schedule:run >> /dev/null 2>&1
```

Ejecuta:

| Tarea | Frecuencia | Qué hace |
|---|---|---|
| `services:sync-visit-counters` | cada 5 min | Pasa a MySQL (`services.views`) las visitas acumuladas en Redis. |
| `billing:expire-plans` | cada hora | Pasa a gratis los planes Pro vencidos y cierra pagos con tarjeta abandonados. |

Ambas usan `withoutOverlapping()` y `onOneServer()`, que necesitan la caché en Redis.

En local: `php artisan schedule:work`.

## Cola de correos

Los correos de bienvenida y de nuevos contactos van a la cola `mail` (3 reintentos), para que las páginas no esperen al SMTP. Mantén un worker vivo con Supervisor o systemd:

```bash
php artisan queue:work redis --queue=mail,default --sleep=1 --tries=3 --max-time=3600
```

Tras cada despliegue: `php artisan queue:restart`.

Alternativa sin Redis para colas: `QUEUE_CONNECTION=database` (la tabla `jobs` ya existe).

## Caché

- **Portada:** sus datos se cachean en `home:landing` (máx. 10 min) y se invalidan al guardar servicios, categorías o reseñas. Si editas la base a mano: `php artisan cache:clear`.
- **Assets:** en producción compila con `npm run prod` (CSS y JS minificados con versión en la URL). Con `APP_DEBUG=false` Livewire sirve su JS minificado.
- **Rendimiento:** los listados cargan las relaciones por adelantado (`with(...)`) para evitar consultas repetidas.
- `php artisan optimize` en cada despliegue cachea configuración, rutas y vistas.

## Contador de visitas

`ServiceVisitCounter` suma cada visita en Redis (`service-visits:service:{id}`) sin escribir en MySQL. El comando programado reclama los contadores de forma atómica, los suma en una transacción y, si MySQL falla, los devuelve a Redis. Si Redis no está disponible, la página del servicio se muestra igual (la visita no se cuenta y el error queda en el log).

## Respaldos

- Base de datos MySQL (diario).
- `public/images/` (imágenes subidas).
- `storage/app/receipts/` (comprobantes de pago; datos sensibles).
- El archivo `.env` (guardado fuera del servidor y del repositorio).
