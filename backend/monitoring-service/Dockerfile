FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    bash \
    libxml2-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    xml \
    dom \
    xmlwriter \
    mbstring \
    pcntl \
    bcmath

WORKDIR /var/www/html

# Copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock* ./

# Install composer dependencies
# Allow plugins and handle missing lock file gracefully
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist \
    || composer update --no-dev --no-interaction --no-progress --optimize-autoloader

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
