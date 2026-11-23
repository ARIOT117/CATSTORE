# Verificación de la entrega

La rama `entrega/universidad` separa la raíz pública del código compartido, la configuración, la base y las pruebas. La URL pública sigue en el commit anterior `eff7a1b3df683456df1721016fb4085512bc894b` de `main`, activo desde el 12 de noviembre de 2026. El sitio publicado y el ZIP comparten la navegación, el diseño y los recorridos descritos. La raíz `public/`, las pruebas extendidas, las exclusiones y la documentación pertenecen a la rama de entrega.

## Inventario previo

- **Entradas de páginas**. `index.php`, `menu.php`, `nosotros.php`, `productos.php`, `login.php`, `registro.php`, `carrito.php`, `pedidos.php` y `fuentes.php`.
- **Formularios y acciones**. Registro y acceso hacen POST a sus propias páginas. `acciones.php` recibe altas y cambios del carrito, confirmación de pedidos y cierre de sesión. `comprobante.php` descarga un CSV del pedido de la cuenta.
- **Base y sesiones**. `auth.php` usa PDO y MySQL. El registro escribe en `usuarios`, el carrito autenticado en `sc_carrito` y los pedidos en `sc_pedidos` y `sc_pedido_items`. `app.php` inicia la sesión, genera CSRF y reúne el carrito de invitado con el de la cuenta al acceder.
- **Imágenes**. Las imágenes públicas se referencian con `assets/`. `mascota.php` guarda JPG, PNG o WebP de hasta 2 MB con nombre generado. La tabla `sc_mascotas` guarda los metadatos. La foto se sirve al dueño de la cuenta. Las fotos de perfil son el flujo existente que cubre la gestión de archivos.
- **Arranque previo y nuevo**. XAMPP y el servidor de desarrollo servían desde la raíz del repositorio. En Railway, Apache servía `/var/www/html`. La rama sirve `public/` desde PHP y Apache. El `.htaccess` de la raíz conserva las URL para XAMPP en subcarpeta.
- **Línea base**. Antes de mover los archivos, Inicio, El menú, La cafetería, Tiendita, Acceder y Carrito devolvieron HTTP 200 en local y Railway. Mis pedidos devolvió 303 hacia Acceder con sesión anónima. `/CSS/style.css` y `/JS/app.js` devolvieron 200 en ambos entornos.

## Resultados

| Requisito | Prueba realizada | Resultado local | Resultado en Railway | Pendiente |
| --- | --- | --- | --- | --- |
| 1. Inventario y línea base | Rutas, recursos con mayúsculas, formularios y configuración | Seis páginas 200 y Mis pedidos 303 sin sesión. CSS y JS 200. | Mismos códigos. El servicio usa Docker, Apache y MySQL. | — |
| 2. Formularios, sesión, archivos y HTTPS | Registro desde Chromium, consulta SQL, otra sesión, carrito, pedido demo y foto | Cuenta visible en `usuarios` con hash. Carrito y pedido se recuperaron. Subida, reemplazo, tamaño/MIME, foto anterior ante rechazo y borrado probados. | HTTP redirige a HTTPS. Registro, sesión, carrito y alta/borrado de foto probados con datos ficticios. Volúmenes montados en ambas rutas. | SQL directo, pedido nuevo, reemplazo y persistencia tras un redespliegue en Railway. |
| 3. Carpetas y compatibilidad | Arranque con PHP y rutas públicas y privadas con Apache | Rutas conservadas y lógica fuera de `public/`. Pruebas HTTP y de navegador pasaron. | Rutas públicas equivalentes del commit anterior. | Publicar la rama y verificar Apache en el contenedor de Railway. |
| 4. Git y exclusiones | `git ls-files` y `git check-ignore` | Ejemplos y SQL conservados. Se retiraron del índice el ajuste local del editor y un retrato personal en desuso. | Fuera del alcance de la versión publicada anterior. | Publicar la rama para que el retrato fuera de uso deje de formar parte del código servido. |
| 5. Rúbrica funcional | Chromium a 360, 768 y 1366 px, teclado, foco, búsqueda, filtros y axe | Latte a $70.00 MXN, dirección, ficha de Google Maps, filtros y carrito verificados. Navegaciones medidas entre 57 y 180 ms hasta `DOMContentLoaded`. | Equivalente en los recorridos automatizados. Navegaciones de 178 a 4,589 ms en una corrida. | Contraste marcado para revisión manual, lector de pantalla y pruebas con personas y dispositivos reales. |
| 6. Entrega reproducible | Integridad, extracción limpia, lectura de `URL.txt` y arranque | Incluye instalación, configuración, SQL, semillas, pruebas y documentación de entrega. | `URL.txt` señala el despliegue anterior. | Igualar el código desplegado con el ZIP. |
| 7. Coevaluación | Recorrido y tabla de diez criterios | Guía preparada con procedimientos de base y archivos. | Acciones automatizables comprobadas en la versión pública según la tabla. | Observación del compañero, especialmente propósito percibido, satisfacción y memorabilidad. |

Axe-core 4.10.3 no reportó infracciones automáticas en 21 navegaciones anónimas por entorno. Marcó elementos de Inicio y El menú para inspección manual de contraste. La medición de carga es una muestra de navegador, con tiempos afectados por la red y el estado de caché.

La cuenta ficticia de la prueba en Railway permanece como registro. Al terminar se retiraron su foto y su artículo del carrito. El pedido demo se confirmó solo en la base temporal porque escribirlo en Railway habría afectado el inventario publicado.

## Exclusiones

- **Repositorio**. Configuración y variables reales, llaves, logs, temporales, respaldos privados, bases de ejecución, sesiones, fotos de usuarios, carpetas locales del editor y herramientas, dependencias opcionales reconstruibles y ZIP generados. Se conservaron `.env.example`, el ejemplo PHP, los esquemas, las semillas, los recursos oficiales y la configuración de Railway. La revisión por rutas y patrones no encontró credenciales versionadas. El historial se conserva.
- **ZIP**. Además de esas exclusiones, el paquete omite `.git`, capturas e informes históricos y configuraciones antiguas de publicación. Los datos de demostración son los JSON y SQL de la rama. Los archivos locales de la carpeta original se conservan.

[Coevaluación y recorrido](coevaluacion.md)
