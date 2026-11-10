# Zazu EMP — disposable Render preview (SQLite data is ephemeral)
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM composer:2 AS composerbin
FROM php:8.4-cli-bookworm AS vendor
RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev libxml2-dev libzip-dev libsqlite3-dev pkg-config unzip \
    && docker-php-ext-install -j"$(nproc)" mbstring pdo_sqlite xml zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composerbin /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts

FROM php:8.4-apache-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev libxml2-dev libzip-dev libsqlite3-dev pkg-config \
    && docker-php-ext-install -j"$(nproc)" mbstring pdo_sqlite xml zip opcache \
    && a2enmod rewrite headers expires \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -ri 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache
COPY docker/render-entrypoint.sh /usr/local/bin/render-entrypoint
RUN chmod +x /usr/local/bin/render-entrypoint
EXPOSE 10000
ENTRYPOINT ["render-entrypoint"]
