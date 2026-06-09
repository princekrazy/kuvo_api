FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip curl libpq-dev libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Make start script executable
RUN chmod +x start.sh

# Expose port (Render uses dynamic PORT)
EXPOSE 10000

# Start app
CMD ["./start.sh"]