# Imagen de producción de Smart Waste Bin para Render (PHP + Apache).
# La base de datos MySQL vive en Aiven; la conexión se configura con variables de entorno.
FROM php:8.3-apache

# Extensión MySQL y configuración de PHP para producción
RUN docker-php-ext-install mysqli \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php-produccion.ini "$PHP_INI_DIR/conf.d/zz-smartbin.ini"

# Apache: oculta versión y bloquea archivos internos (SQL, docs, scripts)
COPY docker/apache-seguridad.conf /etc/apache2/conf-available/smartbin-seguridad.conf
RUN a2enconf smartbin-seguridad && a2enmod headers

# Script de arranque: Render indica el puerto en la variable PORT
COPY docker/iniciar.sh /usr/local/bin/iniciar-smartbin
RUN sed -i 's/\r$//' /usr/local/bin/iniciar-smartbin && chmod +x /usr/local/bin/iniciar-smartbin

# Código de la aplicación (ver .dockerignore para lo que se excluye)
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
RUN rm -rf docker Dockerfile render.yaml

ENV PORT=10000
EXPOSE 10000
CMD ["iniciar-smartbin"]
