FROM php:7.4-cli-alpine

RUN apk add --no-cache \
        unzip git sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN cp example.env .env
RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p db runtime && chmod -R 777 db runtime

# Render provides $PORT - PHP's built-in server serves the public/ dir
ENV PORT=10000
EXPOSE 10000

CMD php -d display_errors=1 -d error_reporting=E_ALL -S 0.0.0.0:${PORT} -t public