FROM php:8.2-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git curl zip unzip vim \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev \
    mariadb-client \
    nodejs npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www

# Копируем весь проект
COPY . .

# Установка прав
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Установка JS-зависимостей и билд фронта
RUN npm install && npm run build

EXPOSE 8000

# По умолчанию запускается php-fpm
CMD ["php-fpm"]
