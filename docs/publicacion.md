# Publicación completa de Snacks and Cats

El sitio usa PHP 8.2+, MySQL/MariaDB, sesiones y archivos privados. La versión actual no es una web estática: necesita un servidor PHP, una base de datos y almacenamiento persistente para las fotos.

## GitHub y Vercel

El código fuente completo se conserva en `ARIOT117/CATSTORE`, rama `main`. No se incluyen credenciales locales, cuentas/pedidos de la base de datos ni fotos privadas.

El proyecto Vercel existente aloja la entrega anterior en HTML. Mientras se configura el alojamiento completo, `vercel.json` omite automáticamente los builds (`ignoreCommand: exit 0`) y exige un directorio de salida que no se genera en esta entrega. Esto conserva la publicación anterior y evita servir archivos PHP como contenido estático. `.vercelignore` también excluye el código de servidor y los directorios privados. No se debe promover una nueva publicación estática de este proyecto como si tuviera el backend funcionando.

## Requisitos del alojamiento completo

- PHP 8.2 o superior con `pdo_mysql`, `mbstring`, `fileinfo` y sesiones.
- MySQL o MariaDB accesible desde la aplicación.
- HTTPS y configuración de cookies correspondiente.
- Persistencia de la base y de las fotos de `data/mascotas` entre publicaciones.
- Protección de `data`, `scripts`, archivos de configuración y código interno mediante Apache/.htaccess o reglas equivalentes.

## Configuración

Se puede usar `config.local.php`, sin subirlo a GitHub, o estas variables:

- `CATSTORE_DB_HOST`
- `CATSTORE_DB_PORT`
- `CATSTORE_DB_NAME`
- `CATSTORE_DB_USER`
- `CATSTORE_DB_PASSWORD`
- `CATSTORE_DB_SOCKET` (normalmente vacío en alojamiento remoto)

Los valores reales se configuran en el proveedor. No se escriben contraseñas en el repositorio ni en mensajes del chat.

Con acceso CLI, `php scripts/instalar.php` prepara tablas y datos semilla sin borrar cuentas ni pedidos. Si el alojamiento no ofrece CLI se debe preparar un SQL de instalación limpio para importarlo desde su panel. No se debe importar una base temporal de pruebas ni copiar las credenciales locales de XAMPP.

## Railway

Railway puede ejecutar PHP y un servicio MySQL. Se debe revisar primero la elegibilidad de la cuenta y el crédito disponible. La prueba y el plan Free tienen límites de consumo y almacenamiento; no garantizan alojamiento permanente de esta aplicación sin coste.

Fuentes oficiales revisadas: https://railway.com/pricing y https://docs.railway.com/pricing/free-trial. La omisión de builds de Vercel se documenta en https://vercel.com/kb/guide/how-do-i-use-the-ignored-build-step-field-on-vercel.

## Antes de dar por publicada la web

Comprobar en la URL pública: páginas, precios reales del menú, registro/login, cierre de sesión, carrito, pedido, aislamiento entre cuentas, CSV y subida/eliminación de fotos. Confirmar también que la base y los archivos sobreviven a una nueva publicación.
