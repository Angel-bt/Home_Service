# 09 · Plan para llevar Malakplace a nivel profesional

Escrito el 16/11/2026. Complementa el plan de diseño (`08-plan-de-rediseno.md`). Cada punto sale de una medición del proyecto, no de una lista genérica.

## Dónde estamos (medido)

| Tema | Hoy | Por qué importa |
|---|---|---|
| Plataforma | Vercel con `vercel-php@0.8.0`, un adaptador **comunitario y sin mantenimiento oficial** | Si deja de funcionar con una versión nueva de PHP o de Vercel, el sitio se cae y no hay a quién reclamar. Además, sin colas ni tareas programadas reales. |
| Base de datos | Supabase gratuito | **Sin copias de seguridad descargables**: un borrado por error no tiene vuelta atrás. |
| Correo | Gmail con contraseña de aplicación | Unos 500 correos al día y riesgo de caer en spam con el volumen. |
| Calidad automática | 0 flujos de integración continua; sin análisis estático ni formato automático | Cada cambio depende de que alguien recuerde correr las 319 pruebas. |
| Permisos | 66 comprobaciones `utype === …` repartidas; 0 políticas | Una comprobación olvidada en una pantalla nueva es un agujero de seguridad. |
| Código | 142 archivos y 8.200 líneas en `app/`; 52 componentes; 57 archivos de la época del tutorial sin tipos estrictos; 0 enums | Mezcla de dos estilos; los estados (`pending`, `ADM`…) son textos sueltos. |
| Monitoreo | Errores en `/admin/errors` y una revisión diaria | Nadie se entera de una caída hasta que alguien entra a mirar. |
| Entornos | Solo producción y la computadora local | Lo que se prueba nunca es idéntico a lo que se publica. |

Lo que ya está bien y se conserva: 319 pruebas (unitarias, permisos, flujos, recorrido de todo el sitio y seguridad), cabeceras y política de contenido, API de Supabase cerrada, historial de la administración, panel para operar sin código y documentación al día.

## Principio rector

Primero lo que evita **perder el negocio** (datos, caídas, accesos indebidos). Después lo que evita **romperlo al cambiarlo** (arquitectura y calidad). Y recién entonces lo que lo hace **crecer** (escala). Construir para miles de usuarios antes de tener cien cuesta más de lo que rinde. La escala se prepara con un diseño limpio y se activa cuando las cifras lo piden.

## Fase A · Cimientos (1–2 semanas) — no perder nada

**Avance al 18/11/2026.** Hechos en el código: copias diarias cifradas con prueba de restauración semanal (1), integración continua con pruebas en SQLite y Postgres, Larastan, Pint y auditorías (2), vigilante cada 15 minutos con `/salud` y correo ante errores nuevos (4, sin Sentry por ahora) y dos pasos obligatorio para administración (7). Faltan los que necesitan cuentas o decisiones tuyas: proteger `main` en GitHub (2), staging (3), Sentry (4, opcional), correo transaccional (5) y dominio con Cloudflare (6).

1. **Copias de seguridad con restauración probada.** Diarias, guardadas fuera de Supabase (en el almacén privado) y con 30 días de historial. Una vez al mes, restaurar en un esquema de prueba y comprobar que todo está. Alternativa: el plan pago de Supabase, que permite volver a cualquier momento.
2. **Integración continua en GitHub.** En cada cambio, automáticamente:
   - las pruebas en SQLite **y en Postgres**;
   - análisis estático (Larastan) y formato (Pint);
   - `composer audit` y `npm audit`.

   Si algo falla, no se publica (rama `main` protegida).
3. **Entorno de pruebas** (`staging`) con su propia base, idéntico a producción. Todo pasa por ahí antes de publicarse.
4. **Monitoreo con alertas.** Errores con contexto (Sentry o similar) y un vigilante de disponibilidad que avisa por correo o WhatsApp si el sitio no responde.
5. **Correo transaccional** (Resend, Postmark o Amazon SES) con dominio propio verificado (SPF, DKIM y DMARC), en lugar de Gmail.
6. **Dominio propio detrás de Cloudflare:** firewall, protección contra ataques, caché de imágenes y certificados.
7. **Verificación en dos pasos obligatoria** para cuentas de administración, y sesión corta para el panel.

## Fase B · Plataforma soportada (2–3 semanas)

Salir del adaptador comunitario a un servicio que soporte Laravel completo: colas con trabajadores, tareas programadas reales y Redis.

| Opción | A favor | En contra |
|---|---|---|
| **Laravel Cloud** | Hecho para Laravel; colas, programador y base gestionados; casi sin mantenimiento | Costo mensual fijo |
| Servidor propio (VPS) con Forge o Ploi | Más barato con volumen; control total | Alguien debe cuidar el servidor |
| Contenedores (Fly.io, Railway) | Flexible, escala por partes | Más configuración |

Recomendación: **Laravel Cloud** si no hay una persona técnica fija; un servidor con Forge si la hay. Con cualquiera de las dos:
- los correos, el procesamiento de fotos y los avisos van a una **cola** y dejan de demorar la página;
- el programador corre cada minuto, en lugar de una vez al día;
- Redis se usa para la caché, las sesiones, los límites de intentos y el contador de visitas.

