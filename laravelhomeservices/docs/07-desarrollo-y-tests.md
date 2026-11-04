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

Todo el sitio usa un solo diseño. La portada añade animaciones; las demás páginas, piezas reutilizables:

### Diseño "Valle de noche" (portada)

La dirección visual, la referencia (Halo Lab) y las razones están en [01-historia-y-decisiones.md](01-historia-y-decisiones.md) (fase 4).

- **Layout** `layouts/landing.blade.php`: no carga el tema Bootstrap. El `<body>` lleva `lp`, con un reset propio en `resources/css/app.css` envuelto en `:where()` (especificidad 0, **cualquier clase de Tailwind lo pisa**; mantenlo así).
- **Paleta** (Tailwind): `noche-*` (fondos oscuros), `humo-*` (fondos claros), `grafito` / `grafito-400…600` (texto), `oro-*` (único acento: botón principal y tarjeta de proveedores), `menta-*` (brillo sutil). No agregues más colores de acento.
- **Tipografía:** `font-body` (Inter Tight) para todo. Titulares con la clase `lp-title` (peso 500, interletrado cerrado). Para el acento usa `lp-accent` (Instrument Serif cursiva) en **una sola palabra o frase corta** por titular. Etiquetas de sección: `lp-eyebrow uppercase`.
- **Patrones:** filas con línea fina (`border-t border-humo-300`) y flecha `arrow-up-right` en lugar de tarjetas; botones en píldora con texto `text-[13px] uppercase tracking-[.08em]`; `lp-glass` para elementos sobre fotos; `lp-noise` y `lp-glow` para dar profundidad a secciones oscuras.
- **Sin adornos genéricos:** nada de cápsulas con puntito delante de los antetítulos ni "badges" de colores; las etiquetas son texto (`lp-eyebrow uppercase`).
- **Encabezado:** `<x-site.header />` es de ancho completo y toma el tono de la sección que tiene debajo. **Toda sección nueva de la portada debe llevar `data-tone="dark"` o `data-tone="light"`** según su fondo (el pie ya lo tiene); si falta, el encabezado conserva el tono anterior y puede no contrastar.
- **Componentes** (`resources/views/components/site/`): `<x-site.header />`, `<x-site.footer />`, `<x-site.auth-modal />` (se abre con `$dispatch('open-auth', { mode: 'register', as: 'SVP' })`) y `<x-site.icon name="arrow-up-right" class="h-4 w-4" />` (agrega íconos ahí; no cargues Font Awesome).
- **Fotos:** CDN de Unsplash con el helper `$photo(id, ancho)` al inicio de `home-component.blade.php` (calidad 50, formato automático). Para fotos propias, guárdalas optimizadas (WebP/AVIF, ≤ 250 KB) en `public/images/` y cambia esas URLs. Pon `loading="lazy"` salvo en la foto del hero, y `srcset` en fotos grandes.
- **Animaciones** (`resources/js/landing.js`, entrada propia en `webpack.mix.js`; solo lo carga la portada). Se activan con atributos:

  | Atributo | Efecto |
  |---|---|
  | `data-hero-media`, `data-hero-title`, `data-hero-eyebrow`, `data-hero-lede`, `data-hero-search`, `data-hero-zones` | Entrada del hero y parallax al bajar. |
  | `data-hero-services` + clases `lp-marquee` / `lp-marquee-track` | Cinta de servicios del hero. El movimiento es **CSS** (no GSAP): la lista va duplicada y la pista se desplaza el 50 %; la duración (`--lp-marquee-duration`, 3 s por tarjeta) se calcula en la vista. Se pausa con hover/foco y sigue moviéndose (más lenta) en modo suave. Datos: `ticker` en `HomeComponent`. |
  | `data-words` | Párrafo que se ilumina palabra por palabra con el scroll. |
  | `data-split` | Título que sube línea por línea al entrar en pantalla. |
  | `data-anim`, `data-row` | Aparecen al hacer scroll (en grupo). |
  | `data-preview="url"` + `data-preview-box` | Foto de vista previa que sigue al cursor (solo con ratón; se oculta al salir de la lista o hacer scroll). |
  | `data-tone="dark\|light"` (en secciones) | Tono del encabezado mientras esa sección está debajo (también en modo suave). |
  | `data-stack` | Tarjeta que se reduce y oscurece cuando llega la siguiente (≥ 1024 px). |
  | `data-count="N"` | Contador que sube hasta N. |
  | `data-magnetic` | Botón que sigue un poco al cursor. |

  Reglas: **nunca ocultes contenido en CSS** (anima con `gsap.from`); envuelve en `calm()` las animaciones de elementos con clases `transition` de Tailwind (si no, pueden quedarse en el estado inicial); usa la curva `expo.out` y duraciones largas (1–1,4 s) para mantener la coherencia; y prueba el **modo suave** (`prefers-reduced-motion`) además de la versión completa (`?motion=full`).
