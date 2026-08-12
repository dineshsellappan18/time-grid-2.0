FROM richarvey/nginx-php-fpm:3.1.6

ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apk add --no-cache postgresql-dev nodejs npm \
    && docker-php-ext-install pdo_pgsql

COPY . .

RUN composer dump-autoload --optimize --no-interaction

RUN npm ci 2>/dev/null || npm install \
    && npm run build \
    && rm -rf node_modules \
    && apk del nodejs npm

RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

CMD ["/start.sh"]
