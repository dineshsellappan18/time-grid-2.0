FROM php:8.3-cli-alpine

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apk add --no-cache \
        nginx supervisor curl git unzip \
        postgresql-dev libpng-dev libxml2-dev oniguruma-dev \
        nodejs npm \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring gd xml bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer dump-autoload --optimize --no-interaction --ignore-platform-reqs

RUN npm ci 2>/dev/null || npm install \
    && npm run build \
    && rm -rf node_modules

RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs bootstrap/cache /run/nginx \
    && chmod -R 777 storage bootstrap/cache

COPY docker/render/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/render/supervisord.conf /etc/supervisor/conf.d/app.conf

RUN echo "upload_max_filesize=20M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=25M" >> /usr/local/etc/php/conf.d/uploads.ini

COPY scripts/00-laravel-deploy.sh /usr/local/bin/deploy.sh
RUN chmod +x /usr/local/bin/deploy.sh

EXPOSE 10000

CMD ["/bin/sh", "-c", "/usr/local/bin/deploy.sh && /usr/bin/supervisord -c /etc/supervisor/conf.d/app.conf"]
