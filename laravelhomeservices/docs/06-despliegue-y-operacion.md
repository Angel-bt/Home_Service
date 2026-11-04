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

## Vercel

Vercel no tiene disco permanente, ni procesos en segundo plano, ni `schedule:run`. La app se adapta con variables de entorno, sin cambiar de código: `.env.vercel.example` trae la lista completa.

| En un servidor normal | En Vercel | Por qué |
|---|---|---|
| Fotos en `public/images` | Bucket con lectura pública (`FILESYSTEM_DISK=s3`) | El disco es de solo lectura. Las imágenes que vienen en el repositorio se siguen sirviendo desde `public/images` (`App\Support\Media`). |
| Comprobantes en `storage/app/receipts` | Bucket **privado** (`RECEIPTS_BUCKET`) | Solo el admin los ve, por la ruta protegida. |
| Subidas temporales en `storage/app/uploads` | Directo del navegador al bucket privado (`LIVEWIRE_TEMP_DISK=receipts`) | Cada petición puede caer en otra instancia, y Vercel corta los cuerpos de más de 4,5 MB. |
| Redis para sesiones y caché | Base de datos (`SESSION_DRIVER`, `CACHE_DRIVER=database`) | Un servicio menos que contratar. |
| Contador de visitas en Redis | Escritura directa (`VISIT_COUNTER=database`) | Sin Redis. |
| Colas con `queue:work` | Envío en el momento (`QUEUE_CONNECTION=sync`, `MAIL_TIMEOUT=5`) | No hay trabajadores. Si el correo falla, la cuenta o el mensaje igual se guardan y el error queda en el registro. |
| `schedule:run` | Vercel Cron llama a `/cron/diario` con `CRON_SECRET` | El plan Hobby solo permite una vez al día, y alcanza: `isPro()` ya revisa la fecha de vencimiento. |
| Registros en archivo | `LOG_CHANNEL=stderr` | Se ven en el panel de Vercel. |

Archivos: `vercel.json`, `.vercelignore` y `api/index.php` (lleva a `/tmp` lo que Laravel genera al arrancar). Los assets compilados se suben al repositorio, así que Vercel no compila nada.

### El despliegue actual

- **Sitio:** https://malakplace.vercel.app (proyecto `malakplace` del equipo `angel-a824`).
- **Base de datos y almacenamiento:** Supabase, instalado desde el Marketplace de Vercel (plan gratuito, región `us-east-1`). Es **Postgres**, no MySQL: la app funciona en ambos y toda la batería de tests se corrió también sobre Postgres. `DB_CONNECTION=pgsql`; la conexión sale de `POSTGRES_URL_NON_POOLING`, que crea la integración. No se usa el pooler de transacciones (`POSTGRES_URL`) porque rompe las consultas preparadas de PDO.
- **Buckets en Supabase Storage:** `media` (público) y `privado`. `AWS_ENDPOINT=<SUPABASE_URL>/storage/v1/s3`, `AWS_URL=<SUPABASE_URL>/storage/v1/object/public/media`, `AWS_USE_PATH_STYLE_ENDPOINT=true`.
- **Datos de demostración:** cargados con `DemoMarketplaceSeeder`, con contraseñas aleatorias guardadas en `.vercel/cuentas-demo.txt` (privado, no se versiona). El sitio va con `X-Robots-Tag: noindex` en `vercel.json` hasta el lanzamiento.
- **Pendiente para que funcione todo:** SMTP (por ahora `MAIL_MAILER=log`: los correos solo van al registro).
- **Despliegue automático:** cada push a `main` en GitHub publica el sitio (*Root Directory* = `laravelhomeservices`, porque la app no está en la raíz del repositorio). Para desplegar a mano, usa `vercel deploy --prod` desde la **raíz del repositorio**, no desde `laravelhomeservices/`.

**Subidas activadas:** las claves S3 de Supabase están en `AWS_ACCESS_KEY_ID` y `AWS_SECRET_ACCESS_KEY` (se crean en *Project Settings → Storage → S3 Connection*), con `FILESYSTEM_DISK=s3` y `LIVEWIRE_TEMP_DISK=receipts`.

**Autocomprobación:** el cron diario corre `php artisan storage:check`, que escribe, lee y borra un archivo en los dos buckets y hace una subida firmada completa con las mismas clases que una real. Si algo falla, `/cron/diario` responde 500 y el motivo queda en los registros de Vercel. Para probar en cualquier momento: `vercel crons run /cron/diario` y luego `vercel logs --no-follow --since 5m`.

**Antes del lanzamiento real:** borra los datos de demostración, quita el `noindex` de `vercel.json`, conecta el SMTP, carga los datos bancarios (`BILLING_BANK_*`) y pasa Vercel al plan de pago (ver abajo).

### Antes de desplegar, conviene saber

- **El runtime PHP no es oficial de Vercel.** `vercel-php` es comunitario y su repositorio está archivado. Funciona, pero nadie garantiza que siga funcionando si Vercel cambia su plataforma.
- **El plan Hobby es solo para uso no comercial.** Con el plan Pro de proveedores cobrando dinero real, corresponde el plan de pago de Vercel.
- **La base de datos debe estar cerca de la función** (región `iad1` por defecto): cada página hace varias consultas.

### Pasos

1. **Crea los servicios:** una base MySQL o Postgres accesible desde internet, dos buckets compatibles con S3 (Supabase Storage o Cloudflare R2 tienen capa gratuita) y un SMTP.
   - Bucket de fotos: lectura pública. Su dirección va en `AWS_URL`.
   - Bucket privado: sin acceso público, con CORS que permita `PUT` y `GET` desde tu dominio, y una regla que borre `livewire-tmp/` después de 1 día.
2. **Prepara la base desde tu computadora,** con un `.env.produccion` temporal que apunte a ella:
   ```bash
   php artisan migrate --force --env=produccion
   php artisan db:seed --class=ServiceCategorySeeder --force --env=produccion
   ```
   Luego registra tu cuenta en el sitio y cambia su `utype` a `ADM` en la base. Borra ese archivo al terminar.
3. **Crea el proyecto en Vercel** con *Root Directory* = `laravelhomeservices` y Node 22, y carga las variables de `.env.vercel.example` en *Settings → Environment Variables*. `APP_KEY` sale de `php artisan key:generate --show`.
4. **Despliega** y prueba: registro, foto de perfil, publicar un servicio, solicitud, comprobante y aprobación.

### Ensayo local

Para probar esta configuración sin Vercel: un S3 simulado (moto) y un `.env` con los valores de arriba, Redis y correo apuntando a puertos cerrados. Así se validó la versión actual; lo único que no cubre es el runtime de Vercel en sí.
