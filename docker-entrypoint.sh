#!/bin/bash
set -e

# Support Railway dynamic port binding
PORT="${PORT:-80}"
sed -i "s/Listen .*/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:.*/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf

exec "$@"
