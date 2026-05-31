FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Set working directory
WORKDIR /var/www/html
