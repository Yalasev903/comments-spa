FROM php:8.4-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    git curl zip unzip vim \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev \
    mariadb-client \
    nodejs npm \
    netcat-openbsd \
    redis-tools \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && git config --global --add safe.directory /var/www

# Установка composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

RUN npm install && npm run build && npm install -g laravel-echo-server

EXPOSE 8000

CMD ["php-fpm"]
