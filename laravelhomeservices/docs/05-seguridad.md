# 05 · Seguridad

Medidas aplicadas, qué protegen y dónde están. Están cubiertas por `tests/Feature/SecurityHardeningTest.php` y los tests de cada módulo.

## Acceso por rol

| Medida | Dónde | Por qué |
|---|---|---|
| Guards `authadmin` / `authsprovider` en las rutas | `routes/web.php` | Cada panel solo para su rol. |
| Los mismos guards en **cada petición Livewire** | `AppServiceProvider` (`Livewire::addPersistentMiddleware`) | Las acciones de Livewire (borrar, aprobar, subir archivos) van por `/livewire/update`, no por la ruta de la página. Sin esto, alguien que perdió el rol podría seguir ejecutando acciones. |
| Comprobaciones de dueño en el código | Componentes del proveedor, `ServiceRequest` | Un proveedor solo edita sus servicios y solo cambia sus solicitudes; un cliente solo cancela y reseña las suyas. |
| Registro limitado a `CST` / `SVP` | `CreateNewUser` | Nadie puede crearse una cuenta de administrador desde el formulario. |

## Datos y archivos

| Medida | Dónde | Por qué |
|---|---|---|
| Comprobantes de pago en disco privado | Disco `receipts` → `storage/app/receipts` | Contienen datos bancarios; no deben tener URL pública. Solo el admin los descarga. |
| Imágenes: solo imágenes reales, máx. 4 MB | Formularios de servicio, categorías, slider y perfil | Evita subir ejecutables o archivos enormes a `public/`. |
| Nombres de archivo aleatorios | `HandlesServiceForm`, perfil, comprobantes | Evita choques y adivinar nombres. |
| Borrado de archivos con rutas absolutas | `ServiceAssetCleaner` | Antes se usaban rutas relativas que dependían del directorio actual. |

## Formularios y abuso

| Medida | Límite | Dónde |
|---|---|---|
| Formulario de contacto | 5 cada 10 min por IP | `ContactComponent` |
| Solicitudes de servicio | 5 cada 10 min por usuario | `ServiceDetailsComponent` |
| Comprobantes / avisos de pago | 5 por hora por usuario | `SproviderPlanComponent` |
| Contador de visitas | 60 por minuto | ruta `increment-visit` |
| Login y 2FA | Fortify (5 por minuto) | `FortifyServiceProvider` |

Los límites de las acciones Livewire usan `Concerns\ThrottlesActions`, porque el `throttle` de las rutas no cubre `/livewire/update`.

**Recuperación de contraseña:** con un correo no registrado la respuesta es idéntica a la de uno registrado (`NeutralPasswordResetLinkResponse`). Así nadie puede averiguar qué correos tienen cuenta.

## Sesiones y caché

| Medida | Por qué |
|---|---|
| Sesiones serializadas en **JSON** (`SESSION_SERIALIZATION=json`) | Si alguien manipulara los datos de sesión, no podría inyectar objetos PHP (ataques de deserialización). |
| La caché **no deserializa ningún objeto** (`cache.serializable_classes = false`) | Misma protección para Redis. La app solo cachea arreglos y valores simples. Si algún día necesitas cachear objetos, lista sus clases ahí. |
| Cookie de sesión `secure` en producción (`SESSION_SECURE_COOKIE=true`) | Solo viaja por https. |

## Cabeceras HTTP

`App\Http\Middleware\SecurityHeaders` añade a todas las respuestas:

- `X-Content-Type-Options: nosniff` — el navegador no adivina tipos de archivo.
- `X-Frame-Options: SAMEORIGIN` — nadie puede meter el sitio en un iframe ajeno (clickjacking).
- `Referrer-Policy: strict-origin-when-cross-origin` — no filtra URLs completas a otros sitios.
- `Permissions-Policy` — desactiva cámara, micrófono y pagos del navegador; geolocalización solo para el propio sitio.
- `Strict-Transport-Security` — solo cuando la petición llega por https.

**No hay Content-Security-Policy** todavía: quedan scripts en línea (Livewire, contador de visitas, mapa). El tema heredado con varios CDN ya no se carga, así que es la siguiente mejora de seguridad recomendada.

**Rol equivocado:** `AuthAdmin` y `AuthSprovider` bloquean el acceso y redirigen al panel propio del usuario con un aviso, sin cerrar su sesión. También corren en `/livewire/update` (middleware persistente).

## Dependencias

- Laravel 13 y paquetes actualizados (soporte de seguridad vigente). Revisa con `composer audit` y `npm audit`.
- `APP_DEBUG=false` en producción (ya en `.env.production.example`): con `true` se muestran trazas con datos internos.
