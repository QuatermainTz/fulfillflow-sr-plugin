FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
    unzip git libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p db runtime && chmod -R 777 db runtime

# Render provides $PORT - PHP's built-in server serves the public/ dir
ENV PORT=10000
EXPOSE 10000

CMD php -S 0.0.0.0:${PORT} -t public
