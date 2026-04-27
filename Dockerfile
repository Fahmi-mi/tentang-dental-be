FROM php:8.3-cli-alpine

# System dependencies + PHP extensions needed by Laravel and project packages
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    icu-libs \
    libjpeg-turbo \
    libpng \
    freetype \
    libwebp \
    libzip \
    sqlite-libs \
    oniguruma \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    sqlite-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    xml \
    zip \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first for better Docker cache reuse
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    --no-scripts

# Copy application files
COPY . .

# Rebuild autoload with full source available
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-interaction --no-scripts

# Ensure writable directories exist
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=10000

EXPOSE 10000

USER www-data

CMD ["sh", "-c", "php artisan storage:link >/dev/null 2>&1 || true; php artisan serve --host=0.0.0.0 --port=${PORT}"]
