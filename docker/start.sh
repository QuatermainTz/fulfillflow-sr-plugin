#!/bin/sh
set -e

sed "s/PORT_PLACEHOLDER/${PORT:-10000}/" /app/docker/nginx.conf > /etc/nginx/http.d/default.conf

php-fpm -D
nginx -g "daemon off;"
