FROM php:8.4-cli

# System packages
RUN apt-get update && apt-get install -y \
    git unzip zip curl libpq-dev libzip-dev libpng-dev libxml2-dev libonig-dev

# PHP extensions REQUIRED for Laravel 12
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    xml \
    ctype \
    fileinfo \
    zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install dependencies
RUN composer install -vvv \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# Permissions
RUN chmod +x start.sh

EXPOSE 10000

CMD ["./start.sh"]