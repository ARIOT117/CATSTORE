# Configurar Snacks and Cats en Railway

El proyecto `snacks-and-cats` usa un servicio web PHP con Apache y un servicio MySQL. La compilación toma el `Dockerfile` desde la raíz del repositorio. Apache sirve `/var/www/html/public` en el puerto que indica `PORT`. El chequeo de salud solicita `/` y el arranque ejecuta `scripts/instalar.php`.

## Conectar MySQL

Añade MySQL 8 y configura estas variables en el servicio web mediante el selector de referencias de Railway. Si llamaste de otra forma al servicio de datos, ajusta el nombre `MySQL` en las referencias.

| Variable web | Referencia de Railway |
| --- | --- |
| `CATSTORE_DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `CATSTORE_DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `CATSTORE_DB_NAME` | `${{MySQL.MYSQLDATABASE}}` |
| `CATSTORE_DB_USER` | `${{MySQL.MYSQLUSER}}` |
| `CATSTORE_DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |

El instalador prepara el esquema y las semillas iniciales. Conserva las cuentas y pedidos existentes. Las referencias pertenecen al entorno de Railway. El ejemplo local contiene valores ficticios.

## Mantener el almacenamiento

| Servicio | Ruta del volumen | Contenido |
| --- | --- | --- |
| MySQL | `/var/lib/mysql` | Esquema, cuentas, carrito, pedidos y metadatos de fotos |
| Web | `/var/lib/snacks` | `uploads/` para fotos y `sessions/` para sesiones |

El contenedor mantiene las rutas usadas por el despliegue anterior y ajusta sus permisos al arrancar. Para una actualización, conserva ambos volúmenes. La foto queda fuera de la raíz pública y se entrega mediante `mascota.php`, que exige una sesión válida. [Volúmenes de Railway](https://docs.railway.com/volumes)

Después de publicar, abre `URL.txt`, compara el commit activo con el código entregado y comprueba HTTPS, las páginas, el carrito entre sesiones y una foto ficticia. Conserva las mayúsculas de `CSS/` y `JS/` al verificar los recursos.
