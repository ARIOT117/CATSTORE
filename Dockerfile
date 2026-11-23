FROM php:8.3-apache-bookworm

# Extensiones usadas por cuentas, MySQL, validación de archivos y catálogo.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install -j2 pdo_mysql mbstring curl \
    && a2dismod -f mpm_event mpm_worker \
    && a2enmod mpm_prefork headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY deployment/apache.conf /etc/apache2/conf-available/snacks.conf
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf \
    && a2enconf snacks
COPY public/ /var/www/html/public/
COPY app/ /var/www/html/app/
COPY data/ /var/www/html/data/
COPY scripts/ /var/www/html/scripts/
RUN apache2ctl -t
COPY deployment/entrypoint.sh /usr/local/bin/snacks-start
RUN chmod 755 /usr/local/bin/snacks-start \
    && mkdir -p /var/lib/snacks/uploads /var/lib/snacks/sessions \
    && chown -R www-data:www-data /var/lib/snacks \
    && printf '%s\n' \
        'session.save_path=/var/lib/snacks/sessions' \
        'upload_max_filesize=3M' \
        'post_max_size=4M' \
        'memory_limit=128M' \
        'display_errors=Off' \
        'log_errors=On' \
        'expose_php=Off' \
        > /usr/local/etc/php/conf.d/snacks.ini

ENV CATSTORE_UPLOAD_DIR=/var/lib/snacks/uploads
WORKDIR /var/www/html
EXPOSE 8080
CMD ["snacks-start"]
