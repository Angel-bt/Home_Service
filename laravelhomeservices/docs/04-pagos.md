# 04 · Pagos del plan Pro

## Modelo de negocio

- **Los clientes no pagan a la plataforma.** Le pagan directamente al proveedor, en efectivo o por transferencia, lo que acuerden. La app no toca ese dinero.
- **Los proveedores** pueden publicar 3 servicios gratis. Para publicar sin límite pagan el **plan Pro**: por defecto **$8 cada 30 días** (configurable).

*Por qué así:* intermediar el dinero de cada trabajo implica facturación electrónica, devoluciones y obligaciones legales. Una suscripción simple es viable desde el primer día.

## Métodos aceptados (por ahora)

| Método | Cómo funciona |
|---|---|
| **Transferencia bancaria** | El proveedor transfiere, sube el comprobante y espera la aprobación. |
| **Efectivo** | El proveedor avisa que pagará en efectivo y entrega el dinero en persona; o el admin registra directamente un pago recibido. |

*Por qué solo estos dos:* es lo que usa la gente de la zona y no necesita contratos con pasarelas ni RUC con comercio electrónico. DeUna se quitó de la interfaz (los pagos antiguos con DeUna conservan su etiqueta en el historial). El pago con tarjeta por **Payphone** está programado pero **desactivado** (ver el final).

## Flujo: transferencia

1. El proveedor entra a **Mi plan** (`/sprovider/plan`, `SproviderPlanComponent`).
2. Elige el periodo: 30, 90, 180 o 360 días. El total se calcula al momento.
3. Elige **Transferencia bancaria** y ve los datos de la cuenta (`BILLING_BANK_*`). Si no están configurados, se le pide escribir por WhatsApp.
4. Transfiere, escribe el número de comprobante y sube la foto o el PDF (máximo 5 MB).
5. Se crea un `SubscriptionPayment` con estado **en revisión** (`pending`).
   - El archivo se guarda en el disco privado `receipts` (`storage/app/receipts`), **fuera de `public/`**. Solo un admin puede verlo, desde `/admin/payments/{id}/receipt`.
6. El admin revisa en `/admin/payments` y pulsa **Aprobar** (si el dinero llegó) o **Rechazar** con un motivo, que el proveedor ve en su historial.

## Flujo: efectivo

**Opción A: el proveedor avisa desde la app**

1. En **Mi plan** elige **Efectivo**. Ve un enlace de WhatsApp a la administración para coordinar la entrega.
2. Opcionalmente indica cuándo y dónde pagará, y pulsa **Avisar pago en efectivo**. No se sube comprobante.
3. Queda un pago **en revisión**. Cuando la administración recibe el dinero, lo aprueba en `/admin/payments`.

**Opción B: el admin registra un pago recibido en persona**

1. En `/admin/payments`, sección **Registrar pago en efectivo recibido**, elige el proveedor, el periodo y una nota opcional.
2. Al confirmar, el pago se crea y se aprueba en el mismo paso (`AdminPaymentsComponent::recordCashPayment`). Solo se puede asignar a usuarios proveedores.

## Qué pasa al aprobar

Todo cambio de plan pasa por `App\Services\ProPlanBilling::approve()`:

- Si el proveedor no es Pro (o ya venció), el plan dura desde hoy `30 días × periodos`.
- Si renueva antes de vencer, **los días se suman a su fecha de vencimiento actual**; no pierde días.
- Aprobar dos veces el mismo pago no suma días de más.
- Guarda quién aprobó y cuándo.

Un proveedor solo puede tener **un pago en revisión a la vez** y enviar como máximo 5 avisos o comprobantes por hora.

## Vencimiento

- `User::isPro()` revisa la fecha de vencimiento en cada consulta: en cuanto vence, el proveedor vuelve al límite de 3 servicios.
- El comando `billing:expire-plans` (cada hora) actualiza la base: pasa `subscription_plan` a `free` y cierra los intentos de pago con tarjeta abandonados.
- Los servicios publicados durante el Pro **siguen en línea** al vencer; el proveedor solo no puede publicar más hasta renovar.
- Un usuario marcado `pro` sin fecha de vencimiento (cambio manual en la base) es Pro para siempre.

## Configuración

En `.env` (definidas en `config/billing.php`):

| Variable | Valor por defecto | Para qué |
|---|---|---|
| `BILLING_PRO_PRICE` | `8.00` | Precio en USD por periodo. |
| `BILLING_PRO_PERIOD_DAYS` | `30` | Días de cada periodo. |
| `BILLING_TAX_RATE` | `0` | IVA incluido en el precio (`0.15` = 15 %). Confírmalo con tu contador. |
| `BILLING_BANK_NAME`, `BILLING_BANK_ACCOUNT_TYPE`, `BILLING_BANK_ACCOUNT_NUMBER`, `BILLING_BANK_HOLDER`, `BILLING_BANK_HOLDER_ID` | vacío | Datos de la cuenta que ve el proveedor al elegir transferencia. |
| `MARKETPLACE_CONTACT_PHONE` | — | WhatsApp de la administración para coordinar pagos en efectivo. |

La facturación electrónica al SRI de cada pago **no la genera la app**; se emite por fuera.

## Payphone (tarjeta) — desactivado

Existe una integración con el botón de pagos de Payphone (`Services\PayphoneGateway`, `BillingController`). Con `PAYPHONE_ENABLED=false` (por defecto) no se muestra en ninguna pantalla y su ruta responde 404.

Si en el futuro se activa:

1. Crear la cuenta de comercio en Payphone (pide RUC), obtener el token y el Store ID, y **registrar el dominio de producción** (Payphone bloquea otros dominios).
2. Configurar `PAYPHONE_ENABLED=true`, `PAYPHONE_TOKEN` y `PAYPHONE_STORE_ID`.
3. Flujo: el proveedor pulsa "Pagar con tarjeta" → se prepara el cobro (`api/button/Prepare`) → paga en Payphone → vuelve a `/billing/payphone/return` → la app confirma el pago con Payphone (`api/button/V2/Confirm`) y lo aprueba solo si el estado es "aprobado" y coinciden la transacción y el monto. Payphone revierte el cobro si no se confirma en 5 minutos.
4. Hacer una prueba con un pago real pequeño antes de anunciarlo.

Documentación oficial: https://docs.payphone.app/boton-de-pago-por-redireccion
