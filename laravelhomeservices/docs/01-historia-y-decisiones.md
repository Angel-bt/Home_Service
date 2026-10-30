# 01 · Historia del proyecto y decisiones

Este documento explica **de dónde viene el proyecto, qué se cambió y por qué**. Si vas a mantenerlo, léelo primero: muchas cosas que parecen raras tienen una razón.

## Punto de partida

El código base es el tutorial *Laravel 8 Project Home Services* (SurfsideMedia), construido sobre Laravel 9, Livewire 2 y Jetstream 2. Ofrecía:

- categorías y servicios con imágenes, slider y destacados;
- búsqueda con autocompletado, formulario de contacto y "cambiar ubicación";
- registro como cliente o proveedor y un perfil de proveedor;
- panel de administración para todo lo anterior.

Era un **catálogo**: los proveedores no podían publicar nada, no había forma de pedir un servicio, ni reseñas, ni cobro. Además traía datos de la India (categorías como *Geyser* o *AC*, teléfonos +91, dirección en Haryana).

## Auditoría inicial (resumen)

Se revisó todo el código antes de tocar nada. Los hallazgos principales:

| Gravedad | Hallazgo |
|---|---|
| Error 500 | El panel de admin se caía con una base de datos nueva (el contador de visitas creaba un registro sin un campo obligatorio). |
| Error 500 | El registro fallaba si el formulario no enviaba el tipo de cuenta. |
| Pérdida de datos | Borrar una categoría borraba en cascada los **perfiles de los proveedores**. |
| Lógica | Los proveedores no podían publicar servicios, así que el límite del plan gratis nunca se aplicaba. |
| Lógica | "Cambiar ubicación" no cargaba Google Maps y la ubicación no se usaba para nada. La búsqueda solo convertía el texto en un enlace. |
| Seguridad | Laravel 9 sin soporte; "olvidé mi contraseña" revelaba qué correos existían; subidas de imágenes sin límite de tamaño. |
| Negocio | Stripe estaba previsto para cobrar, pero **Stripe no acepta comercios de Ecuador**. |

Con eso se definió un plan en cuatro fases.

## Fase 0 · Que funcione como el tutorial

Objetivo: corregir los errores sin cambiar el producto.

- **Contador de visitas:** `Visit::counter()` crea el registro global con todos sus campos. El dashboard muestra el número real de visitas.
- **Registro:** el tipo de cuenta (`registeras`) se valida como `CST` o `SVP`; si falta, la cuenta es de cliente; `ADM` se rechaza (nadie puede registrarse como admin).
- **Categorías:** la llave foránea de `service_providers` pasó a `nullOnDelete`. *Por qué:* un proveedor que pierde su categoría simplemente elige otra; borrar su perfil era destructivo.
- **Cambiar ubicación:** carga Google Places solo si existe `GOOGLE_MAPS_API_KEY`; sin clave, se pueden llenar los campos a mano. El mapa arranca en Malacatos.
- **Pantallas de admin:** un id o slug inexistente devuelve 404 en lugar de error 500.
- Se añadieron los primeros tests de humo y se limpió el repositorio (una copia duplicada de la app, un archivo vacío `sudo`).

## Fase 1 · Marketplace para Malacatos

Objetivo: que clientes y proveedores se encuentren y se contacten.

- **Proveedores publican sus servicios** (`/sprovider/my-services`). El formulario es el mismo que usa el admin (`Concerns\HandlesServiceForm` + `partials/service-form`), para no mantener dos copias.
  - *Por qué la ruta es `my-services`:* `/sprovider/services` chocaba con la ruta pública `/{categoría}/services`.
- **Plan gratis con límite:** 3 servicios (`FREE_SERVICE_LIMIT`). El límite ya existía en el código pero nadie llegaba a él.
- **WhatsApp:** botón en cada servicio. Los números ecuatorianos (`099…`) se convierten al formato `593…` (`App\Support\WhatsApp`). *Por qué:* en la zona casi todo se contrata por WhatsApp; es el canal que la gente ya usa.
- **Zonas:** cada proveedor marca dónde trabaja (lista en `config/marketplace.php`). Los listados se filtran por zona y la zona elegida se recuerda. *Por qué no solo Malacatos:* la parroquia es pequeña; incluir el valle da volumen suficiente de clientes y proveedores.
- **Solicitudes de servicio:** el cliente envía zona, dirección, fecha y mensaje; el proveedor acepta, rechaza o completa.
- **Reseñas:** solo de solicitudes completadas y una por solicitud. *Por qué:* evita reseñas falsas; cada reseña corresponde a un trabajo real.
- **Búsqueda real** (`/buscar`) por nombre, descripción y categoría.
- **Español:** idioma por defecto, textos traducidos, páginas estáticas reescritas (traían texto de una plantilla de viajes), contactos y categorías de la India reemplazados por configuración.
- **CSS:** el rediseño de la portada usaba Tailwind, pero `css/app.css` estaba desactualizado y no se cargaba. Se recompiló y se enlazó **sin el reset global de Tailwind** (`preflight`) para no romper el tema Bootstrap heredado.

