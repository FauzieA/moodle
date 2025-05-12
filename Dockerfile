# Use an official PHP 8.2 Apache image as the base
FROM php:8.2-apache

# Install necessary packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_pgsql pgsql

# Enable Apache mod_rewrite (required by Moodle)
RUN a2enmod rewrite

# Set working directory inside the container
WORKDIR /var/www/html

# Copy all project files into the container
COPY . /var/www/html/

# Fix file permissions
RUN chown -R www-data:www-data /var/www/html

# Expose the default Apache port
EXPOSE 80
