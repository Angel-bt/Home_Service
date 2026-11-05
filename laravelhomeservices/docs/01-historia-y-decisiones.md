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

## Fase 4 · Rediseño UX/UI de la landing: "Valle de noche"

Objetivo: una primera impresión **profesional**, clara y propia de Malacatos, que invite a explorar sin ser pesada.

### Cómo se llegó a este diseño (tres iteraciones)

1. **"El valle a un toque"** (fondo crema, tarjetas redondeadas tipo *bento*, píldoras, chat con emojis). Correcta y liviana, pero **se veía genérica, "hecha con IA"**.
2. **"Oficios del valle"** (grabado y risografía: papel con grano, sellos de linóleo, recortes con cinta, cartel con tiras). Tenía identidad, pero se sintió **infantil y amontonada**.
3. **"Valle de noche"** (actual), tomando como referencia **[Halo Lab](https://www.halo-lab.com/)**: pocos elementos, mucho aire, una imagen protagonista por pantalla y tipografía sobria. Se tomó el *lenguaje* de la referencia, no su contenido ni sus recursos.

### Qué se tomó de la referencia y cómo se adaptó

| Halo Lab | Malakplace |
|---|---|
| Fondo azul noche con secciones claras intercaladas | Verde noche (`noche-*`), propio de los cerros al anochecer, alternado con secciones "humo" (`humo-*`) |
| Una grotesk neutra en peso medio (Suisse) | **Inter Tight** 400/500 para todo |
| Acentos en serif cursiva ("Design", "13 years") | **Instrument Serif** cursiva solo en la palabra clave de cada titular ("*en Malacatos.*", "*arreglar?*") y en las cifras |
| Una imagen 3D protagonista en el hero | Foto real del valle (desde el Mandango, sobre Vilcabamba) a pantalla completa, con velo oscuro |
| Filas con línea fina y flecha ↗ en lugar de tarjetas | Franja de zonas y lista de oficios como filas con ↗ |
| Casos de estudio a pantalla completa | "Trabajos recomendados" como tarjetas a pantalla completa que se **apilan** al hacer scroll |
| Cifras grandes en cursiva entre líneas | Proveedores, servicios y reseñas en Instrument Serif |
| Un solo acento de color (amarillo) | Un solo acento **oro** (`oro-*`): botón principal y la tarjeta de proveedores |

### Estructura (siete bloques, sin repetir mensajes)

1. **Hero:** "Gente de oficio de confianza, *en Malacatos.*", buscador (qué + zona), una **cinta de servicios** que pasa sola (foto, nombre, oficio, proveedor, precio y calificación de hasta 12 servicios) y franja con las **4 zonas**, Malacatos marcada como *Principal*. La cinta existe para que quien entra vea servicios reales al instante, sin tener que buscar ni entrar a ningún lado; se pausa al pasar el cursor y cada tarjeta lleva al detalle.
2. **Manifiesto:** una frase que explica qué es Malakplace (se ilumina palabra por palabra) y tres cifras reales. Se quitó el remate "Sin intermediarios ni comisiones" a pedido del dueño del proyecto.
3. **Oficios:** lista de 8 oficios en dos columnas; en escritorio aparece una foto del servicio junto al cursor.
4. **Trabajos recomendados:** hasta 4 servicios a pantalla completa, apilándose.
5. **Cómo funciona:** tres pasos numerados.
6. **Reseña destacada** (solo si hay reseñas).
7. **Para proveedores:** tarjeta dorada con beneficios y foto de montaña.

El pie incluye el contacto por WhatsApp, por eso no hay un cierre repetido.

### Zonas: solo Malacatos y tres parroquias vecinas

El mercado real es pequeño, así que la plataforma se limita a **Malacatos (zona principal), Vilcabamba, Yangana y Quinara** (`config/marketplace.php` → `zones` y `main_zone`). La migración `2026_10_30_000001_limit_zones_to_malacatos_parishes` borra las zonas de proveedores que ya no existen (Loja, Catamayo, San Pedro de Vilcabamba); las solicitudes antiguas conservan su zona como texto histórico.

### Animaciones

GSAP + ScrollTrigger + SplitText y Lenis (desplazamiento suave), en `resources/js/landing.js`, que solo carga la portada. Todas son lentas y con la misma curva (`expo.out`):

- **Hero:** la foto entra con un zoom lento; el titular sube por líneas; al bajar, la foto hace parallax y el texto se desvanece.
- **Manifiesto:** las palabras pasan de gris a negro a medida que se leen.
- **Títulos** por líneas, **filas** y bloques que aparecen al entrar, **contadores**.
- **Oficios:** foto de vista previa que sigue al cursor. Se decide en cada movimiento del puntero qué fila está debajo y se oculta al salir de la lista, al hacer scroll, al cambiar de pestaña o al perder el foco (antes podía quedarse "pegada" sobre otras secciones).
- **Recomendados:** cada tarjeta, al llegar la siguiente, se reduce y oscurece (solo escritorio).
- **Botones** con un leve efecto magnético.

**Movimiento reducido:** si el sistema tiene "reducir movimiento" activado (Windows: *Mostrar animaciones en Windows* desactivado), la página usa un **modo suave** (solo fundidos y contadores). Con `?motion=full` en la URL se ve la versión completa. **Excepción:** la cinta de servicios del hero sigue moviéndose en modo suave (1,6 veces más lenta), porque es contenido que se pidió ver pasar, no un adorno; se pausa con el cursor o el foco. Sin JavaScript la página se ve completa.

### Rendimiento

| | Original (tutorial) | Ahora |
|---|---|---|
| Peticiones | 73 | ~14 (las fotos de servicios se cargan al acercarse) |
| Peso inicial aprox. en producción (gzip) | ~3,4 MB | ~400 KB (foto del hero ~180–270 KB según pantalla, 2 tipografías ~66 KB, Livewire+Alpine ~57 KB, animaciones ~50 KB) |

### Otras decisiones

- **Nombre: Malakplace** (Malacatos + *marketplace*). Lo eligió el dueño del proyecto para que la plataforma tenga un nombre propio y local; **Proyetech queda como la empresa detrás** (pie: "Malakplace, un proyecto de Proyetech"). Se cambió en la portada, el layout heredado, términos, FAQ, contacto, nosotros, mensajes de WhatsApp, el aviso de registro, el concepto del cobro Payphone y `APP_NAME`. Los correos demo (`*@proyetech.test`) y las clases CSS heredadas `proyetech-*` se dejaron igual.
- La portada usa su propio layout (`layouts/landing`), sin el tema Bootstrap; el **slider del admin no se muestra** en la portada.
- Encabezado, pie, modal de acceso e íconos son componentes (`components/site/*`) para llevar el mismo estilo al resto del sitio.
- **Encabezado a todo lo ancho** (reemplazó a la barra flotante en cápsula, que no convencía): marca tipográfica *Malak**place*** ("place" en la cursiva de acento), enlaces en mayúsculas pequeñas. Es transparente sobre el hero, se oculta al bajar y reaparece al subir con fondo translúcido. **Cambia de tono según la sección que tiene debajo**: cada sección declara `data-tone="dark|light"` y `landing.js` (`headerTone()`) lo copia al encabezado; los colores están en `.lp-header` (`app.css`). El logo PNG sigue en `public/images/logo.png`.
- Se quitaron adornos que delataban una plantilla genérica: la cápsula con punto del antetítulo del hero y la píldora dorada de "Principal" (ahora es texto en mayúsculas).
- Datos en un solo arreglo cacheado (`home:landing`, máx. 10 min); la caché no deserializa objetos (ver [05-seguridad.md](05-seguridad.md)).
- Las fotos son de Unsplash (Vilcabamba y montañas del sur de Loja; no hay fotos libres de Malacatos). Conviene reemplazarlas por fotos propias de Malacatos cuando las haya.
- Las categorías del seeder del tutorial están en inglés ("AC", "Car"…); conviene renombrarlas o borrarlas desde el admin.

## Fase 5: todo el sitio con el mismo diseño y pensado para cada rol

**Pedido:** aplicar el estilo de la portada a toda la página, que funcione bien y que sea muy simple: cualquier error debe explicarse y cada rol debe entender qué hacer.

**Decisión de fondo:** se reescribió `layouts/base` y todas las vistas sin Bootstrap, jQuery, Font Awesome, SweetAlert ni Chart.js. Las piezas repetidas pasaron a clases `ui-*` (`resources/css/app.css`) y componentes `components/ui/*` para que el resto del sitio no vuelva a divergir.

### Principios de usabilidad aplicados

1. **Lo próximo que hay que hacer siempre está a la vista.** Cada panel empieza por lo pendiente, no por estadísticas.
2. **Una frase bajo cada título** dice para qué sirve la página.
3. **Los botones dicen qué pasa** («Aprobar y activar Pro», «Ya terminé»), no «Enviar» o íconos sin texto.
4. **Ninguna pantalla vacía sin salida:** cada lista vacía explica por qué y ofrece el siguiente paso.
5. **Los errores dicen cómo arreglarlo,** junto al campo y con un resumen; la página se desplaza hasta el primero.
6. **Lo destructivo pide confirmación** explicando la consecuencia.

### Qué ve cada rol

| Rol | Al entrar | Ayuda concreta |
|---|---|---|
| **Proveedor** | Aviso grande si hay clientes esperando, y «Para empezar a recibir clientes»: WhatsApp y zonas → foto y presentación → primer servicio. Se marca solo y desaparece al completarse. | Solicitudes como tarjetas: datos del cliente, qué toca hacer ahora y solo los botones válidos. Mi plan numerado paso a paso. |
| **Cliente** | Sus solicitudes con una barra de avance (Enviada → Aceptada → Terminada) y una frase que explica el estado. Sin solicitudes, cómo funciona en 3 pasos. | WhatsApp del proveedor, calificar con estrellas y, si lo rechazan, «Buscar otro proveedor». |
| **Administración** | «Qué tienes pendiente»: pagos por revisar, solicitudes con más de un día sin respuesta, mensajes de la semana, proveedores con perfil incompleto. O «Todo al día». | Contadores en el menú y pagos con la pregunta clave: ¿recibiste el dinero? |

### Cambios de comportamiento

- **Rol equivocado:** antes cerraba la sesión y mandaba al login sin explicar nada. Ahora lleva al panel propio con un aviso («Esa página es solo para la administración»). El acceso sigue bloqueado; solo cambia la respuesta.
- **Después de ingresar o registrarse:** proveedores y administración llegan a su panel (`RoleHomeResponse`), donde ven qué hacer; los clientes siguen a donde iban o a la portada.
- **Enlace del servicio:** se crea solo y se numera si ya existe (`poda-de-arboles-2`). Queda en «Opciones avanzadas».
- **Panel de administración:** se quitaron el gráfico con datos inventados y la tabla con cada visita. Las visitas se reinician con una acción de Livewire.
- **Mensajes de validación** reescritos en lenguaje simple («Falta el nombre.», `lang/es/validation.php`).
- **Páginas de error propias** (403, 404, 419, 429, 500, 503) en `resources/views/errors/`, sin base de datos ni sesión, para que se vean aunque el sitio falle.
- **Fallos de Livewire** (sin internet, sesión vencida, error del servidor): aviso en español en lugar del de Livewire en inglés; una sesión vencida recarga la página sola (`components/ui/livewire-errors`). Con `APP_DEBUG=true` se ve el error técnico.
- **Eliminar una categoría** también borra sus servicios (la base de datos lo hace en cascada), y la confirmación ahora lo advierte con el número exacto.

## Limpieza de lo que dejó de usarse

Con el rediseño ya guardado se quitó lo que nada usaba (se comprobó buscando referencias a cada cosa, y todo sigue en el historial de git):

- **Tema Bootstrap 3:** toda la carpeta `public/assets` (150 archivos). Lo único que se usaba era el favicon, que ahora está en `public/images/favicon.png`.
- **Estilos viejos:** `public/css/proyetech.css` y el bloque `proyetech-*` de `resources/css/app.css` (500 líneas). El CSS compilado bajó de 117 KB a 86 KB.
- **Vistas de Jetstream sin ruta:** `welcome`, `dashboard` y `navigation-menu`, junto con los componentes que solo ellas usaban.
- **Términos y privacidad de Jetstream:** `terms`, `policy`, `layouts/guest`, `GuestLayout` y `resources/markdown`. Esa función de Jetstream está apagada y el sitio tiene sus propias páginas `/terms` y `/privacy`, editables desde el panel. **No activar `Features::termsAndPrivacyPolicy()`:** sus vistas ya no existen.
- **Copia antigua en la raíz del repositorio:** la raíz guardaba una versión de septiembre de 2026 de la app (`app/`, `public/`, `resources/`…, 492 archivos) que nadie usaba. Allí solo quedan `laravelhomeservices/`, `README.md`, `AGENTS.md` y la configuración de git y del editor. La copia sigue en el historial de git.
- **`.git` dentro de `laravelhomeservices/`:** era un repositorio viejo, de 5 commits ya incluidos en el principal, que apuntaba al mismo GitHub. Ahora git funciona igual desde cualquier carpeta del proyecto.

## Fase 6: recorrido completo y preparación para Vercel

**Pedido:** recorrer el flujo completo y dejarlo listo para desplegar en Vercel.

Se recorrió en el navegador, con tres roles y archivos reales: registro, perfil con foto, publicar, buscar por zona, solicitar, aceptar, terminar, reseñar, pagar con comprobante, rechazar con motivo, reintentar en efectivo y aprobar. Luego se repitió con la configuración de Vercel, sobre una base vacía, con S3 simulado, sin Redis y con el correo caído. Los detalles de la adaptación están en [06-despliegue-y-operacion.md](06-despliegue-y-operacion.md).

**Errores que encontró el recorrido:**

- **Comprobantes en una carpeta pública:** las subidas temporales de Livewire quedaban en `public/images/livewire-tmp`, accesibles desde la web y algunas incluso en git. Ahora van a un disco privado (`uploads`).
- **Correo caído:** «olvidé mi contraseña» daba error 500, y sin cola el registro y el contacto habrían hecho lo mismo. Ahora se explica al usuario o se registra el error, sin perder la acción.
- **Avisos perdidos:** el ping de visitas de cada página podía consumir el mensaje de un formulario enviado justo en ese momento. Ahora solo sale en la primera página de la sesión.

**Publicación:** se eligió Supabase desde el Marketplace de Vercel porque da base de datos y almacenamiento en un solo servicio, sin cuentas aparte. Implica Postgres en vez de MySQL. La app ya era casi compatible; lo que cambió es la búsqueda: Postgres distingue mayúsculas y tildes, y buscar «tuberias» no encontraba «tuberías». Se detectó probando el sitio ya publicado y se corrigió en `Service::scopeSearch()`.

## Fase 7: formularios para gente real

**Pedido:** mejorar el formulario y dejar el sitio listo para que entre gente.

Se recorrieron los tres roles en vista de celular, desde una base vacía, con Postgres y la configuración de producción. Lo que cambió:

- **Publicar un servicio:** solo pide nombre, oficio, precio, una foto y dos líneas. Lo demás quedó en «Más detalles». Antes exigía dos fotos y cuatro textos.
- **Fotos del celular:** ahora se achican en el navegador antes de subir (`components/ui/photo-input`). Una de 10,6 MB queda en unos 660 KB; antes el límite de 4 MB la rechazaba y el proveedor no podía publicar. No requiere GD en el servidor, que Vercel no tiene.
- **Registro:** sin repetir la contraseña (se puede ver con el ojo), con el celular validado para WhatsApp, y un freno a registros automáticos.
- **Cliente:** vuelve a la página donde estaba tras registrarse, y al solicitar ve primero «¿Qué necesitas?» con sus datos resumidos.
- **Correos sin distinción de mayúsculas:** Postgres sí las distingue y alguien podría no entrar a su cuenta (`fortify.lowercase_usernames`).
- **Sitio sin servicios:** invita a sumarse en lugar de mostrar tres ceros.
- **Oficios:** `MarketplaceCategoriesSeeder` trae 14 reales, con «Otros oficios» para quien no encaje.
- **Sin SMTP:** «olvidé mi contraseña» manda a WhatsApp en lugar de prometer un correo que no saldría.
- **Lanzamiento (04/11/2026):** con autorización del dueño se borraron los datos de demostración, porque tenían teléfonos inventados que podrían ser de personas reales. Su cuenta pasó a administrador y el sitio quedó abierto a los buscadores.

## Fase 8: confianza, verificación y actividad

El objetivo: saber quién está en la plataforma, dejar claro que Malakplace solo conecta a las personas, darle a la gente razones para volver y tener datos para decidir.

**Términos antes de registrarse.** El formulario tiene dos pasos: primero el tipo de cuenta, un resumen en palabras simples y las casillas; recién entonces los datos. El servidor vuelve a exigirlo. Un proveedor sin permiso de publicación no puede registrarse. Se guarda qué versión se aceptó y cuándo. Las cuentas anteriores aceptan al entrar (`EnsureTermsAccepted`), y todas vuelven a hacerlo si sube `marketplace.terms_version`.

**La verificación es voluntaria, y eso es una decisión legal, no de producto.** Se pidió exigir antecedentes y publicar «sin antecedentes penales». Se mantuvo el sello, pero el documento no es obligatorio, porque en Ecuador:

- La LOPDP (art. 4) define el pasado judicial como dato sensible, y su art. 26 prohíbe tratarlo salvo, entre otros casos, con consentimiento explícito y fines claros. Su art. 8 exige que el consentimiento sea libre y se pueda revocar en cualquier momento.
- La Constitución (art. 11.2) prohíbe discriminar por pasado judicial.
- Hay fuentes secundarias que dicen que el Decreto 1166 impide exigir el certificado como requisito. No se pudo confirmar con el texto oficial, y es un motivo más para no hacerlo obligatorio.

Por eso: quien no tiene sello no aparece marcado de ninguna forma; si el certificado registra algo, simplemente no se da el sello y la persona sigue trabajando; los documentos se borran apenas se revisan; el proveedor puede retirarlos cuando quiera. El sello dura 180 días (el certificado oficial vale 3 meses) y muestra su fecha, porque solo afirma lo que se revisó ese día. **Debe revisarlo un abogado.**

**Intermediario.** Términos, registro, ficha de servicio y preguntas frecuentes dicen que Malakplace solo conecta y no responde por robos, daños ni incumplimientos. Se escribió «en todo lo que la ley permita» porque el art. 43 de la Ley de Defensa del Consumidor anula las cláusulas que eximen de responsabilidad o hacen renunciar a derechos: una exención total sería más débil, no más fuerte. Estos textos reducen el riesgo, pero no lo eliminan. Tampoco conviene prometer lo contrario en la publicidad: el titular de la portada todavía dice «de confianza».

**Actividad.** Tabla `activity_events` y panel `/admin/activity`: lo más buscado, lo buscado sin resultados (qué oficios faltan), servicios más vistos y contactos por WhatsApp. No guarda IP ni mensajes, no cuenta robots ni a la administración, y se borra a los 365 días. Se hizo con tabla propia y no con Google Analytics para no pedir permiso de cookies a terceros y para poder mostrarle a cada proveedor sus números.

**Retención.** No había avisos: si un proveedor no entraba a su panel, nunca se enteraba de que tenía una solicitud. Ahora hay correos en cada paso de la solicitud, una invitación a dejar reseña al terminar el trabajo y, en el panel del proveedor, sus visitas y contactos de los últimos 30 días.

**Cómo ganar dinero: plan, no comisión.** El cliente le paga directo al proveedor, fuera de la plataforma, así que una comisión no se puede cobrar ni comprobar: basta con cerrar el trato por WhatsApp. Además, cobrar sobre cada trabajo acercaría a Malakplace a ser parte del negocio, justo lo contrario de ser solo intermediario. La recomendación es mantener la suscripción, no cobrar hasta tener demanda real, y cambiar lo que vende: casi ningún oficial necesita más de 3 servicios, de modo que el límite actual no es una razón para pagar. Pro debería vender visibilidad (salir primero) y estadísticas. Los sellos no deben cobrarse nunca: un sello pagado deja de ser creíble.

## Decisiones pendientes o deliberadamente postergadas

- **Content-Security-Policy:** no se configuró porque el sitio aún tiene scripts en línea (Livewire, contador de visitas, mapa). El tema heredado que usaba varios CDN ya se eliminó.
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
| `6039de7` | Fase 4: nueva landing "Valle de noche" (inspirada en Halo Lab) y zonas limitadas a Malacatos, Vilcabamba, Yangana y Quinara. Fase 5: todo el sitio con ese diseño y paneles guiados por rol. |
| `6584a0a` | Limpieza del tema Bootstrap, los estilos `proyetech-*` y las vistas sin uso de Jetstream. |
| `9347892` | Fase 6: preparación para Vercel y errores del recorrido completo. |
| siguiente | Limpieza de la copia antigua de la app en la raíz del repositorio. |
