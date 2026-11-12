FROM php:8.3-apache-bookworm

# Extensiones usadas por cuentas, MySQL, validación de archivos y catálogo.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install -j2 pdo_mysql mbstring curl \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY deployment/apache.conf /etc/apache2/conf-available/snacks.conf
RUN a2enconf snacks
COPY --chown=www-data:www-data . /var/www/html/
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
