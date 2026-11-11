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

### Documentos de verificación

El certificado de antecedentes es un dato sensible (LOPDP art. 4 y 26). Por eso:

- Se guarda en el disco privado `receipts`, y solo lo sirve una ruta bajo `authadmin`, con `Cache-Control: private, no-store`.
- Cada documento pide su propio permiso, con la fecha guardada en `consent_at`, aparte de la aceptación de los términos.
- El archivo se borra al revisarlo, y la copia temporal de Livewire al subirlo. Solo queda el resultado, la fecha y quién revisó. Eliminar la cuenta borra los archivos pendientes (`DeleteUser`).
- Se publica el sello y su fecha, nunca el documento. Todo cambio pasa por `App\Services\ProviderVerification`.

### Registro de actividad

`activity_events` no guarda IP, navegador ni mensajes. Lo que escribe la gente va como dato y se neutraliza al exportarlo a Excel (fórmulas). Se borra a los 365 días (`activity:prune`), como promete la política de privacidad. Al eliminar una cuenta, sus filas quedan sin dueño.

### Cuentas suspendidas

`FortifyServiceProvider` rechaza el ingreso con el motivo, `EnsureNotSuspended` (grupo web) cierra la sesión abierta en la siguiente petición y `Service::scopeActive()` esconde sus servicios. Todas las acciones del panel de usuarios vuelven a comprobar que quien las hace es administración, y ninguna se puede aplicar a la propia cuenta.

### Auditoría del 11/11/2026

| Riesgo | Qué se hizo |
|---|---|
| Dependencias con fallas publicadas (6 paquetes de Symfony; `sweetalert2`) | Symfony actualizado a 8.1.11; `sweetalert2` quitado (no se usaba). `composer audit` y `npm audit --omit=dev` quedan en cero. |
| Sin política de contenido (CSP) | `SecurityHeaders` agrega CSP: solo scripts, estilos, fuentes e imágenes propios o de hosts conocidos (Google Fonts, Unsplash, el bucket de fotos, Google Maps si hay clave), `object-src 'none'`, `base-uri` y `form-action` propios, `frame-ancestors 'self'`. Permite `unsafe-inline`/`unsafe-eval` porque Alpine y Livewire los necesitan. También `Cross-Origin-Opener-Policy` y se quita `X-Powered-By`. |
| Archivos privados abiertos en el navegador | `App\Support\PrivateFile`: sin caché, sin indexar y las imágenes aisladas con `sandbox`. Los PDF van sin `sandbox` porque Chrome no los muestra con esa cabecera. |
| Vercel respondía 200 en direcciones de archivos del proyecto (dejaba saber cuáles existen) | `vercel.json` responde 404 a `.env*`, `composer.*`, `vendor/`, `app/`, `routes/`, `docs/`, etc., antes de llegar a PHP. |
| Supabase publica las tablas en una API web (anon/authenticated) | `App\Support\SupabaseLockdown` activa RLS en las tablas de la app (solo las que son del usuario de la app, para no cortarle el acceso) y quita los permisos a esos roles. Lo corren la migración `2026_11_11_000001` y, cada día, `security:check`. |
| Contraseñas filtradas | En producción se rechazan las que aparecen en filtraciones (Have I Been Pwned; solo viajan 5 caracteres del hash). |
| Cuenta de administración | Cada ingreso se avisa por correo (hora, IP y navegador), y el inicio del panel pide activar la verificación en dos pasos. |
| Configuración insegura sin aviso | `php artisan security:check` (en el cron diario, 500 si falla): `APP_DEBUG` apagado, `APP_KEY`, cookie de sesión segura y que ninguna tabla quede expuesta. |

Ya estaban bien: HSTS, cookies `secure`/`httponly`/`samesite=lax`, CSRF, límites de intentos (ingreso 5/min, registro, contacto, solicitudes), recuperación de contraseña que no revela correos, permisos por rol en rutas y en cada acción del admin, consultas parametrizadas y validación del tipo real de los archivos subidos.

Pendiente: revisar en el panel de Supabase (*Advisors → Security*) que no queden avisos, activar la verificación en dos pasos en la cuenta de administración y respaldos periódicos de la base (el plan gratuito de Supabase no guarda respaldos descargables).

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

**No hay Content-Security-Policy** todavía: quedan scripts en línea (Livewire, contador de visitas, mapa). El tema heredado con varios CDN ya se eliminó, así que es la siguiente mejora de seguridad recomendada.

**Rol equivocado:** `AuthAdmin` y `AuthSprovider` bloquean el acceso y redirigen al panel propio del usuario con un aviso, sin cerrar su sesión. También corren en `/livewire/update` (middleware persistente).

## Dependencias

- Laravel 13 y paquetes actualizados (soporte de seguridad vigente). Revisa con `composer audit` y `npm audit`.
- `APP_DEBUG=false` en producción (ya en `.env.production.example`): con `true` se muestran trazas con datos internos.

**Subidas temporales:** nunca en `public/` (disco `uploads`, o el bucket privado en Vercel), porque pueden ser comprobantes de pago. **`/cron/diario`** responde 404 sin `CRON_SECRET`. **`TRUSTED_PROXIES=*`** solo en hosts accesibles únicamente a través de su proxy, como Vercel: en otro servidor permitiría falsificar la IP y saltarse los límites de intentos.
