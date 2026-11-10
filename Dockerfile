# Zazu EMP — temporary Render staging image
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts

FROM php:8.4-apache-bookworm
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libpq-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mbstring opcache pcntl pdo_pgsql zip \
    && a2enmod rewrite headers expires \
    && sed -ri -e 's!/var/www/html!\${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!\${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
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
