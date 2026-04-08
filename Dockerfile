FROM php:8.1-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    openssl-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/conf.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisord.conf

# Create nginx directories
RUN mkdir -p /run/nginx

# Expose port 80
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]