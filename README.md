# Snacks and Cats

Propuesta académica para la cafetería Snacks and Cats, Chihuahua. PHP 8+, MySQL/MariaDB, HTML, CSS y JavaScript, sin framework ni compilación. La cafetería es la sección principal; la tiendita es secundaria. **Todas las compras son de demostración: no hay cobros, entregas ni descuentos de inventario comercial.**

## Abrir con XAMPP

1. Enciende Apache y MySQL en XAMPP.
2. La carpeta real de esta instalación es `/opt/lampp/htdocs/CATSTORE-BUENO-PARA-CLASE`. La ruta `/home/diego/Escritorio/htdocs/CATSTORE-BUENO-PARA-CLASE` apunta a ella.
3. Ejecuta una sola vez (puedes repetirlo sin borrar cuentas ni pedidos):

```bash
cd /home/diego/Escritorio/htdocs/CATSTORE-BUENO-PARA-CLASE
/opt/lampp/bin/php scripts/instalar.php
```

4. Abre **http://localhost/CATSTORE-BUENO-PARA-CLASE/**.

El instalador conserva la tabla `usuarios` existente y agrega tablas con prefijo `sc_`. Nunca importa ni reemplaza las cuentas de la base temporal usada para pruebas.

La conexión predeterminada usa `127.0.0.1:3306`, base `caotitos`, usuario local `root`, contraseña vacía. Si necesitas otros valores, copia `config.example.php` como `config.local.php` y edítalo. También se admiten `CATSTORE_DB_HOST`, `CATSTORE_DB_PORT`, `CATSTORE_DB_NAME`, `CATSTORE_DB_USER`, `CATSTORE_DB_PASSWORD` y `CATSTORE_DB_SOCKET`. En Linux puedes usar `/opt/lampp/var/mysql/mysql.sock` como socket. No publiques `config.local.php`.

## Qué hace PHP y cómo se guarda una compra

- `auth.php`: abre la conexión PDO con MySQL y consulta/crea usuarios. Guarda un **hash** de la contraseña mediante `password_hash`; al entrar la verifica con `password_verify`. Las cuentas anteriores son compatibles.
- `app.php`: inicia la sesión, genera la protección CSRF, proporciona las funciones de carrito y registra los pedidos. Las cookies son HttpOnly y SameSite=Lax; usan Secure cuando el sitio tiene HTTPS. La duración solicitada es 30 días; la configuración del servidor puede reducirla. El carrito y los pedidos de una cuenta permanecen en MySQL aunque expire la sesión.
- `registro.php` y `login.php`: validan los formularios desde el servidor. Después de autenticar, renuevan el identificador de sesión y reúnen el carrito de invitado con el de la cuenta. Se limita la cantidad de intentos de acceso por correo/IP.
- `productos.php`: lee el catálogo de MySQL. Si el servidor no responde, puede mostrar la copia local, pero no simula haber guardado cambios en la base.
- `acciones.php`: recibe únicamente POST con CSRF válido. Agrega, modifica o quita productos y confirma la compra. JavaScript mejora la experiencia del botón Agregar, pero los formularios también funcionan sin JavaScript.
- `carrito.php`: presenta las cantidades y el total. Antes de entrar, el carrito se conserva en la sesión del visitante. Después de entrar se guarda en `sc_carrito`, por lo que aparece en otros navegadores al iniciar sesión.
- `pedidos.php`: consulta los pedidos filtrando siempre por el ID de usuario de la sesión. No toma el usuario de un parámetro enviado por el navegador. También muestra la foto privada del michi y su formulario de carga.
- `mascota.php`: sube, sirve y elimina la foto del michi. Solo acepta sesiones iniciadas y POST con CSRF; comprueba el MIME real con `finfo`, el tamaño (máximo 2 MB) y las dimensiones (20–4000 px). El archivo se guarda fuera de la carpeta pública, en `data/mascotas`, y se sirve únicamente por este script.

Al confirmar, PHP abre una **transacción**: bloquea el usuario y su carrito, vuelve a consultar los productos activos, valida cantidades/existencias y calcula el total usando los precios de MySQL. El navegador no decide cuánto cuesta un producto. Inserta el pedido y una copia de cada artículo con nombre, imagen, precio y cantidad, vacía el carrito y confirma la transacción. Si algo falla, deshace la operación. Una clave única evita duplicar el pedido cuando se repite la misma confirmación.

