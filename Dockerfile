# syntax=docker/dockerfile:1
FROM php:8.4-apache-bookworm AS php-base
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libsqlite3-dev unzip \
    && docker-php-ext-install -j"$(nproc)" mbstring pdo_sqlite opcache \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\nServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/fast-track.conf \
    && a2enconf fast-track \
    && printf 'expose_php=Off\nopcache.enable=1\nopcache.validate_timestamps=0\n' > /usr/local/etc/php/conf.d/app.ini
WORKDIR /var/www/html

FROM php-base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction

FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci --ignore-scripts --no-audit --no-fund
COPY --from=dependencies /var/www/html/vendor ./vendor
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php-base AS app
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
COPY --from=dependencies /var/www/html ./
COPY --from=assets /app/public/build ./public/build
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/app-entrypoint
RUN mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 80
ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
