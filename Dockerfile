# PHP-FPM and Composer image for Laravel
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
        bash git curl libpng libpng-dev libjpeg-turbo libjpeg-turbo-dev freetype freetype-dev oniguruma-dev icu-dev libzip-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath intl zip gd

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Create user for Laravel
RUN addgroup -g 1000 laravel && adduser -u 1000 -G laravel -s /bin/sh -D laravel

WORKDIR /var/www/html

USER laravel

CMD ["php-fpm"]