## Fase C · Arquitectura (continua, 4–6 semanas para lo principal)

Objetivo: que agregar una función no obligue a tocar diez archivos ni abra huecos de seguridad.

1. **Módulos por dominio**, cada uno con sus reglas en un solo lugar: Cuentas, Catálogo, Solicitudes, Pagos, Verificación, Contenido y Analítica (`app/Domain/<Módulo>`).
2. **Casos de uso como Acciones** (`PublicarServicio`, `AceptarSolicitud`, `AprobarPago`…). Los componentes de pantalla solo muestran y llaman acciones, así la misma regla sirve para la web, una futura app y la API.
3. **Políticas y permisos centralizados** (Laravel Policies y Gates) en lugar de las 66 comprobaciones sueltas, más **roles de administración** separados: dueño, moderación y finanzas.
4. **Enums** para roles y estados (`RequestStatus`, `PaymentStatus`, `Role`): sin textos mágicos y con el compilador vigilando.
5. **Eventos y notificaciones de Laravel** (`SolicitudCreada`, `PagoAprobado` → correo hoy, WhatsApp mañana) en lugar de enviar correos desde los modelos.
6. **Formularios como objetos** (Livewire Forms) con su validación, reutilizables.
7. **Limpiar lo heredado del tutorial:**
   - borrar el carrusel (`Slider`), las páginas de administración que ya no se usan y `LocationComponent`;
   - poner tipos estrictos en los 57 archivos que no los tienen.
8. **Pruebas de arquitectura** que impidan volver atrás: por ejemplo, que una pantalla no consulte la base sin pasar por su módulo, o que toda acción del admin tenga su política.
9. **Análisis estático** subiendo de nivel hasta el máximo de Larastan.

## Fase D · Seguridad avanzada (2–3 semanas, en paralelo con C)

- **Anti‑robots** (Cloudflare Turnstile) en registro, contacto y solicitudes.
- **Verificación del correo**, y del celular por código cuando haya WhatsApp Business.
- **Confirmar la contraseña** antes de acciones graves del panel (eliminar cuentas, descargar datos, publicar términos).
- **Política de contenido sin `unsafe-eval`**, usando la versión de Alpine compatible con CSP.
- **Rotación de claves** documentada y un **inventario de secretos**.
- **Prueba de intrusión externa** antes de mover dinero dentro de la plataforma.
- **LOPDP completa:**
  - registro de actividades de tratamiento;
  - descarga y borrado de los propios datos desde la cuenta;
  - evaluar la figura del delegado de protección de datos;
  - un **plan de respuesta a incidentes** escrito: qué hacer, a quién avisar y en qué plazo.

## Fase E · Escala (cuando las cifras lo pidan)

Disparadores sugeridos: más de 500 servicios, más de 5.000 visitas al día, o la expansión a otro cantón.

- **Búsqueda:** texto completo de Postgres con `unaccent` y trigramas (tolera errores de tipeo) o un motor dedicado (Meilisearch o Typesense).
- **Fotos:**
  - se procesan en cola, en varios tamaños y en WebP;
  - se sirven por CDN;
  - las de celular dejan de pesar más de lo necesario.
- **Geografía jerárquica** (provincia → cantón → parroquia) en lugar de la lista fija de zonas, para crecer más allá de Malacatos sin tocar código.
- **API versionada** (`/api/v1`, Sanctum) para una futura app móvil.
- **Rendimiento:**
  - índices revisados;
  - detección de consultas repetidas (`preventLazyLoading`) en desarrollo;
  - registro de consultas lentas.
- **Prueba de carga** (k6) con metas como «p95 menor a 500 ms con 50 pedidos por segundo». La aplicación ya no guarda estado propio, así que escala agregando servidores.
- **Réplica de lectura** de la base solo si la base se vuelve el cuello de botella.

## Fase F · Producto y diseño

El plan de `08-plan-de-rediseno.md` (pendiente: elegir la dirección de la Fase 0) avanza en paralelo, sobre los componentes que deja la Fase C.

## Orden recomendado

```
Semanas 1–2   A · Cimientos           (copias, integración continua, staging, monitoreo, correo, dominio)
Semanas 3–5   B · Plataforma          (colas, programador, Redis)
Semanas 4–10  C · Arquitectura        (módulo por módulo, sin frenar el producto)
Semanas 6–9   D · Seguridad avanzada
Continuo      F · Diseño
Según cifras  E · Escala
```

Cada fase se publica por partes, pasando primero por staging, con las 319 pruebas actuales en verde, y documentada en `docs/`.

## Decisiones que necesito de ti

1. **Presupuesto mensual de infraestructura**: define entre Laravel Cloud y un servidor propio, y si Supabase pasa a plan pago.
2. **Quién mantendrá el código**: tú con ayuda de IA, o una persona contratada. Cambia cuánto conviene automatizar.
3. **Dominio propio**: el nombre, para el correo y Cloudflare.
4. **Expansión**: si piensas salir de Malacatos en el próximo año, la geografía jerárquica entra antes.
