FROM php:8.2-fpm as base

RUN apt-get update && apt-get install -y \
 git \
 curl \
 libpng-dev \
 libonig-dev \
 libxml2-dev \
 zip \
 unzip \
 librabbitmq-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN pecl install amqp && docker-php-ext-enable amqp
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

FROM base as order-service
WORKDIR /var/www/order-service
RUN chown -R www-data:www-data /var/www/order-service
USER www-data

FROM base as product-service
WORKDIR /var/www/product-service
RUN chown -R www-data:www-data /var/www/product-service
USER www-data
