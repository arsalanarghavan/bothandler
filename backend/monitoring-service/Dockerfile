FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    wget \
    libzip-dev \
    zip \
    unzip \
    bash \
    libxml2-dev \
    oniguruma-dev \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
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

# Copy composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock* ./

# Install dependencies without scripts
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    || composer update --no-dev --no-interaction --no-progress --no-scripts

# Copy application code
COPY . .

# Run composer autoload
RUN composer dump-autoload --optimize --no-dev

# Create storage directories if they don't exist
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views

# Set permissions
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy supervisor config
COPY docker/supervisord.conf /etc/supervisord.conf

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
