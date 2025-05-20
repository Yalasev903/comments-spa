FROM php:8.4-fpm

# 1. Установка зависимостей
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

# 2. Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3. Рабочая директория
WORKDIR /var/www

# 4. Копирование всех файлов
COPY . .

# 5. Права доступа
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# 6. node-пакеты, vite build, echo-server
RUN npm install && npm run build && npm install -g laravel-echo-server

# 7. composer install
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 8. Финальные права (на всякий случай)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# 9. Открываем порт
EXPOSE 8080

# 10. Запуск artisan-команд и сервера Laravel только на старте (CMD)
CMD php artisan config:clear \
 && php artisan cache:clear \
 && php artisan storage:link || true \
 && php artisan migrate --force || true \
 && php artisan serve --host=0.0.0.0 --port=8080

