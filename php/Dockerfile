FROM php:8.2-fpm

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install system packages needed for zip and other PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

WORKDIR /var/www/html
