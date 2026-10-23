FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql mbstring exif pcntl bcmath gd xml

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/webpa

# Copy application code
COPY . .

# Composer cache directory is mounted as a volume at runtime
# Vendor directory is installed at runtime via docker-compose command
RUN rm -rf vendor composer.lock

# Set proper permissions
RUN chown -R www-data:www-data /var/www/webpa \
    && chmod -R 755 /var/www/webpa \
    && mkdir -p /var/www/webpa/vendor \
    && chown -R www-data:www-data /var/www/webpa/vendor

EXPOSE 9000

CMD ["php-fpm"]
