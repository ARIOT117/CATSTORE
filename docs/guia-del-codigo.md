# Guía del código

Snacks and Cats conserva PHP, MySQL, HTML, CSS y JavaScript sin framework. Cada página tiene un archivo propio y comparte cabecera, pie y lógica de aplicación.

## Dónde está cada parte

| Archivo | Responsabilidad |
|---|---|
| `index.php` | Bienvenida, franja animada, bebidas destacadas e invitación a visitar. |
| `menu.php` | Menú desde MySQL, resumen, navegación por anclas y encabezados de categoría. |
| `nosotros.php` | Experiencia, convivencia, preguntas frecuentes y ubicación. |
| `productos.php` | Catálogo, búsqueda, disponibilidad y formularios para agregar productos. |
| `login.php` / `registro.php` | Formularios de cuenta y validación en el servidor. |
| `carrito.php` | Cantidades, eliminación, resumen y confirmación de compra. |
| `pedidos.php` | Historial privado y controles de foto de la cuenta. |
| `fuentes.php` | Procedencia de datos e imágenes. |
| `header.php` | Metadatos, navegación, carga de CSS/JS y accesos a cuenta y carrito. |
| `footer.php` | Ubicación, enlaces y único contacto WhatsApp; no agrega personajes repetidos. |
| `app.php` | Sesión, CSRF, utilidades, carrito, compra transaccional y helper `personaje()`. |
| `auth.php` | Configuración y conexión PDO, consultas de usuarios y contraseñas. |
| `acciones.php` | Operaciones POST de carrito, compra y cierre de sesión. |
| `comprobante.php` | Generación y descarga privada de CSV. |
| `mascota.php` | Gestión y entrega de fotos privadas de la cuenta. |
| `data/personajes.php` | Registro único de personajes editoriales. Asocia cada gato a una sola función. |
| `data/menu-repositorio.php` | Consulta, destacados y migración inicial del menú. |
| `data/schema.sql` / `data/schema-menu.sql` | Definición de tablas y relaciones. |
| `CSS/style.css` | Fundamentos, componentes, páginas, responsive y movimiento. |
| `JS/app.js` | Navegación, filtros, carrito AJAX, anclas, estados y preferencia de movimiento. |
| `JS/login.js` / `JS/script.js` | Referencias históricas; las plantillas actuales no los cargan. |

## Reglas del diseño

- Un personaje editorial se utiliza en una sola ubicación de todo el sitio. El logo compartido identifica la marca y no forma parte de este registro.
- Para cambiar un gato se edita `data/personajes.php`, sin copiar rutas entre plantillas.
- Los colores de categorías se definen con `--color-categoria` en `.categoria-cafe`, `.categoria-sin-cafe`, etc. Son acentos; el texto mantiene contraste independiente de ellos.
- WhatsApp se configura una sola vez en `footer.php`: número, mensaje y enlace. No se envían mensajes automáticamente.
- El movimiento de franja, flor y postal comparte pausa. Se respeta movimiento reducido y pestaña oculta.
- Los precios, existencias y pedidos siguen viniendo de MySQL. Ningún cambio visual sustituye sus validaciones.

## Instalación y pruebas

| Script | Uso |
|---|---|
| `scripts/instalar.php` | Instala tablas y semillas sin borrar cuentas existentes. |
| `scripts/integridad.php` | Comprueba relaciones y restricciones de datos. |
| `scripts/migrar-menu.php` | Instala el menú de manera independiente. |
| `scripts/actualizar-catalogo.php` | Actualiza datos desde la API y reconstruye el respaldo JSON. |
| `scripts/router.php` | Router del servidor local de pruebas; protege rutas privadas. |
| `scripts/verificar.py` | Sintaxis, recursos, semillas y registro de personajes sin duplicados. |
| `scripts/pruebas_integracion.py` | Registro, sesión, carrito, pedidos, CSV y gestión de fotos en una instalación de prueba. |
| `scripts/pruebas_menu.php` | Modifica datos de prueba, verifica la interfaz y restaura sus valores. |
| `scripts/pruebas_ui.mjs` | Recorrido e interacciones reales en Chromium. |

Las pruebas con escrituras deben ejecutarse contra una base temporal, nunca sobre cuentas reales. Las instrucciones para iniciarla están en `README.md`.

## Legibilidad

- Cuatro espacios para PHP y SQL; dos para CSS y JavaScript.
- Etiquetas HTML, atributos y bloques PHP separados en líneas legibles.
- Comentarios al principio de los archivos para explicar su propósito.
- `.editorconfig` y `.prettierrc.json` conservan las convenciones.
- Se retiraron reglas CSS de componentes eliminados; no se añadieron capas de overrides para ocultarlos.
- Los formateadores se instalaron únicamente en `/tmp`. No se añadieron dependencias de ejecución al sitio.
