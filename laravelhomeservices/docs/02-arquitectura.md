# 02 · Arquitectura

## Estructura de carpetas

La app vive en `laravelhomeservices/`. Ejecuta todos los comandos desde ahí.

```
app/
  Actions/Fortify, Actions/Jetstream   Registro, perfil y contraseñas (Fortify/Jetstream)
  Console/Commands                     Comandos programados (visitas, vencimiento de planes)
  Http/Controllers                     Búsqueda, visitas y facturación (lo que no es Livewire)
  Http/Livewire                        Todas las páginas (componentes Livewire de página completa)
    Admin/, Admin/Service/             Panel de administración
    Sprovider/                         Panel del proveedor
    Customer/                          Panel del cliente
    Service/                           Catálogo público, búsqueda y detalle
    Concerns/                          Lógica compartida entre componentes (traits)
  Http/Middleware                      Guards de rol, cabeceras de seguridad
  Http/Responses                       Respuestas personalizadas de Fortify
  Jobs, Mail                           Correos en cola (bienvenida, nuevo contacto)
  Models                               Modelos Eloquent
  Services                             Lógica de negocio sin interfaz (planes, Payphone, archivos)
  Support                              Utilidades (WhatsApp)
config/marketplace.php                 Zonas, límite gratis, contactos públicos
config/billing.php                     Precio del plan Pro y datos de pago
lang/es, lang/es.json                  Traducciones
resources/views/livewire               Vistas de cada componente
resources/views/livewire/partials      Fragmentos reutilizables (encabezado, formulario de servicio, tarjetas…)
resources/views/layouts/base.blade.php Layout del sitio (encabezado, menús, pie)
public/assets                          Tema Bootstrap 3 del tutorial (CSS/JS/imágenes)
public/images                          Imágenes subidas (servicios, categorías, slider, proveedores)
storage/app/receipts                   Comprobantes de pago (privados, fuera de public/)
```

## Cómo se arma una página

Casi todas las páginas son **componentes Livewire de página completa**: la ruta apunta a la clase, la clase prepara los datos en `render()` y devuelve su vista con el layout `layouts.base`.

```php
Route::get('/buscar', SearchComponent::class)->name('search');
```

Las acciones (guardar, aprobar, filtrar) son métodos públicos del componente que Livewire llama por AJAX (`/livewire/update`). Lo que no es interactivo (retorno de Payphone, comprobantes, autocompletado) usa controladores normales.

Las páginas de cuenta de Jetstream (`/user/profile`) usan `layouts/app.blade.php`, que envuelve el layout del sitio para que tengan el mismo encabezado.

## Roles y acceso

`users.utype` define el rol: `ADM` (administrador), `SVP` (proveedor), `CST` (cliente).

| Middleware | Qué hace |
|---|---|
| `auth:sanctum` + `verified` | Exige sesión iniciada. |
| `authadmin` (`AuthAdmin`) | Solo `ADM`. Si no, cierra la sesión y manda al login. |
| `authsprovider` (`AuthSprovider`) | Solo `SVP`. Igual que el anterior. |

Los dos guards de rol también corren en **cada petición Livewire** (registrados como *persistent middleware* en `AppServiceProvider`). Así una acción de un componente de admin no se puede ejecutar sin ser admin, aunque alguien tenga el HTML de la página.

Tras el login, `App\Http\Responses\LoginResponse` envía a cada rol a su panel.

## Mapa de rutas

### Públicas

| Ruta | Página |
|---|---|
| `/` | Portada (destacados, categorías) |
| `/service-category` | Todas las categorías |
| `/{categoría}/services` | Servicios de una categoría, con filtro por zona |
| `/service/{slug}` | Detalle: precio, WhatsApp, solicitud, reseñas |
| `/buscar?q=&zona=` | Búsqueda |
| `/contact-us`, `/about-us`, `/faq`, `/terms`, `/privacy` | Contacto y páginas informativas |
| `/change-location` | Cambiar ubicación (Google Places opcional) |
| `/autocomplete`, `POST /search` | Autocompletado y redirección del buscador antiguo |
| `/login`, `/register`, `/forgot-password`, `/user/profile` | Fortify/Jetstream |

### Proveedor (`authsprovider`)

| Ruta | Página |
|---|---|
| `/sprovider/dashboard` | Resumen: servicios, solicitudes, calificación |
| `/sprovider/profile`, `/sprovider/profile/edit` | Perfil (WhatsApp, zonas, foto) |
| `/sprovider/my-services` | Sus servicios |
| `/sprovider/service/add`, `/sprovider/service/edit/{slug}` | Publicar y editar |
| `/sprovider/requests` | Solicitudes recibidas |
| `/sprovider/plan` | Plan Pro y pagos |