Los artículos del pedido son una fotografía histórica: sus precios y nombres no se actualizan cuando cambia el catálogo. No se descuenta stock porque es una demostración.

## Tablas

| Tabla | Qué guarda |
|---|---|
| `usuarios` | Cuentas existentes y nuevas: nombre, correo único y hash |
| `sc_productos` | Catálogo, precio en centavos enteros, existencia de demo y procedencia |
| `sc_carrito` | Una cantidad por producto y usuario |
| `sc_pedidos` | Usuario, fecha UTC, total, estado de demo y clave única de confirmación |
| `sc_pedido_items` | Copia de los artículos tal como se compraron |
| `sc_intentos` | Intentos fallidos de acceso para limitar reintentos |
| `sc_mascotas` | Una foto privada por cuenta: archivo, tipo MIME, tamaño y fecha |

## Menú, contenido y API

El menú se consulta desde MySQL: inicialmente **33 opciones en seis categorías**, transcritas de la referencia proporcionada de marzo de 2026. `data/menu.json` se conserva únicamente como semilla de instalación; editarlo no cambia el menú publicado. El nombre inicial “Moat” se dejó como aparece en la referencia. No se inventaron precios de comida, teléfonos, redes, fotografías del local ni horarios semanales. Los horarios se consultan en el enlace original de Maps. Los destacados incluyen fotografías de referencia de Pexels, permitidas para uso gratuito sin atribución obligatoria bajo su licencia; las fuentes están en `fuentes.php`. Las tres fotografías seleccionadas se sirven desde copias WebP locales; su procedencia se conserva en `fuentes.php`. No representan bebidas fotografiadas en el establecimiento. Las imágenes de gatos provienen del proyecto original y se reutilizan como ilustraciones; no representan a los gatos del establecimiento.

El catálogo inicial tiene **11 productos: tres locales y ocho obtenidos de la API Open Pet Food Facts**, consultada el 5 de noviembre de 2026. `data/api-original.json` conserva la respuesta y `data/catalogo.json` la selección normalizada. Todos los precios y existencias son ficticios. Se excluyeron fórmulas veterinarias Prescription y registros sin fotografía. Los nombres de la API conservan su idioma original cuando no existe una traducción al español.

La consulta remota se realiza desde PHP por CLI, no en cada visita. Para refrescar:

```bash
/opt/lampp/bin/php scripts/actualizar-catalogo.php
```

El script necesita MySQL, Internet y cURL. Valida categorías de gato, códigos de barras y dominio HTTPS de las imágenes; procesa hasta 12 productos por actualización. Conserva precios/existencias existentes y asigna $149 MXN ficticios a nuevas entradas. Las ocho fotos iniciales están guardadas en WebP localmente para funcionar sin Internet; las fotos nuevas que introduce el refresco se sirven desde la fuente externa. Si la API falla, se conserva el catálogo. No existe integración con inventarios o proveedores reales.

