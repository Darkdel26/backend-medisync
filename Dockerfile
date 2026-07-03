FROM php:8.2-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev

RUN docker-php-ext-install pdo pdo_mysql zip mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

# IMPORTANT: éviter crash Laravel pendant build
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

RUN php artisan config:clear || true
RUN php artisan cache:clear || true

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
