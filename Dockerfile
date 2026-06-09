FROM php:8.3-cli

# System packages
RUN apt-get update && apt-get install -y \
    git unzip zip curl libpq-dev libzip-dev libpng-dev libxml2-dev

# PHP extensions REQUIRED for Laravel 12 + Reverb + APIs
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    xml \
    ctype \
    fileinfo \
    zip \
    sockets

# Enable curl (already built but ensure dependency exists)
RUN apt-get install -y libcurl4-openssl-dev

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install dependencies safely
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# Fix permissions
RUN chmod +x start.sh

EXPOSE 10000

CMD ["./start.sh"]