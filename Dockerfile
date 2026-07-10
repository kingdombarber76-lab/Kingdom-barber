# Stage 1: compile Vite assets
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# Stage 2: Laravel application
FROM php:8.4-apache AS app

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --from=frontend /app/public/build ./public/build
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Sevalla injects PORT at runtime; Apache must listen on that port.
RUN printf '%s\n' \
    '#!/bin/sh' \
    'set -e' \
    'port="${PORT:-8080}"' \
    'sed -i "s/Listen 80/Listen ${port}/" /etc/apache2/ports.conf' \
    'exec apache2-foreground' \
    > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
