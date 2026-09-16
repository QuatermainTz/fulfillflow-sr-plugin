FROM php:7.4-fpm-alpine

RUN apk add --no-cache \
        unzip git sqlite-dev nginx \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN cp example.env .env

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p db runtime && chmod -R 777 db runtime

RUN chmod +x docker/start.sh

ENV PORT=10000
EXPOSE 10000

CMD ["sh", "docker/start.sh"]
