#!/bin/sh
set -eu

: "${CATSTORE_DB_HOST:?Configura CATSTORE_DB_HOST con la referencia del servicio MySQL.}"
: "${CATSTORE_DB_NAME:?Configura CATSTORE_DB_NAME.}"
: "${CATSTORE_DB_USER:?Configura CATSTORE_DB_USER.}"
: "${CATSTORE_DB_PASSWORD:?Configura CATSTORE_DB_PASSWORD.}"

port="${PORT:-8080}"
case "$port" in
    ''|*[!0-9]*) echo 'El puerto de la aplicación no es válido.' >&2; exit 1 ;;
esac
if [ "$port" -lt 1 ] || [ "$port" -gt 65535 ]; then
    echo 'El puerto de la aplicación está fuera de rango.' >&2
    exit 1
fi

sed -i "s/^Listen 80$/Listen $port/" /etc/apache2/ports.conf
sed -i "s/:80>/:$port>/" /etc/apache2/sites-available/000-default.conf

# El volumen se monta antes del arranque; ajustar permisos sin usar chmod 777.
mkdir -p /var/lib/snacks/uploads /var/lib/snacks/sessions
chown -R www-data:www-data /var/lib/snacks
chmod 750 /var/lib/snacks/uploads /var/lib/snacks/sessions

# MySQL puede tardar más que la aplicación en iniciar. La instalación es idempotente.
intento=1
until php scripts/instalar.php > /tmp/snacks-instalacion.log 2>&1; do
    if [ "$intento" -ge 30 ]; then
        cat /tmp/snacks-instalacion.log >&2
        echo 'No se pudo preparar MySQL. Revisa las referencias de las variables.' >&2
        exit 1
    fi
    intento=$((intento + 1))
    sleep 2
done
cat /tmp/snacks-instalacion.log
# Validar también con el entorno real del servicio, antes de iniciar Apache.
ls -l /etc/apache2/mods-enabled/*mpm*
grep -R LoadModule /etc/apache2/conf-enabled /etc/apache2/sites-enabled || true
apache2ctl -t
# Cargar explícitamente el entorno de Debian antes del proceso en primer plano.
. /etc/apache2/envvars
exec /usr/sbin/apache2 -D FOREGROUND
