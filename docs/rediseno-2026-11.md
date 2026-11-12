# Rediseño editorial de Snacks and Cats

Implementación del plan aprobado el 11 de noviembre de 2026. El respaldo previo está en la rama `respaldo/antes-rediseno-2026-11-11`, commit `7a5e6b39d7c4d5424335f333dfdb3952f8929162`, de https://github.com/ARIOT117/CATSTORE. Esta entrega modifica el proyecto local; no se publicó en producción ni se sustituyó `main`.

## Fase 1 — Fundamentos y recursos

- Se reemplazó la acumulación de reglas CSS y overrides por una hoja organizada en fundamentos, navegación, páginas, estados, interacciones y adaptaciones.
- Paleta: crema, terracota, salvia y café. Rosa empolvado limitado a acentos del personaje de bienvenida. Se mantienen las fuentes del sistema Georgia y Trebuchet/Arial, sin petición externa de fuentes.
- Contenido de lectura de 14–16 px, formularios de 16 px, acciones principales de al menos 44–48 px, foco visible y contraste revisado.
- Las tres fotografías Pexels se descargaron y optimizaron a WebP local (aproximadamente 110 KB en conjunto). Se conserva la procedencia en `fuentes.php` y `assets/img/bebidas/README.md`.
- Se revisaron los 23 PNG de los dos ZIP. Solo se integraron los personajes útiles. Moño y pensativo requirieron limpieza de fondo con herramienta de imágenes; no se aplicó una eliminación automática de blancos que pudiera borrar el pelaje. Se mantienen alfa y proporciones. Procedencia en `assets/img/personajes/README.md`.

## Fase 2 — Inicio

- Se conserva el titular «Un cafecito. Un antojo. Muchos michis.».
- Descripción explícita de bebidas, antojos y experiencia felina; acciones principales Ver el menú y Cómo llegar.
- El gato con moño rosa es el personaje de bienvenida. Arco terracota suave, una flor editorial y una postal, sin giros permanentes ni levitación de tarjetas.
- Se conserva la composición abierta de visita y el gato con café y croissant, conforme a la preferencia expresada por Diego. No se vuelve a encerrar la sección en una tarjeta.
- Se ajustó la invitación a la tiendita para evitar una columna de texto estrecha en móvil.

## Fase 3 — Menú y cafetería

- Menú continuo, con las seis categorías y los precios existentes desde MySQL. Las categorías desplazan la vista sin recarga; selección activa al recorrer, navegación horizontal en móvil y foco de sección al activar con teclado.
- Las alturas de barras se miden con ResizeObserver, no se escriben estilos en cada evento de scroll.
- Cada categoría tiene una pestaña superior de color y un sello de gato distinto; ningún archivo de gato se repite en la página. El gato con café acompaña la introducción, otro personaje ocupa el recordatorio lateral y el pie usa un tercero. Los títulos, descripciones y precios siguen alineados y legibles.
- Cafetería: narrativa breve, recomendaciones generales de convivencia y FAQ nativa con details/summary. El gato pensativo acompaña las preguntas.
- Se unificaron los enlaces con la ficha Maps suministrada por Diego: https://maps.app.goo.gl/ud9W22f4zkyRXqPMA. Se verificó la carga real del mapa en Chromium.
- No se publicaron horarios, redes, tarifas de acceso, testimonios, adopciones ni condiciones no confirmadas. Las recomendaciones de convivencia no se presentan como un reglamento oficial.

## Fase 4 — Tiendita, cuenta y carrito

- Catálogo y precios de demostración intactos. Categorías de filtro derivadas de los registros, búsqueda compatible con acentos y filtro Solo disponibles combinado con categoría/búsqueda.
- Botones Agregar con texto, estado agotado y estado ocupado durante una petición. Imágenes fallidas muestran respaldo sin bloquear acciones.
- Login y registro: controles consistentes, mensajes vinculados al formulario y foco accesible del error. El gato ejecutivo acompaña el login.
- Gato sorprendido en carrito vacío. Se conservan cantidades, validación, resumen, confirmación, pedidos y descarga CSV.
- El pie aparece en todas las páginas con un personaje distinto por pantalla, texto propio y llamada a WhatsApp. Ningún gato se repite dentro de la misma página.
- Sin JavaScript, la navegación móvil queda desplegada y el catálogo sigue disponible; los filtros que requieren JavaScript se ocultan. Formularios y enlaces internos continúan funcionando.

## Fase 5 — Verificaciones

Entorno: PHP 8.2.12, Chromium local; base exclusiva `snacks_cats_test_redesign_20261111`, servidor http://127.0.0.1:8876. No se cambiaron registros de `caotitos`. Se crearon cuentas y pedidos ficticios solo en la base temporal.

