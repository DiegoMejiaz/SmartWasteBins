#!/bin/sh
# Arranque del contenedor: Apache escucha en el puerto que asigna Render ($PORT).
set -e
PUERTO="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf
echo "Smart Waste Bin escuchando en el puerto ${PUERTO}"
exec apache2-foreground