Fuentes oficiales: [Open Pet Food Facts API](https://support.openfoodfacts.org/help/en-gb/10-open-pet-food-facts/102-where-can-i-find-the-open-pet-food-facts-api), [documentación de búsqueda](https://openfoodfacts.github.io/documentation/docs/Product-Opener/v2/search/get-search/). Datos bajo ODbL; imágenes de productos bajo CC BY-SA. `fuentes.php` enlaza a cada ficha original. La procedencia y derechos de los recursos locales originales siguen pendientes de documentar antes de una publicación comercial.

## Verificar

```bash
python3 scripts/verificar.py
node --check JS/app.js
```

Para las pruebas HTTP completas, utiliza **una base temporal**, nunca la cuenta real de la cafetería. El script crea cuentas ficticias y pedidos de prueba:

```bash
CATSTORE_DB_NAME=snacks_cats_test /opt/lampp/bin/php scripts/instalar.php
CATSTORE_DB_NAME=snacks_cats_test /opt/lampp/bin/php -S 127.0.0.1:8876 -t . scripts/router.php
# En otra terminal:
CATSTORE_TEST_URL=http://127.0.0.1:8876 python3 scripts/pruebas_integracion.py
CATSTORE_DB_NAME=snacks_cats_test CATSTORE_TEST_URL=http://127.0.0.1:8876 \
  /opt/lampp/bin/php scripts/pruebas_menu.php
CATSTORE_TEST_URL=http://127.0.0.1:8876 \
PLAYWRIGHT_MODULE=/tmp/snacks-browser/node_modules/playwright/index.mjs \
CHROMIUM_PATH=/home/diego/.cache/ms-playwright/chromium-1234/chrome-linux64/chrome \
  node scripts/pruebas_ui.mjs
```

`pruebas_integracion.py` también comprueba la foto del michi: subida PNG válida, servido privado, rechazo de MIME falso, CSRF obligatorio y eliminación del archivo. Al terminar, la foto de prueba se borra; si una ejecución falla antes, revisa `data/mascotas` y elimina solo archivos `michi-*` de la base temporal.

El servidor PHP integrado usa el router para bloquear archivos privados. En XAMPP/Apache los `.htaccess` bloquean acceso a `data`, `scripts`, conexión y configuración. No expongas el servidor integrado de PHP a Internet.

## Alcance de esta entrega

No incluye pagos reales, administración de inventario, reservas, pedidos de comida ni recuperación de contraseña por correo (requiere configurar un servicio de correo). Antes de vender/publicar: confirmar los datos y derechos de marca/recursos con el negocio, obtener fotos reales y configurar un alojamiento PHP con HTTPS y credenciales de MySQL propias. Una publicación estática en Vercel no ejecuta este backend PHP.

Los antiguos `JS/script.js` y `JS/login.js` se conservan como referencia del proyecto previo; las plantillas actuales solo cargan `JS/app.js`. El reporte anterior queda en `docs/reporte-anterior.md`.


## Cambiar el menú en MySQL

Abre **http://localhost/phpmyadmin/** y selecciona `caotitos`:

- **`sc_menu_items`** contiene cada bebida: `nombre`, `descripcion`, `precio_centavos`, `categoria_id`, `orden`, `disponible` y `destacado_orden`.
- **`sc_menu_categorias`** contiene los títulos: `nombre`, `nota`, `orden` y `activa`.
- **`sc_migraciones`** registra la carga inicial para no reinsertar bebidas eliminadas ni restablecer precios al repetir el instalador.

Ejemplo: edita la fila de Lavanda latte y cambia `precio_centavos` de `7000` a `8250` para mostrar **$82.50 MXN**. Al recargar, cambia tanto en el menú como en el destacado del inicio. Puedes modificar su nombre o descripción del mismo modo. Usa `disponible=0` para ocultar una bebida sin borrar sus datos; deja de aparecer también en los destacados y los contadores. `activa=0` oculta una categoría completa.

`destacado_orden` define los destacados de la portada: números positivos (1, 2, 3...) determinan la prioridad; `NULL` significa no destacado. Se muestran como máximo tres bebidas disponibles en categorías activas. `orden` organiza las categorías o bebidas de menor a mayor. Para añadir una bebida, usa una `clave` única (ej. `latte-avellana`), un `categoria_id` existente, precio en centavos y descripción; el ID numérico se genera automáticamente.

Si todas las bebidas visibles de una categoría cuestan lo mismo, la leyenda del precio común se calcula automáticamente. Si cambias solo uno de sus precios, la leyenda desaparece para no mostrar un precio incorrecto. En `nota` escribe solo información descriptiva, sin precios fijos.

**PHP lee MySQL en cada petición:** los cambios aparecen al recargar la página, sin editar HTML/PHP ni reiniciar Apache. No hay sincronización automática con Google Maps ni un panel de edición propio; por ahora los cambios se hacen en phpMyAdmin.

La migración del menú puede ejecutarse de manera independiente:

```bash
/opt/lampp/bin/php scripts/migrar-menu.php
```

No toca usuarios, carritos ni pedidos. La importación inicial ocurre una sola vez y repetirla conserva todas las ediciones. Si MySQL no responde, se muestra un aviso en el menú y no se publican precios de respaldo desactualizados; el resto de la portada sigue disponible.

Para comprobar la actualización del menú en una instalación de prueba:

```bash
CATSTORE_DB_NAME=snacks_cats_test CATSTORE_TEST_URL=http://127.0.0.1:8876 /opt/lampp/bin/php scripts/pruebas_menu.php
```

La prueba exige un nombre de base que empiece por `snacks_cats_test`; modifica bebidas de ejemplo y restaura sus valores al finalizar. Nunca se ejecuta sobre `caotitos`.


## Movimiento y accesibilidad

La portada conserva las estrellas que giran lentamente, la postal del gato y una cinta que se desliza. El punto del logotipo late y el cuadrito decorativo se balancea; ambos usan `transform` y `opacity`, sin mover texto ni controles. Las microinteracciones al pulsar o pasar el cursor duran 160–200 ms. Los precios, formularios, categorías y pedidos permanecen quietos. No se incorporó una biblioteca de animación.

El botón **Pausar movimiento** detiene todas las animaciones decorativas y recuerda la elección durante la pestaña actual. Se puede usar con teclado. Con la preferencia del sistema **Reducir movimiento**, las animaciones se desactivan; sin JavaScript, los elementos siguen visibles y quietos. Los efectos de cursor se aplican únicamente con ratón o puntero fino. Una pestaña oculta pausa las animaciones.

Criterios consultados: [Taste Skill](https://github.com/Leonxlnx/taste-skill/blob/main/skills/taste-skill/SKILL.md) y [Animate de Emil Kowalski](https://github.com/emilkowalski/skills/blob/main/skills/animate/SKILL.md). Se aplicaron a una mejora puntual del diseño existente; los giros decorativos responden a la petición explícita del usuario y no a la pauta predeterminada de Taste para cafeterías.

Evidencia histórica de una versión anterior (no acredita la versión actual): capturas con `shoot.mjs` de Taste a 1440, 1024 y 390 px, más pruebas funcionales de giro, pausa, teclado, preferencia recordada, movimiento reducido, contenido sin JavaScript y ausencia de desbordamiento. El checker de layout de Taste no pudo completar su evaluación: exige las clases `.wrap`/`.container` de su plantilla, mientras el proyecto utiliza `.contenedor`. Esto no se presentó como una aprobación de ese checker ni se cambió la composición para adaptarla a su plantilla.


## Comprobantes y cambios del 11 de noviembre de 2026

Desde **Mi cuenta → Descargar comprobante CSV**, cada usuario puede descargar un pedido propio. PHP verifica la sesión y la propiedad antes de consultar el detalle, crea un archivo temporal, escribe CSV UTF-8, lo lee para enviarlo como descarga y lo elimina al cerrar el recurso. La descarga no sube archivos; la carga de la foto del michi se explica en los ajustes finales. Los nombres potencialmente interpretables como fórmulas se neutralizan. Un pedido ajeno devuelve 404 y sin sesión se redirige al login.

Para instalaciones existentes, ejecutar:

```bash
/opt/lampp/bin/php scripts/integridad.php
```

La migración adapta las columnas de usuario al tipo real de `usuarios.id` y agrega claves foráneas. Si encuentra registros huérfanos, se detiene sin borrarlos. Los ALTER de MySQL no son una transacción conjunta: si falla la segunda tabla, se puede volver a ejecutar. El instalador también llama a esta comprobación. Se ejecutó correctamente sobre `caotitos` y sobre la base temporal `snacks_cats_test` el 11 de noviembre de 2026.

El actualizador confirma MySQL primero y después reconstruye el JSON con los datos guardados, incluidos precios actuales. Si falla el archivo, informa del error y la base conserva los cambios; repetir el script permite reconstruir el respaldo. No existe atomicidad conjunta entre MySQL y el sistema de archivos.

La revisión final pasó `python3 scripts/verificar.py`, `scripts/pruebas_integracion.py`, `scripts/pruebas_menu.php` y `scripts/pruebas_ui.mjs` contra `snacks_cats_test`. Las pruebas HTTP cubren además la subida, el servido y la eliminación de la foto del michi. Las capturas de `docs/capturas-rediseno/` se regeneraron con la versión actual; las de `docs/capturas/` corresponden a la versión anterior Caotitos/HTML y no acreditan esta entrega.

## Navegación continua del menú

Las categorías de `menu.php` son enlaces internos (`#cafe`, `#sin-cafe`, etc.). Todas las secciones disponibles se renderizan juntas desde MySQL; cambiar de categoría desplaza la vista sin una nueva petición. El desplazamiento respeta movimiento reducido y deja espacio bajo la cabecera y la barra móvil. La selección se actualiza al recorrer el contenido. Los antiguos enlaces `?categoria=` siguen funcionando con JavaScript. Los gatos decorativos reutilizan los recursos locales del proyecto; no se añadieron memes externos.


## Ajustes finales de diseño y archivos — 11 de noviembre de 2026

- **Un gato distinto por pantalla.** Ningún archivo de gato se repite dentro de una misma página. El pie elige un personaje según `$pagina`; el menú usa seis gatos diferentes para sus categorías y otro para el anfitrión y el recordatorio. El pie dejó de usar el logotipo del gato para no duplicar el personaje de la marca.
- **Fondo negro para recortes.** Las ilustraciones con transparencia se presentan sobre un escenario carbón (`#12100e`) con bordes redondeados: arco de portada, gato de visita, categorías del menú, preguntas, estados vacíos y retrato del pie. Así los bordes del recorte se integran con la composición.
- **Pie propio por página.** Inicio, menú, cafetería, tiendita, login, registro, carrito, pedidos y fuentes tienen un personaje y un texto de despedida distintos, centrados y con enlace directo a WhatsApp.
- **WhatsApp visible.** El número `614 120 5926` abre `wa.me/526141205926` con un mensaje editable de dudas. Hay un botón flotante en todas las páginas y una llamada a la acción en el pie; ninguno envía mensajes de forma automática.
- **Menú por colores.** Cada categoría conserva una pestaña superior de color (café, salvia, rosa, azul, amarillo y taupe) y un sello de gato propio. Los contadores y precios siguen viniendo de MySQL.
- **Foto privada del michi.** En **Mis pedidos** se puede subir, reemplazar y eliminar una foto JPG, PNG o WebP de hasta 2 MB. Se valida el MIME real, el tamaño y las dimensiones; se guarda con nombre aleatorio en `data/mascotas`, se sirve solo con sesión y se elimina el archivo anterior al reemplazarlo. La carpeta está bloqueada por `.htaccess` y por `scripts/router.php`.
- **Pruebas.** `pruebas_integracion.py` cubre la foto (subida, servido, MIME falso, CSRF y borrado); `verificar.py` revisa sintaxis, recursos y protección de carpetas; `pruebas_menu.php` y `pruebas_ui.mjs` cubren el menú y la navegación.

Las capturas actuales están en `docs/capturas-rediseno/`. La revisión automatizada no sustituye una prueba en dispositivos físicos ni una revisión manual de accesibilidad.

## Rediseño editorial — noviembre de 2026

La implementación y sus verificaciones se describen en `docs/rediseno-2026-11.md`. Se conserva PHP/MySQL, las consultas, los precios, los productos y los flujos de cuenta y compra. Los horarios, redes y condiciones del negocio que no se pudieron verificar no se incorporaron.

Pruebas de interacción en navegador (dependencia de prueba fuera del proyecto):

```bash
npm install --prefix /tmp/snacks-browser playwright
CATSTORE_TEST_URL=http://127.0.0.1:8876 \
PLAYWRIGHT_MODULE=/tmp/snacks-browser/node_modules/playwright/index.mjs \
node scripts/pruebas_ui.mjs
```

Playwright necesita un Chromium instalado; se puede indicar su ejecutable con `CHROMIUM_PATH`. El servidor de prueba debe apuntar a una base temporal, como en las pruebas HTTP anteriores. Las capturas actuales están en `docs/capturas-rediseno/`; las capturas antiguas de Caotitos no acreditan esta versión.


## Revisión de estructura y personajes únicos

La simplificación posterior se documenta en `docs/revision-premium.md`. La guía de archivos está en `docs/guia-del-codigo.md` y las capturas actuales en `docs/capturas-premium/`. Los gatos se asignan una sola vez desde `data/personajes.php`; el pie conserva un único enlace de WhatsApp. Las pantallas, formularios, sesiones, datos y flujo de fotos de cuenta siguen usando la lógica existente.

## Publicación completa

El despliegue PHP/MySQL está preparado para Railway con `Dockerfile`, `railway.json` y `deployment/`. Consulta `docs/railway.md` para las referencias de MySQL, los volúmenes y las comprobaciones. Las credenciales locales y fotos privadas quedan fuera de Git y de la imagen Docker.

Vercel se mantiene pausado para las nuevas versiones PHP mediante `vercel.json`; su publicación anterior no representa el backend completo. La web completa se entrega desde el alojamiento PHP una vez configurado y verificado.
