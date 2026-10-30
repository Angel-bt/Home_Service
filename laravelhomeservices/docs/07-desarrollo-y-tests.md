# 07 · Desarrollo y tests

## Convenciones

- **Idioma:** la interfaz y los mensajes al usuario en español; nombres de clases, métodos y comentarios técnicos pueden estar en inglés (así está el código existente).
- **Archivos nuevos** usan `declare(strict_types=1)` y tipos en las firmas. Los archivos heredados del tutorial no; al editar uno, sigue el estilo de ese archivo.
- **Lógica de negocio fuera de las vistas:** reglas de estados en los modelos (`ServiceRequest`), cambios de plan en `ProPlanBilling`, consultas reutilizables como *scopes* (`Service::scopeInZone`, `scopeSearch`).
- **Formularios repetidos** como trait + partial (`HandlesServiceForm` + `partials/service-form`), no copias.

## Livewire 3: lo que hay que saber

El proyecto empezó en Livewire 2. Diferencias que afectan al escribir código nuevo:

| Tema | Regla |
|---|---|
| Ubicación | Los componentes siguen en `App\Http\Livewire` (configurado en `config/livewire.php`). Layout por defecto: `layouts.base`. |
| `wire:model` | Por defecto se envía **al hacer una acción** (enviar el formulario). Usa `wire:model.live` si el cambio debe actualizar la pantalla al momento (filtros, selects que recalculan algo) y `wire:model.live.debounce.400ms` para buscadores. `.lazy` ahora es `.blur`. |
| Eventos | `$this->dispatch('evento')->to('componente')` (antes `emitTo`). |
| Un solo elemento raíz | Cada vista de componente debe tener **un** elemento raíz. Los `<script>` y `<style>` van dentro de él o en `@push('scripts')`. Si no, Livewire lanza un error en desarrollo. |
| Alpine | Viene incluido con Livewire; no lo importes en `resources/js/app.js`. |
| Límite de envíos | El `throttle` de rutas no cubre las acciones; usa `Concerns\ThrottlesActions`. |

## Jetstream

- Vistas y componentes publicados en `resources/views` (`<x-button>`, `<x-input>`…; ya no existe el prefijo `x-jet-`).
- Las páginas de cuenta usan `layouts/app.blade.php`, que envuelve el layout del sitio. No hay ruta `dashboard`.
- Textos traducidos en `lang/es.json`. Si agregas una vista de Jetstream con textos nuevos, añade sus traducciones ahí.

## Estilos

- Tema Bootstrap 3 en `public/assets` (no se compila).
- Tailwind en `resources/css/app.css`, compilado con Laravel Mix a `public/css/app.css`. Tras usar clases nuevas de Tailwind en una vista: `npm run dev` (o `npm run prod`) y sube el CSS compilado.
- `preflight` está desactivado (no resetea el tema Bootstrap) y `darkMode: 'class'` evita que el sitio cambie a oscuro según el sistema.

## Tareas frecuentes

**Agregar una zona:** añade `'slug' => 'Nombre'` en `config/marketplace.php` → `zones`. No cambies slugs existentes: están guardados en `provider_zones` y en las solicitudes.

**Agregar o renombrar categorías:** desde el panel de admin (`/admin/service-categories`). El seeder `ServiceCategorySeeder` trae las del tutorial (en inglés) y `DemoMarketplaceSeeder` crea categorías locales en español.

**Cambiar el precio o el periodo del plan Pro:** variables `BILLING_PRO_PRICE` y `BILLING_PRO_PERIOD_DAYS`.

**Cambiar el límite del plan gratis:** `FREE_SERVICE_LIMIT`.

**Cachear un objeto nuevo en Redis:** agrega su clase a `serializable_classes` en `config/cache.php`, o guarda arrays en lugar de objetos.

**Nueva acción que cambia el estado de una solicitud:** usa `ServiceRequest::transitionTo()`; no cambies `status` directamente.

**Nueva forma de activar el plan Pro:** crea el `SubscriptionPayment` y llama a `ProPlanBilling::approve()`; no cambies `subscription_plan` a mano.

## Tests

```bash
php artisan test
```

Usan SQLite en memoria, caché y sesión en arreglo y cola síncrona (`phpunit.xml`): no necesitan MySQL ni Redis. Se usa PHPUnit 12, así que los *data providers* se declaran con el atributo `#[DataProvider('metodo')]` y deben ser estáticos.

| Archivo | Qué cubre |
|---|---|
| `MarketplaceSmokeTest` | Todas las páginas públicas, de admin, proveedor y cliente cargan; 404 en registros inexistentes; roles del registro; borrar una categoría no borra proveedores. |
| `MarketplaceFlowTest` | Publicar con imágenes, límite del plan gratis, no tocar servicios ajenos, zonas del perfil, flujo completo solicitud → aceptación → completada → reseña, cancelaciones, búsqueda, filtro por zona, WhatsApp. |
| `ProPlanBillingTest` | Comprobante privado, aviso de efectivo, registro de efectivo por el admin, aprobación y renovación, rechazo, vencimiento, métodos no permitidos, Payphone simulado con `Http::fake()`. |
| `SecurityHardeningTest` | Cabeceras de seguridad, límites de envío, respuesta neutral de recuperación de contraseña, sesiones JSON. |
| Tests de Jetstream (`AuthenticationTest`, `RegistrationTest`, `ProfileInformationTest`, …) | Login, registro, perfil, contraseña, 2FA, sesiones. Los de API y verificación de correo se omiten porque esas funciones están desactivadas. |

Antes de subir cambios: `php artisan test` en verde y, si tocaste vistas, revisarlas en el navegador.