## Fase 2 · Cobro del plan Pro

Objetivo: un ingreso para la plataforma que funcione en Ecuador.

- Modelo de negocio: **el cliente paga al proveedor directamente** (la plataforma no intermedia ese dinero) y **el proveedor paga una suscripción Pro** para publicar sin límite.
- *Por qué no comisión por trabajo:* intermediar pagos implica obligaciones legales y contables (facturación al SRI, devoluciones); se deja para cuando haya volumen.
- *Por qué no Stripe:* no opera con comercios ecuatorianos.
- Se implementó pago manual con comprobante, aprobación por el admin y vencimiento automático. También se dejó lista una integración con **Payphone** (tarjeta), hoy desactivada.
- **Actualización posterior:** por ahora solo se acepta **transferencia bancaria o efectivo**. Se quitó DeUna de la interfaz y se añadió el efectivo: el proveedor avisa o el admin registra el pago recibido en persona. Detalle en [04-pagos.md](04-pagos.md).

## Ajustes posteriores

- **Zona horaria:** la app usaba UTC, así que las fechas salían 5 horas adelantadas (un pago hecho a las 23:57 aparecía al día siguiente a las 04:57). Ahora usa `America/Guayaquil` (`APP_TIMEZONE`). Los registros creados antes del cambio se guardaron en UTC y se verán 5 horas antes; no afecta a instalaciones nuevas.
- **`.env.example`** para desarrollo local (antes solo existía el de producción).
- **Documentación** completa en español en `docs/`.

## Fase 3 · Actualización y seguridad

Objetivo: que el proyecto reciba parches de seguridad.

- **Laravel 9 → 13**, **Livewire 2 → 3.8**, **Jetstream 2 → 5.5**, **Sanctum 4**, **PHPUnit 12**. Requiere PHP 8.3+.
  - *Por qué Laravel 13 y no 12:* es la versión con soporte más largo; Laravel 12 deja de recibir parches de seguridad en febrero de 2027.
  - Se mantuvo la estructura de carpetas antigua (`app/Http/Kernel.php`, etc.), que Laravel 13 sigue soportando, para no mover decenas de archivos sin necesidad.
  - Los componentes Livewire siguen en `App\Http\Livewire` (configurado en `config/livewire.php`) por la misma razón.
- **Cambios de Livewire 3** que afectan cómo se escribe código nuevo: ver [07-desarrollo-y-tests.md](07-desarrollo-y-tests.md).
- **Jetstream 5:** las páginas de cuenta (`/user/profile`) usan el layout del sitio y están en español (`lang/es.json`).
- **Seguridad:** cabeceras, guards de rol en cada petición Livewire, límites de envío, sesiones en JSON y más. Detalle en [05-seguridad.md](05-seguridad.md).
- **Recuperación de contraseña:** se eliminó un controlador propio que duplicaba las rutas de Fortify; ese choque impedía `php artisan route:cache` en producción.

## Decisiones pendientes o deliberadamente postergadas

- **Content-Security-Policy:** no se configuró porque el tema heredado usa scripts en línea y varios CDN. Requiere limpiar el tema primero.
- **Notificaciones por correo** de solicitudes y pagos: hoy el aviso es por el panel y WhatsApp.
- **Facturación electrónica (SRI)** de los pagos Pro: la app no la genera; se hace por fuera.
- **Estructura moderna de Laravel 11+** (`bootstrap/app.php` sin `Kernel`): opcional, no aporta funcionalidad.
- **Términos y privacidad:** son borradores; deben revisarse con un profesional legal (LOPDP).

## Historial de commits relevantes

| Commit | Contenido |
|---|---|
| `225f05c` | Fases 0–2 sobre el tutorial base. |
| `d2e701a` | Fase 3: Laravel 13, Livewire 3, Jetstream 5 y seguridad. |
| `2214269` | Pagos solo por transferencia y efectivo; zona horaria de Ecuador; documentación completa. |
