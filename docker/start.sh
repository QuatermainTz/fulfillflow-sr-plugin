#!/bin/sh
set -e

sed "s/PORT_PLACEHOLDER/${PORT:-10000}/" /app/docker/nginx.conf > /etc/nginx/http.d/default.conf

php /app/console.php db:create || true

chmod -R 777 /app/db /app/runtime

php-fpm -D
nginx -g "daemon off;"