- **Compilar:** `npm run dev` mientras trabajas y `npm run prod` antes de subir.

### Páginas internas y paneles (`layouts/base`)

Usan el mismo diseño. Antes de escribir HTML nuevo, reutiliza estas piezas:

| Pieza | Para qué |
|---|---|
| `<x-ui.panel title accent help>` + `<x-slot:actions>` | Marco de toda pantalla de panel: menú del rol, título, frase de ayuda, botones y avisos. El menú y sus contadores se definen ahí. |
| `<x-ui.page title accent eyebrow lede :back>` | Cabecera de páginas públicas. |
| `<x-ui.field label name hint optional>` | Campo con etiqueta, error y ayuda. El `id` del control debe ser `name` (o pasa `for`). |
| `<x-ui.flash />` | Avisos `session('message')`, `session('error')` y resumen de errores. `panel` ya lo incluye; en vistas sin panel agrégalo dentro de la vista Livewire. |
| `<x-ui.status :status :label />` | Estado con color fijo. |
| `<x-ui.empty icon title>` + `actions` | Lista vacía: qué significa y qué hacer. |
| `<x-ui.checklist :items />` | Primeros pasos; se oculta sola al completarse. |
| `<x-ui.stat>`, `<x-ui.help>` + `<x-ui.help-step n>` | Cifra enlazable y explicación plegable. |
| Clases `ui-btn` (`-primary`, `-secondary`, `-ghost`, `-danger`, `-success`, `-gold`, `-whatsapp`, `-sm`, `-lg`), `ui-input`, `ui-input-invalid`, `ui-file`, `ui-card`, `ui-card-pad`, `ui-link`, `ui-prose` | Definidas en `resources/css/app.css`. |

**Reglas para que el sitio siga siendo fácil:**

- Un solo botón principal por pantalla, con un texto que diga qué pasa.
- Toda lista vacía ofrece el siguiente paso, y toda acción destructiva lleva `wire:confirm` con su consecuencia. En formularios, `wire:confirm` va en el `<form>`, no en el botón.
- Los mensajes dicen cómo arreglar el problema, sin palabras técnicas. Los genéricos están en `lang/es/validation.php`.
- Muestra solo acciones válidas: usa `canTransitionTo()` en lugar de dejar que el botón falle.
- En `/livewire/update`, `request()->routeIs()` no sirve: la ruta es la de Livewire. Por eso `x-ui.panel` resuelve la ruta con `Livewire::originalUrl()`.
- Tras enviar un formulario la página va al primer `.ui-error`, y a un aviso `data-flash` si quedó fuera de pantalla (`components/ui/livewire-errors`).

**Tailwind:** se compila con Laravel Mix a `public/css/app.css`. Tras usar clases nuevas: `npm run dev` (o `npm run prod`) y sube el CSS compilado. `preflight` sigue desactivado (el reset propio es `:where(.lp …)`) y `darkMode: 'class'` evita que el sitio cambie a oscuro según el sistema.

**La paginación** de Livewire usa `resources/views/vendor/livewire/tailwind.blade.php` (solo Anterior / Siguiente).

## Tareas frecuentes

**Zonas:** hoy son Malacatos (principal, `main_zone`), Vilcabamba, Yangana y Quinara, en `config/marketplace.php`. Para agregar una, añade `'slug' => 'Nombre'` en `zones` y su línea descriptiva en `HomeComponent::ZONE_NOTES`. No cambies slugs existentes: están guardados en `provider_zones` y en las solicitudes.

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
| `RoleGuidanceTest` | Cada rol ve qué hacer (primeros pasos, pendientes, estados), el rol equivocado recibe una explicación, la validación es clara, los enlaces se numeran solos y hay páginas de error propias. |
| `LandingPageTest` | La portada muestra datos reales (zonas, categorías, destacados, reseñas), no carga el tema pesado (jQuery, Bootstrap, Font Awesome…), modal para invitados y menú para usuarios, y la caché se refresca con nuevas reseñas. |
| `SecurityHardeningTest` | Cabeceras de seguridad, límites de envío, respuesta neutral de recuperación de contraseña, sesiones JSON. |
| Tests de Jetstream (`AuthenticationTest`, `RegistrationTest`, `ProfileInformationTest`, …) | Login, registro, perfil, contraseña, 2FA, sesiones. Los de API y verificación de correo se omiten porque esas funciones están desactivadas. |

Antes de subir cambios: `php artisan test` en verde y, si tocaste vistas, revisarlas en el navegador.
