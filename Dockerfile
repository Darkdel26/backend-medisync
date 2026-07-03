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

# 🔥 FIX CRITIQUE Laravel
RUN mkdir -p bootstrap/cache storage/framework storage/framework/cache storage/framework/sessions storage/framework/views

RUN chmod -R 777 bootstrap storage

# installer sans crash strict
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

# exécuter artisan APRÈS préparation
RUN php artisan package:discover || true

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