| Comprobación | Resultado |
|---|---|
| `python3 scripts/verificar.py` | Pasa: sintaxis PHP/JS, datos semilla, recursos y protección de carpetas |
| `scripts/pruebas_integracion.py` | Pasa: CSRF, validaciones, registro/login, fusión y persistencia de carrito, precios de servidor, pedido atómico/idempotente, logout, aislamiento y CSV |
| `scripts/pruebas_integracion.py` (foto del michi) | Pasa: subida PNG válida, servido privado, rechazo de MIME falso, CSRF obligatorio y eliminación del archivo |
| `scripts/pruebas_menu.php` | Pasa: precios/nombres/descripciones, escape HTML, bajas, categorías, altas e instalación idempotente |
| Siete páginas × cinco anchos (320, 390, 768, 1024, 1440 px) | 35 casos sin desbordamientos, errores JS ni imágenes rotas cargadas |
| Revisión posterior a ajustes móviles | 21 comprobaciones adicionales de ancho, imágenes diferidas cargadas para capturas |
| `scripts/pruebas_ui.mjs` | Pasa: anclas sin recarga, selección activa, teclado, menú móvil, filtros, disponibilidad, carrito AJAX, ver contraseña, FAQ y contenido sin JS |
| Fallo forzado de todas las fotos de productos | Respaldos visibles y 11 acciones accesibles |
| Movimiento reducido | Sin animaciones permanentes; al activar la preferencia se hacen visibles las entradas pendientes |
| Compra desde Chromium | Cuenta ficticia, fusión de carrito, confirmación y pedido visible |
| axe WCAG 2 A/AA y 2.1 AA | 14 vistas públicas + 2 de pedidos sin violaciones automáticas detectadas |

La prueba antigua de leyenda de precio común asumía filtrado por categoría. Se corrigió para comprobar solo la sección Sin café dentro del menú completo. No se eliminó la verificación de actualización de precios.

La auditoría axe se inyectó con `bypassCSP` solo en el contexto de pruebas; la CSP del sitio no se debilitó. Las pruebas funcionales y de navegación se ejecutaron sin esa excepción. Axe dejó el contraste de algunos detalles de la portada para revisión manual (composición superpuesta); se inspeccionaron las capturas. Un resultado automático sin hallazgos no constituye una certificación de accesibilidad.

## Fase 6 — Ronda final: gatos, movimiento y archivos

- **Regla de no repetición.** Se interpretó la petición como «ningún archivo de gato se repite en la misma pantalla», no como «un solo gato por página». El pie mapea cada página a un personaje distinto; el menú usa seis sellos distintos para las categorías, más el anfitrión y el recordatorio. Se comprobó con el navegador que no hay `src` de gato duplicado en las páginas públicas, el carrito vacío y la cuenta.
- **Escenarios negros.** Todos los recortes con transparencia (arco de portada, visita, login, registro, sellos del menú, preguntas, estados vacíos y retrato del pie) llevan fondo carbón, borde redondeado y una sombra interior suave. Así se disimulan los bordes del recorte y el personaje se separa del fondo crema.
- **Movimiento con pausa.** El punto del logotipo late (2.8 s) y el cuadrito de la portada se balancea (7 s); se suman la cinta, la flor y la postal existentes. El botón **Pausar movimiento** detiene el conjunto y respeta `prefers-reduced-motion`.
- **WhatsApp.** Botón flotante y llamada a la acción en el pie con el número `614 120 5926` y mensaje editable. Los enlaces usan `https://wa.me/526141205926?text=...` y no envían nada por sí solos.
- **Pie por página.** Inicio, menú, cafetería, tiendita, login, registro, carrito, pedidos y fuentes tienen personaje, etiqueta y frase propios. El pie ya no repite el logotipo del gato de la cabecera.
- **Menú con color.** Las categorías conservan su pestaña superior de color y su sello de gato; el contador y los precios siguen saliendo de MySQL.
- **Foto privada del michi.** `mascota.php` valida sesión, CSRF, MIME real (`finfo`), tamaño (≤ 2 MB) y dimensiones (20–4000 px). Guarda en `data/mascotas` con nombre aleatorio, sirve solo al dueño, actualiza la marca de tiempo al reemplazar y borra el archivo anterior al eliminar. La carpeta está denegada por `.htaccess` y por el router de desarrollo.
- **Ajustes móviles.** Se corrigió el orden de la hoja de estilos para que la sección de la foto y el pie se apilen correctamente a 390 px; se verificaron las vistas de inicio, menú, cafetería, tiendita, login, registro, carrito y pedidos.

## Evidencia y límites

- `docs/capturas-rediseno/`: escritorio y móvil de las páginas, carrito con cuenta y pedidos ficticios. `mapa-detalle-1280.png` acredita el mapa cargado en su viewport; las capturas de página completa pueden mostrar su fondo porque Google difiere el render fuera de pantalla. No se compusieron ni retocaron capturas para simular carga.
- `docs/pruebas-responsive.json`, `docs/pruebas-accesibilidad.json`, `docs/pruebas-accesibilidad-pedidos.json`: resultados conservados.
- Lienzo con capturas reales: https://superdesign.dev/teams/2f4eb650-4d5c-4d9f-aac4-9ac52514a0eb/projects/cb4164bc-8cef-4744-9290-5f7e7ac662e8
- La grabación anunciada no estaba adjunta; se trabajó con el repositorio, las capturas compartidas y el sitio ejecutado en navegador.
- No hay fotografías verificadas del interior o de recetas exactas. Las fotos de bebidas están etiquetadas como ilustrativas; los personajes no representan gatos reales del establecimiento.
- Redes y otros datos oficiales siguen pendientes de verificación directa. No se añadieron datos de directorios contradictorios.
- Se probó Chromium, no dispositivos físicos ni Safari/Firefox. El mapa y los enlaces externos requieren conexión; las imágenes del rediseño se sirven localmente.
