# Use an official PHP 8.2 Apache image as the base
FROM php:8.2-apache

# Install necessary packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip intl pdo pdo_pgsql pgsql


# Enable Apache mod_rewrite (required by Moodle)
RUN a2enmod rewrite

# Set working directory inside the container
WORKDIR /var/www/html

# Copy all project files into the container
COPY . /var/www/html/

# Fix file permissions
RUN chown -R www-data:www-data /var/www/html

# Create moodledata directory with proper permissions
RUN mkdir -p /tmp/moodledata && chmod -R 777 /tmp/moodledata


# Expose the default Apache port
EXPOSE 80

# Set custom PHP configuration values
RUN echo "max_input_vars = 5000" > /usr/local/etc/php/conf.d/custom.ini

