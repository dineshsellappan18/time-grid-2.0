# Multi-stage image for Timegrid (WO-014 scaffolding).
# Target runtime PHP 8.3+ lands with WO-015; this stage documents the intended topology.

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
COPY packages ./packages
COPY app/Support/PlanConfig ./app/Support/PlanConfig
COPY app/helpers.php ./app/helpers.php
COPY database ./database
RUN composer install --no-dev --prefer-dist --no-interaction --ignore-platform-reqs --no-scripts

FROM php:8.3-fpm-bookworm AS app
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring gd \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN chown -R www-data:www-data storage bootstrap/cache
USER www-data
EXPOSE 9000
CMD ["php-fpm"]
