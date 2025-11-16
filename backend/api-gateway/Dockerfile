FROM php:8.3-fpm-alpine AS backend_base

RUN apk add --no-cache git curl libzip-dev zip unzip nginx supervisor bash \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN php -r "file_exists('composer.phar') || copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && composer install --no-dev --no-interaction --no-progress --optimize-autoloader || true

COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf"]


