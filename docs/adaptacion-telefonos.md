# Adaptación para teléfonos

## Cambios

- Cabecera móvil con cuadrícula estable: logo a la izquierda y botones independientes de cuenta, carrito y menú. El lema pequeño se conserva en escritorio y se oculta en la cabecera del teléfono para dar espacio a los controles.
- Navegación desplegable con altura limitada y desplazamiento propio en pantallas horizontales. Se cierra al elegir una página, pulsar Escape o tocar fuera.
- Portada con tipografía ajustada al ancho, acciones grandes en columna y arco del gato proporcionado. Se mantiene el eslogan curvo.
- Categorías táctiles y barra persistente. Las filas de bebidas usan dos columnas claras, nombre/descripción y precio, sin puntos decorativos que estrechen el contenido.
- Catálogo en una columna hasta 600 px, imágenes menos altas, títulos legibles y botón Agregar de 48 px.
- Formularios con campos de 16 px y botones de al menos 50 px. Al recibir un error en teléfono, el mensaje se sitúa en pantalla. El tamaño de los campos evita la causa habitual del zoom de enfoque en iOS; no se deshabilita el zoom del usuario.
- Carrito y resumen en una columna, controles de cantidad accesibles y notificaciones separadas del borde inferior seguro.
- Pie simplificado, enlaces de navegación en dos columnas y WhatsApp fácil de tocar.
- Compatibilidad con las áreas seguras del teléfono mediante `viewport-fit=cover` y variables `safe-area-inset`. Se conserva la reducción de movimiento.

Las reglas se mantienen dentro de los bloques responsive de `CSS/style.css`. La distribución de escritorio no se reemplazó. No cambiaron la base de datos, los precios ni la lógica de cuenta, pedidos o gestión de archivos.

## Verificación

Chromium con emulación móvil/táctil, no dispositivos físicos:

- 320 × 667, 360 × 800, 390 × 844 y 430 × 932.
- Horizontal: 667 × 375 y 844 × 390.
- Referencia de escritorio: 1440 × 960.
- Siete páginas en cada tamaño: 49 vistas sin desbordamiento horizontal, imágenes rotas, errores JS ni superposición entre marca y accesos de cuenta.
- Campos visibles de al menos 16 px en los tamaños de teléfono.
- Apertura y cierre táctil del menú, cierre al tocar fuera y navegación por categorías sin recargar.
- Verificación de sintaxis y recursos: pasa.

Capturas: `docs/capturas-telefonos/`. Resultados del recorrido: `docs/pruebas-telefonos.json`.
