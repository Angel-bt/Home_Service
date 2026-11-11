# 08 · Plan de rediseño UX/UI

Escrito el 11/11/2026 después de recorrer la portada, la búsqueda, la ficha de servicio y los tres paneles en escritorio y celular. Cada punto parte de algo que se vio en pantalla, no de una preferencia.

## Diagnóstico

### Lo que hoy funciona y se conserva
- Una sola acción clara por pantalla, mensajes que explican qué hacer y listas vacías con un siguiente paso.
- Los paneles comparten estructura (menú del rol, título, una línea de ayuda, avisos).
- La ficha de servicio pone precio, WhatsApp y solicitud juntos, arriba a la derecha.

### Lo que no convence, y por qué
1. **El sitio está pensado para cuando ya haya muchos proveedores, y hoy hay muy pocos.** La portada muestra catorce oficios donde casi todos dicen «Próximamente», y cifras como «2 proveedores» o «1 reseña». En producción hay 0 servicios: quien entra a buscar un gasfitero se va sin nada. Es el problema de UX más caro.
2. **La portada es larga y repetitiva.** Ocupa casi nueve pantallas. El encabezado amontona cinco cosas: titular, texto, buscador, franja de servicios y zonas. «Trabajos recomendados» muestra cada servicio a pantalla completa, con fotos de celular que no siempre aguantan ese tamaño.
3. **Lectura difícil para el público real.** Hay etiquetas en mayúsculas de 11–12 px con mucho espaciado y textos gris claro sobre fondo crema. Para gente mayor o con el celular al sol, cuesta.
4. **Se contrata a personas, pero el sitio muestra servicios.** No existe una página pública del proveedor con su foto, sus sellos, sus zonas, su experiencia, sus reseñas y todos sus servicios. La confianza se reparte entre fichas sueltas.
5. **En el celular, el botón importante no está a mano.** En la ficha, WhatsApp queda arriba y se pierde al bajar a leer la descripción y las reseñas.
6. **Dos identidades.** La portada es oscura y cinematográfica («Valle de noche»), y las páginas internas son claras y sobrias. El salto se nota. El logo es solo texto.
7. **Paneles en el celular:** el menú es una fila de botones que se desliza de lado. En el admin, con trece secciones, se pierde.

## Principios del rediseño
- **Primero el celular**, con la mano en una sola mano: acciones principales abajo y al alcance del pulgar, botones de 48 px como mínimo.
- **Legible antes que elegante:** texto base de 17 px, nada por debajo de 14 px, contraste AA en todo.
- **Confianza visible:** persona, foto real, zona, sellos, reseñas y tiempo de respuesta, siempre en el mismo lugar.
- **Honesto con lo que hay:** nunca mostrar ceros ni «Próximamente» en serie. Si falta un oficio, ofrecer una salida.
- **Una sola identidad**, de la portada a los paneles.

## Fases

### Fase 0 · Dirección visual (decisión tuya, antes de programar)
Tres propuestas en maqueta (portada, ficha de servicio y panel del proveedor, en celular y escritorio), para elegir una:

| Dirección | Idea | Para quién funciona |
|---|---|---|
| **A · Valle cálido** | Fondo claro, verdes del valle, tierra y amarillo de sol; fotos luminosas; tipografía redonda y grande. | Lo más cercano y local; confianza de pueblo. |
| **B · Servicio profesional** | Blanco, un color fuerte (verde o azul) y uno de acento; tarjetas limpias tipo aplicación de reservas. | Lo más «empresa seria»; se entiende al instante. |
| **C · Valle de noche, refinado** | La línea actual, pero clara en las páginas internas, con más contraste y una portada más corta. | Si te gusta lo de ahora y solo molestan los detalles. |

Incluye un logo simple (marca y símbolo) para la dirección elegida.

### Fase 1 · Sistema de diseño
- Colores con contraste comprobado, escala tipográfica, espacios, radios y sombras como variables (`resources/css/app.css` y `tailwind.config.js`).
- Componentes únicos, reutilizados en todo el sitio: botón, campo, tarjeta de servicio, tarjeta de proveedor, sello, estrellas, avisos, estado vacío, barra inferior del celular y esqueletos de carga.
- Guía para las fotos: proporción fija, recorte automático y una imagen por oficio para cuando falta foto.
- Una página interna de la guía de estilo, solo para la administración, para revisar todos los componentes juntos.

### Fase 2 · Lo público
1. **Portada de 5 bloques:** buscador con oficios frecuentes en botones; oficios disponibles; cómo funciona y por qué es seguro; proveedores destacados; invitación a proveedores.
2. **Modo arranque:** los oficios sin proveedores no dicen «Próximamente», sino «¿Lo necesitas? Escríbenos y te conseguimos a alguien», que va al WhatsApp de Malakplace y queda registrado en Actividad. Las cifras se muestran solo desde un mínimo. Así el sitio sirve aunque haya pocos proveedores.
3. **Página del proveedor** (`/proveedor/{nombre}`): foto, sellos, zonas, experiencia, reseñas y servicios. La ficha de servicio enlaza a ella.
4. **Ficha de servicio:** galería de fotos, barra fija abajo en el celular con WhatsApp y Solicitar, y reseñas arriba.
5. **Búsqueda:** filtros como botones (zona, oficio, con sello, mejor calificados), tarjeta con persona, zona, calificación y sello, y orden por relevancia.

### Fase 3 · Paneles
- Barra inferior en el celular con las 4 secciones principales de cada rol y «Más» para el resto.
- Inicio de cada panel con una sola cosa importante arriba, como «Responde a Carlos».
- Publicar un servicio por pasos (qué haces → precio → foto → listo), con vista previa de cómo lo verá el cliente.
- Avisos que aparecen y se van solos, y confirmaciones propias en lugar de las del navegador.
- Admin en escritorio: tablas más compactas y acciones en lote (ocultar o destacar varios servicios).

### Fase 4 · Calidad y medición
- Accesibilidad: contraste, foco visible, lectores de pantalla y tamaños táctiles.
- Velocidad en un celular con 3G: la portada visible en menos de 2,5 s (imágenes livianas, fuentes recortadas y animaciones que cargan después).
- Prueba con 5 personas reales de Malacatos (2 clientes, 2 proveedores y tú como admin), con tareas concretas: «encuentra un electricista en Vilcabamba y escríbele», «publica tu servicio». Se corrige lo que trabe a 2 o más.
- Medir antes y después con el panel de Actividad: búsquedas sin resultado, toques en WhatsApp por visita y registros que terminan de publicar.

## Orden y alcance
1. Fase 0 → elegir la dirección (sin código).
2. Fase 1 y Fase 2 (puntos 1–3) → es lo que más cambia la primera impresión y lo que hace útil el sitio con pocos proveedores.
3. Fase 2 (4–5) y Fase 3.
4. Fase 4, y luego ajustes.

Cada fase se publica sola, con sus pruebas: el recorrido de enlaces, los permisos y los flujos ya existentes deben seguir en verde.
