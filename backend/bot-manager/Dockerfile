FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
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

# Set permissions
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache

# Copy nginx config (create if doesn't exist)
RUN mkdir -p docker
COPY --chown=www-data:www-data <<'EOF' docker/nginx.conf
worker_processes auto;

events {
    worker_connections 1024;
}

http {
    sendfile on;
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    server {
        listen 80;
        server_name _;

        root /var/www/html/public;

        index index.php;

        location / {
            try_files $uri /index.php?$query_string;
        }

        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
}
EOF

# Copy supervisor config
COPY --chown=www-data:www-data <<'EOF' docker/supervisord.conf
[supervisord]
nodaemon=true

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
EOF

RUN cp docker/nginx.conf /etc/nginx/nginx.conf && \
    cp docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