### Cliente (cualquier usuario con sesión)

| Ruta | Página |
|---|---|
| `/customer/dashboard` | Mis solicitudes, cancelar y reseñar |

### Administración (`authadmin`)

| Ruta | Página |
|---|---|
| `/admin/dashboard` | Estadísticas y aviso de pagos pendientes |
| `/admin/service-categories`, `/admin/service-category/add`, `/admin/service-category/edit/{id}` | Categorías |
| `/admin/all-services`, `/admin/{categoría}/services`, `/admin/service/add`, `/admin/service/edit/{slug}` | Servicios |
| `/admin/slider`, `/admin/slide/add`, `/admin/slide/edit/{id}` | Slider de la portada |
| `/admin/service-providers` | Proveedores |
| `/admin/service-requests` | Todas las solicitudes |
| `/admin/payments`, `/admin/payments/{id}/receipt` | Pagos del plan Pro y comprobantes |
| `/admin/contacts` | Mensajes del formulario de contacto |

## Modelos y tablas

| Modelo | Tabla | Qué guarda |
|---|---|---|
| `User` | `users` | Cuenta, rol (`utype`), teléfono, plan (`subscription_plan`, `plan_expires_at`). |
| `ServiceProvider` | `service_providers` | Perfil del proveedor: foto, presentación, ciudad, WhatsApp, categoría principal. |
| `ProviderZone` | `provider_zones` | Zonas que cubre cada proveedor (una fila por zona). |
| `ServiceCategory` | `service_categories` | Categorías (nombre, slug, imagen, destacada). |
| `Service` | `services` | Servicios: dueño (`user_id`), precio, descuento, incluye/no incluye (separados por `\|`), imágenes, visitas. |
| `ServiceRequest` | `service_requests` | Solicitudes: servicio, proveedor, cliente, zona, dirección, fecha, estado. |
| `Review` | `reviews` | Reseñas: 1 a 5 estrellas y comentario, una por solicitud. |
| `SubscriptionPayment` | `subscription_payments` | Pagos del plan Pro: método, monto, periodos, estado, comprobante, quién lo revisó. |
| `Slider` | `sliders` | Diapositivas de la portada. |
| `Contact` | `contacts` | Mensajes del formulario de contacto. |
| `Visit` | `visit_counter` | Contador global de visitas (una sola fila). |

Relaciones principales:

```
User 1─1 ServiceProvider 1─N ProviderZone
User 1─N Service N─1 ServiceCategory
Service 1─N ServiceRequest 1─1 Review
User (cliente) 1─N ServiceRequest
User (proveedor) 1─N SubscriptionPayment
```

Las columnas `stripe_id`, `pm_type`, `pm_last_four` y `trial_ends_at` de `users` vienen del plan inicial con Stripe y no se usan.

## Servicios y utilidades

| Clase | Responsabilidad |
|---|---|
| `Services\ProPlanBilling` | Aprobar, rechazar y vencer planes Pro. Único lugar que cambia el plan de un usuario. |
| `Services\ServicePublicationGate` | Bloquea publicar por encima del límite del plan gratis. |
| `Services\ServiceVisitCounter` | Cuenta visitas de cada servicio en Redis (sin escribir en MySQL en cada visita). |
| `Services\ServiceAssetCleaner` | Borra imágenes públicas con rutas absolutas. |
| `Services\PayphoneGateway` | Cliente de Payphone (desactivado). |
| `Support\WhatsApp` | Normaliza números ecuatorianos y arma enlaces `wa.me`. |
| `Livewire\Concerns\HandlesServiceForm` | Campos, validación y guardado del formulario de servicio (admin y proveedor). |
| `Livewire\Concerns\FiltersByZone` | Filtro por zona recordado en la sesión. |
| `Livewire\Concerns\ThrottlesActions` | Límite de envíos para acciones Livewire. |

## Caché, colas y tareas programadas

- **Portada:** categorías, destacados y slider se guardan en caché (`home:*`, 1 hora por defecto). Se invalidan solos al guardar o borrar un servicio, categoría o diapositiva. Si editas la base a mano: `php artisan cache:clear`.
- **Visitas a servicios:** contadores en Redis; `services:sync-visit-counters` los pasa a MySQL cada 5 minutos.
- **Planes Pro:** `billing:expire-plans` cada hora pasa a gratis los planes vencidos.
- **Correos:** bienvenida y aviso de nuevo contacto van a la cola `mail` (3 reintentos).

Ver [06-despliegue-y-operacion.md](06-despliegue-y-operacion.md) para configurarlos en el servidor.
