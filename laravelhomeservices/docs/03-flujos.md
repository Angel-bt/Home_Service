# 03 · Flujos de uso

Cómo funciona cada proceso de punta a punta, con las clases que intervienen.

## Registro

1. El usuario se registra en `/register` (o en el modal de la portada) y elige **Cliente** o **Proveedor de servicios**.
2. `App\Actions\Fortify\CreateNewUser` valida los datos. El tipo de cuenta solo puede ser `CST` o `SVP`; si falta, se crea como cliente.
3. Si es proveedor, se crea también su fila en `service_providers`.
4. Se encola el correo de bienvenida y el usuario entra a su panel.

Los administradores no se registran desde la web: se cambia `users.utype` a `ADM` en la base de datos.

## Proveedor: perfil y publicación

1. **Perfil** (`/sprovider/profile/edit`, `EditSproviderProfileComponent`): WhatsApp (obligatorio y validado), zonas donde trabaja (al menos una), foto, presentación, ciudad y categoría principal. Mientras falten el WhatsApp o las zonas, el panel muestra un aviso: sin zonas, sus servicios no aparecen al filtrar por zona.
2. **Publicar** (`/sprovider/service/add`, `SproviderAddServiceComponent`): nombre, enlace (slug, único), frase corta, categoría, precio "desde", descuento opcional, descripción, qué incluye y qué no (una línea por punto), miniatura e imagen principal (hasta 4 MB).
3. **Límite del plan:** antes de guardar, `ServicePublicationGate` comprueba `User::canPublishService()`. Gratis = 3 servicios (`FREE_SERVICE_LIMIT`); Pro y admin, sin límite.
4. **Editar y borrar:** solo sus propios servicios. Si intenta abrir uno ajeno, recibe 404; si intenta borrarlo, no pasa nada.

El administrador publica con el mismo formulario y además puede marcar servicios como destacados.

## Búsqueda y zonas

- **Búsqueda** (`/buscar`, `SearchComponent`): busca el texto en nombre, frase corta, descripción y nombre de la categoría (`Service::scopeSearch`). Los resultados se actualizan mientras se escribe.
- **Filtro por zona** (búsqueda y páginas de categoría): `Service::scopeInZone()` muestra
  - los servicios de proveedores que cubren esa zona, y
  - los servicios publicados por la administración, que cuentan para todas las zonas.
- La zona elegida se guarda en la sesión (`Concerns\FiltersByZone`). Así se mantiene al cambiar de página y se rellena sola en el formulario de solicitud.
- Las zonas disponibles están en `config/marketplace.php` (`zones`). Para agregar una, ver [07-desarrollo-y-tests.md](07-desarrollo-y-tests.md).

## Contacto por WhatsApp

En el detalle del servicio, el botón **"Escribir por WhatsApp"** abre `wa.me` con un mensaje ya escrito. El número es:

1. el WhatsApp del perfil del proveedor;
2. si no lo tiene, su teléfono de registro;
3. si el servicio es de la administración, `MARKETPLACE_CONTACT_PHONE`.

`App\Support\WhatsApp` convierte `0991234567`, `+593 99…` o `00593…` a `593991234567`.

## Solicitud de servicio

Requiere iniciar sesión (así la solicitud queda ligada a una cuenta y luego se puede reseñar).

1. En el detalle del servicio el cliente indica nombre, celular, zona, dirección o referencia, fecha preferida y mensaje (`ServiceDetailsComponent::submitRequest`).
2. No se puede solicitar un servicio propio. Hay un límite de 5 solicitudes cada 10 minutos por usuario.
3. Se crea un `ServiceRequest` en estado **pendiente**, asignado al dueño del servicio.

### Estados

```
pendiente ──► aceptada ──► completada ──► (el cliente puede reseñar)
    │             │
    ├─► rechazada │
    └─► cancelada ◄┘
```

| Cambio | Quién puede |
|---|---|
| pendiente → aceptada / rechazada | El proveedor dueño o un admin |
| aceptada → completada | El proveedor dueño o un admin |
| pendiente o aceptada → cancelada | Solo el cliente |

Las reglas viven **solo** en `ServiceRequest::transitionTo()` / `canTransitionTo()`. Cualquier pantalla nueva que cambie estados debe pasar por ahí.

El proveedor ve sus solicitudes en `/sprovider/requests` con un enlace de WhatsApp al cliente; el admin ve todas en `/admin/service-requests`.

## Reseñas

1. Cuando el proveedor marca la solicitud como **completada**, el cliente ve el botón **Calificar** en `/customer/dashboard`.
2. Elige de 1 a 5 estrellas y un comentario opcional.
3. `ServiceRequest::canBeReviewedBy()` exige que la solicitud esté completada, que sea del cliente y que no tenga reseña. Hay una sola reseña por solicitud (índice único en la base).
4. La reseña aparece en el detalle del servicio y cuenta para el promedio de estrellas.

## Contacto con la plataforma

El formulario de `/contact-us` guarda el mensaje en `contacts` (visible en `/admin/contacts`) y, si `LEAD_NOTIFICATION_ADDRESS` está configurado, envía un correo. Límite: 5 mensajes cada 10 minutos por IP.

## Plan Pro

El flujo de pago del plan Pro se explica en [04-pagos.md](04-pagos.md).
