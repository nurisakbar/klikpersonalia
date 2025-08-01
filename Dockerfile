# Payroll Management System - Dockerfile
# Multi-stage build for production

# Stage 1: Composer dependencies
FROM composer:2.6 as composer

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Stage 2: Node.js dependencies and build
FROM node:18-alpine as node

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci --only=production

# Copy source files
COPY . .

# Build assets
RUN npm run build

# Stage 3: Production image
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/payroll-system

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    supervisor \
    nginx \
    mysql-client \
    redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && docker-php-ext-enable opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Copy built assets from node stage
COPY --from=node /app/public/build ./public/build

# Copy vendor from composer stage
COPY --from=composer /app/vendor ./vendor

# Set permissions
RUN chown -R www-data:www-data /var/www/payroll-system \
    && chmod -R 755 /var/www/payroll-system \
    && chmod -R 775 /var/www/payroll-system/storage \
    && chmod -R 775 /var/www/payroll-system/bootstrap/cache

# Create necessary directories
RUN mkdir -p /var/log/payroll-system \
    && mkdir -p /var/backups/payroll-system \
    && chown -R www-data:www-data /var/log/payroll-system \
    && chown -R www-data:www-data /var/backups/payroll-system

# Copy configuration files
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Generate application key
RUN php artisan key:generate --no-interaction

# Optimize for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Create health check file
RUN echo "healthy" > /var/www/payroll-system/public/health

# Expose port
EXPOSE 9000

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"] 