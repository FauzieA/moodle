# Use an official PHP runtime as a parent image
FROM php:7.4-apache

# Install necessary extensions for Moodle
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev libzip-dev unzip git

# Enable Apache modules and PHP extensions needed for Moodle
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/freetype2 --with-jpeg-dir=/usr/include
RUN docker-php-ext-install gd zip pdo pdo_mysql

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Set the working directory in the container
WORKDIR /var/www/html

# Copy Moodle source code into the container
COPY . /var/www/html/

# Set correct file permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (used by Apache)
EXPOSE 80
