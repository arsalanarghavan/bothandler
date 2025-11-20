FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git curl libzip-dev zip unzip bash \
    libxml2-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql zip xml dom xmlwriter mbstring

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
