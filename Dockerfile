FROM php:8.4-fpm

# 1. Установка системных зависимостей
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

# 4. Копирование всех файлов (после WORKDIR!)
COPY . .

# 5. Права доступа к storage и bootstrap/cache (важно для production!)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# 6. Установка node-пакетов и сборка фронта (vite/prod)
RUN npm install && npm run build && npm install -g laravel-echo-server

# 7. Установка php-зависимостей через Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 8. Storage link (не ошибка, если уже есть)
RUN php artisan storage:link || true

# 9. Миграции (для dev/CI/CD — production обычно мигрируется отдельно, но для Railway удобно)
RUN cp .env.example .env && php artisan key:generate --force && php artisan migrate --force || true

# 10. Финальные права (на всякий случай)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# 11. Открываем порт
EXPOSE 8080

# 12. Запуск Laravel встроенного сервера (требуется для Railway)
CMD php artisan serve --host=0.0.0.0 --port=8080
