FROM php:8.2.35-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    nodejs \
    npm \
    libxml2-dev \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/cache/apk/*

RUN docker-php-ext-install mysqli pdo pdo_mysql xml session

RUN echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT" > /usr/local/etc/php/conf.d/99-webpa.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/99-webpa.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/99-webpa.ini \
    && echo "error_log = /proc/1/fd/1" >> /usr/local/etc/php/conf.d/99-webpa.ini

WORKDIR /var/www/webpa

COPY composer.json /var/www/webpa/
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . /var/www/webpa/

RUN npm install && npm run build

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/webpa \
    && chmod -R 755 /var/www/webpa

EXPOSE 9000

CMD ["php-fpm", "--allow-to-run-as-root"]
