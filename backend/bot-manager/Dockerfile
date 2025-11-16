FROM php:8.3-fpm-alpine

RUN apk add --no-cache git curl libzip-dev zip unzip bash docker-cli \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN php -r "file_exists('composer.phar') || copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && composer install --no-dev --no-interaction --no-progress --optimize-autoloader || true

COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["php-fpm"]


