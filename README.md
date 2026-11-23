# Snacks and Cats

Sitio PHP de una cafetería gatuna en Chihuahua. Incluye el menú, la ubicación, una tienda de demostración, cuentas, carrito persistente, pedidos y foto privada de perfil.

**Sitio en vivo:** https://web-production-1899fab5.up.railway.app/

## Ejecutar en local

Usa PHP 8.2 o posterior con PDO MySQL, mbstring y fileinfo. cURL se usa para actualizar el catálogo desde la API. MySQL 8 o MariaDB de XAMPP sirven como base. En XAMPP para Linux, sustituye `php` por `/opt/lampp/bin/php` si hace falta. Ejecuta los comandos desde la raíz del proyecto.

1. Inicia MySQL y prepara una base local con un usuario autorizado a instalar tablas. Puedes copiar `.env.example` como `.env`, editar los valores de ejemplo y exportarlos desde tu shell. También puedes copiar `config/config.example.php` como `config/config.local.php` y editarlo. El archivo PHP se carga automáticamente y las variables del entorno tienen prioridad. En XAMPP para Linux puedes indicar `CATSTORE_DB_SOCKET=/opt/lampp/var/mysql/mysql.sock`.
2. Instala el esquema y los datos iniciales y arranca el servidor.

   ```sh
   set -a
   . ./.env   # Omite estas tres primeras líneas si usas config.local.php.
   set +a
   php scripts/instalar.php
   php -S 127.0.0.1:8876 -t public
   ```

3. Abre el sitio. **El menú** debe mostrar precios y **Tiendita** debe mostrar los productos iniciales. [Inicio local](http://127.0.0.1:8876/)

Para un sitio virtual Apache, apunta el `DocumentRoot` a `public/`, deshabilita `AllowOverride` en la raíz del repositorio y concede acceso a `public/`. Para usar el proyecto como subcarpeta de XAMPP, habilita `mod_rewrite` y `AllowOverride All` para que el `.htaccess` mantenga las rutas, por ejemplo `http://localhost/CATSTORE-BUENO-PARA-CLASE/`.

## Configurar el almacenamiento

El usuario que ejecuta PHP debe poder escribir en `data/mascotas/` o en el directorio privado indicado por `CATSTORE_UPLOAD_DIR`. Para conservar fotos de una instalación existente, mantén ese directorio o apunta la variable a su ubicación. El instalador conserva las cuentas, los pedidos y las fotos existentes.

En Railway, el `Dockerfile` prepara el esquema y Apache sirve `/var/www/html/public`. MySQL usa el volumen `/var/lib/mysql`. El servicio web usa `/var/lib/snacks`, con `uploads/` para fotos y `sessions/` para sesiones. Configura las variables de MySQL mediante referencias entre servicios. [Guía de Railway](docs/railway.md)

## Ejecutar las pruebas

Para las pruebas que escriben datos, instala y arranca una base temporal con un nombre que empiece por `snacks_cats_test`. Arranca PHP con la misma configuración de base y un directorio de fotos de prueba.

```sh
export CATSTORE_TEST_URL=http://127.0.0.1:8876
python3 tests/verificar.py
python3 tests/pruebas_integracion.py
php tests/pruebas_menu.php
```

Para Chromium, instala Playwright y su navegador con `npm install --no-save --package-lock=false playwright` y `npx playwright install chromium`. Usa `PLAYWRIGHT_MODULE` para apuntar a un módulo instalado en otra ruta o `CHROMIUM_PATH` si ya tienes un Chromium compatible.

```sh
node tests/pruebas_ui.mjs
CATSTORE_TEST_WRITE=1 node tests/pruebas_rubrica.mjs
```

La segunda prueba comprueba 360, 768 y 1366 px. Con `CATSTORE_TEST_WRITE=1` crea una cuenta ficticia, verifica carrito y foto, y retira ambos al terminar.

## Consultar la entrega

- [Resultados de verificación y versión publicada](docs/entrega.md)
- [Rúbrica y recorrido de coevaluación](docs/coevaluacion.md)
- [URL pública](URL.txt)
