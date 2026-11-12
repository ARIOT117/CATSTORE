# Revisión de estructura y dirección visual

## Diagnóstico previo

La versión encontrada incluía cambios posteriores al primer rediseño: gatos repetidos entre páginas y cierres, tres accesos a WhatsApp, medallones negros con gradientes, sombras y varios adornos animados simultáneos. Estos elementos hacían competir la decoración con el contenido. La evaluación se hizo antes de modificar el código.

## Cambios aplicados

1. Estructura más directa: se eliminó la franja decorativa de cierre y el WhatsApp flotante, conservando un solo contacto en el pie.
2. Personajes sin repeticiones globales: 14 ubicaciones registradas en `data/personajes.php`. Se eliminaron los gatos adicionales de la introducción y barra lateral del menú. El resumen muestra el número real de bebidas y categorías.
3. Pestañas de categorías: líneas de color finas, encabezados limpios y personajes pequeños con fondo claro. El estado activo de navegación usa un tono neutro con buen contraste.
4. Bienvenida: se quitaron el cuadro flotante extra y la frase superpuesta. Se conservaron una flor pequeña, una postal sencilla y el movimiento con pausa pedido anteriormente.
5. Fondos planos, sin gradientes ni sombras decorativas. Se recuperaron salvia y crema en la visita y la cafetería. El gato del carrito conserva negro detrás para disimular el recorte, según la petición previa.
6. Cuenta centrada y formularios legibles. Se conservó el flujo nuevo de fotos privadas sin alterar su lógica.
7. Código ordenado y documentado en `docs/guia-del-codigo.md`.

La referencia de Apple, ChatGPT y Claude se interpretó como claridad, consistencia y reducción de ruido visual. Se conserva la identidad editorial de una cafetería; no se copiaron sus interfaces.

## Verificación

- Sintaxis PHP/JS, recursos y unicidad del registro de personajes: pasa.
- Pruebas HTTP: registro, login, validaciones, sesión, carrito, pedidos, aislamiento, CSV y subida/eliminación de fotos: pasan en `snacks_cats_test_redesign_20261111`.
- Cambios de precios, textos y disponibilidad en el menú: pasan; los valores de prueba se restauraron.
- Interacciones en Chromium: anclas sin recarga, teclado, filtros, carrito AJAX, pausa, persistencia de la preferencia y movimiento reducido: pasan.
- Revisión de ocho páginas a 320, 390, 768, 1024 y 1440 px: 40 vistas sin desbordamientos ni imágenes rotas. El recorrido comprobó que los 14 personajes aparecen una sola vez entre las páginas y que existe un único enlace de contacto en cada pie.
- Auditoría axe en siete páginas a dos tamaños: sin violaciones automáticas detectadas. No equivale a una certificación de accesibilidad.
- Se conservan las compras de demostración y los datos existentes. No se publicaron cambios en `main`.

La revisión automática de permisos bloqueó inicialmente una prueba por límite de uso. Una vez transcurrido el horario indicado, permitió continuar y se ejecutaron las comprobaciones en navegador. El bloqueo no se eludió.

Se guardó una copia de los archivos editables encontrados antes de esta simplificación en `/tmp/snacks-antes-simplificacion-premium.zip`. Las capturas y resultados actuales se guardan en `docs/capturas-premium/`.

## Movimiento editorial posterior

Por petición de Diego se retiró el botón de pausa y su lógica de preferencia. Las dos tarjetas de experiencia ahora flotan 4 px de manera desfasada; la postal se balancea 0.6 grados y sube 5 px. Se conservan el espacio de la cuadrícula, la reducción de movimiento del sistema y la pausa cuando la pestaña está oculta.
