# Publicar Snacks and Cats en Railway

## Estado

Publicada el 11 de noviembre de 2026 (Chihuahua): https://web-production-1899fab5.up.railway.app

Proyecto Railway: `snacks-and-cats`. Servicios `web` (PHP 8.3/Apache) y `MySQL`, con volúmenes independientes de 500 MB. Código en `ARIOT117/CATSTORE`, rama `main`. No hay credenciales en el código. La cuenta se verificó en prueba, con $5 de crédito inicial, 30 días y sin método de pago; no se activó una suscripción de pago.

El objetivo de esta entrega es una demostración escolar de 2–3 días. Eso no garantiza consumo gratuito: debe revisarse la cuenta antes de crear servicios y después de publicarlos. No se debe activar un plan de pago ni añadir recursos si exceden lo autorizado por Diego.

## Servicios

1. Crear un proyecto y añadir un servicio **MySQL**.
2. Añadir un servicio desde GitHub: **ARIOT117/CATSTORE**, rama **main**.
3. Railway debe detectar el `Dockerfile`. `railway.json` configura el constructor y la comprobación de salud.
4. Configurar estas variables en el servicio web, usando referencias al servicio MySQL (si se llama `MySQL`):

| Variable de la web | Valor en Railway |
|---|---|
| `CATSTORE_DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `CATSTORE_DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `CATSTORE_DB_NAME` | `${{MySQL.MYSQLDATABASE}}` |
| `CATSTORE_DB_USER` | `${{MySQL.MYSQLUSER}}` |
| `CATSTORE_DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |

No copiar las contraseñas al chat ni a GitHub. Si el nombre del servicio es diferente, ajustar la referencia desde el selector de variables de Railway.

## Persistencia

- MySQL necesita su propio volumen.
- Para conservar fotos y sesiones entre publicaciones, montar un volumen del servicio web en **`/var/lib/snacks`**.
- `Dockerfile` establece `CATSTORE_UPLOAD_DIR=/var/lib/snacks/uploads`. Las sesiones se guardan en `/var/lib/snacks/sessions`.
- El arranque ajusta permisos al usuario de Apache sin usar permisos 777.
- Si la cuenta no permite ambos volúmenes o no tiene crédito suficiente, detener la configuración y revisar una alternativa; no dar por completa la persistencia ni contratar automáticamente un plan.

En la instalación XAMPP nada cambia: si `CATSTORE_UPLOAD_DIR` no existe, las fotos siguen guardándose en `data/mascotas`.

## Arranque y dominio

El contenedor espera a MySQL y ejecuta el instalador idempotente. Solo crea tablas/semillas que faltan; no borra cuentas ni pedidos. Apache escucha el puerto asignado por Railway y reconoce HTTPS terminado por su proxy. La comprobación de salud usa `/`. El arranque desactiva explícitamente los módulos event/worker para evitar un conflicto con prefork observado en el runtime; la configuración se valida antes de iniciar Apache.

Una vez listo el servicio, generar un dominio público de Railway en Networking y confirmar que abre con HTTPS. Este será el enlace completo para el profesor; no hace falta comprar un dominio.

## Comprobar antes de entregar

- Inicio y menú muestran los precios previstos.
- Registro, login y cierre de sesión funcionan.
- El carrito se guarda, se confirma un pedido y aparece en la cuenta correcta.
- El comprobante CSV se descarga.
- Una foto válida se sube, se sirve solo a su propietario y se puede eliminar.
- Tras volver a desplegar el servicio web, la cuenta, el pedido y la foto siguen presentes.
- `/data/`, `/scripts/`, `/deployment/`, `auth.php`, `app.php` y `config.local.php` no son accesibles desde el navegador.
- La cookie de sesión tiene HttpOnly, SameSite y Secure en HTTPS.
- Revisar el consumo real de Railway. Al terminar la revisión escolar, decidir expresamente si se conserva o elimina el proyecto; esta preparación no programa borrados automáticos.

## Verificaciones realizadas localmente

- Sintaxis PHP/JS y recursos: pasan.
- Sintaxis de `deployment/entrypoint.sh`: pasa.
- Pruebas HTTP con `CATSTORE_UPLOAD_DIR` en un directorio fuera de la web: pasan (sesión, fotos, validación MIME/CSRF, pedidos y CSV).
- La imagen Docker se construyó en Railway y Apache respondió correctamente por HTTPS.

## Verificaciones en la URL pública

- Pruebas HTTP completas: registro/login/logout, CSRF, carrito, precios del servidor, cantidades, pedidos idempotentes, aislamiento entre cuentas y CSV: pasan.
- Fotos privadas: carga, MIME falso, CSRF, acceso del propietario y eliminación: pasan.
- Cookies Secure y HttpOnly verificadas; SameSite=Lax configurado por PHP.
- Navegador Chromium: siete páginas en 1440, 390 y 320 px (21 vistas), sin desbordamientos.
- Tras redesplegar ambos servicios, la misma sesión recuperó la cuenta, el registro y el archivo privado; la foto de prueba se eliminó después de comprobarlo.
- Pruebas hechas con cuentas ficticias y pedidos de demostración; no se cobran compras.
- MySQL configurado con buffer InnoDB de 64 MB y máximo 30 conexiones para esta demostración.
- El crédito limita la disponibilidad: no se garantiza duración exacta ni alojamiento permanente gratuito. Revisar Usage en Railway después de la evaluación.

## Fuentes oficiales

- https://docs.railway.com/builds/dockerfiles
- https://docs.railway.com/databases/mysql
- https://docs.railway.com/volumes
- https://railway.com/pricing
- https://hub.docker.com/_/php
