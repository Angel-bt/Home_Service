# Malakplace · Servicios para el hogar en Malacatos

**Malakplace** (Malacatos + *marketplace*; un proyecto de Proyetech) es el marketplace que conecta a clientes de **Malacatos** (zona principal) y sus parroquias vecinas **Vilcabamba, Yangana y Quinara** con proveedores locales de servicios para el hogar y las quintas: gasfitería, electricidad, jardinería, piscinas, limpieza y más.

El proyecto nació del tutorial **"Laravel 8 Project Home Services"** de SurfsideMedia (enlaces en [`pasos .txt`](pasos%20.txt)). Sobre esa base se corrigieron errores, se convirtió el catálogo en un marketplace real, se añadió el cobro del plan Pro a proveedores y se actualizó todo a Laravel 13. La historia completa y el porqué de cada decisión está en [docs/01-historia-y-decisiones.md](docs/01-historia-y-decisiones.md).

## Qué hace

| Rol | Qué puede hacer |
|---|---|
| **Visitante** | Ver categorías y servicios, buscar, filtrar por zona, escribir por WhatsApp a un proveedor. |
| **Cliente** (`CST`) | Todo lo anterior, enviar solicitudes de servicio, cancelarlas y dejar reseñas de trabajos completados. |
| **Proveedor** (`SVP`) | Publicar y editar sus servicios (3 gratis, ilimitados con el plan Pro), indicar su WhatsApp y zonas, atender solicitudes y pagar el plan Pro por transferencia o en efectivo. |
| **Administrador** (`ADM`) | Gestionar categorías, servicios, slider y proveedores; ver todas las solicitudes; aprobar o rechazar pagos del plan Pro y registrar pagos en efectivo. |

## Tecnología

- **PHP 8.3+**, **Laravel 13**, **Livewire 3** (páginas completas como componentes), **Jetstream 5 / Fortify** (login, registro, 2FA, perfil), **Sanctum 4**.
- **MySQL** para los datos y **Redis** para caché, sesiones, colas y el contador de visitas.
- Interfaz: diseño propio «Valle de noche» con Tailwind compilado con **Laravel Mix** (no Vite), Alpine y GSAP en la portada.
- Idioma: español (`lang/es`, `lang/es.json`).

## Puesta en marcha local

Requisitos: PHP 8.3+ (por ejemplo con [Laravel Herd](https://herd.laravel.com)), Composer, Node 18+, MySQL y Redis.

```bash
composer install
npm install
npm run dev
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ServiceCategorySeeder
php artisan db:seed --class=DemoMarketplaceSeeder   # datos de demo, solo local
php artisan serve
```

El `.env` local usa la base de datos `home` (usuario y contraseña `root`) y Redis en `127.0.0.1:6379`.

**Cuentas de demo** (creadas por `DemoMarketplaceSeeder`, la contraseña está en ese archivo): `admin@proyetech.test`, `proveedor@proyetech.test`, `proveedora@proyetech.test` y `cliente@proyetech.test`.

Para crear un administrador real, registra la cuenta y cambia `users.utype` a `ADM` en la base de datos.

## Comandos útiles

| Comando | Para qué |
|---|---|
| `php artisan test` | Ejecuta los tests (SQLite en memoria; no necesita MySQL ni Redis). |
| `npm run dev` / `npm run prod` | Compila CSS (Tailwind) y JS. Vuelve a ejecutarlo si agregas clases de Tailwind a las vistas. |
| `php artisan schedule:work` | Ejecuta las tareas programadas en local (visitas y vencimiento de planes). |
| `php artisan queue:work redis --queue=mail,default` | Procesa los correos en cola. |
| `php artisan cache:clear` | Limpia la caché de la portada tras editar datos directamente en la base. |

## Documentación

| Documento | Contenido |
|---|---|
| [01 · Historia y decisiones](docs/01-historia-y-decisiones.md) | Auditoría inicial, qué se hizo en cada fase y por qué. |
| [02 · Arquitectura](docs/02-arquitectura.md) | Carpetas, rutas, roles, modelos y tablas, servicios y caché. |
| [03 · Flujos de uso](docs/03-flujos.md) | Registro, publicación de servicios, solicitudes, reseñas, búsqueda y zonas. |
| [04 · Pagos del plan Pro](docs/04-pagos.md) | Transferencia y efectivo, aprobación, vencimiento y configuración. |
| [05 · Seguridad](docs/05-seguridad.md) | Medidas aplicadas y por qué. |
| [06 · Despliegue y operación](docs/06-despliegue-y-operacion.md) | Servidor, variables de entorno, cron, colas y caché. |
| [07 · Desarrollo y tests](docs/07-desarrollo-y-tests.md) | Convenciones, cómo extender el proyecto y qué cubren los tests. |

Para asistentes de código hay un resumen técnico en inglés en [`AGENTS.md`](../AGENTS.md).

## Créditos

Basado en el tutorial *Laravel 8 Project Home Services* de SurfsideMedia (demo original: https://home-services.jitinrathore.com/). Laravel es software libre bajo licencia MIT.
