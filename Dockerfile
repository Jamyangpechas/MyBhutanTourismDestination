# Use official PHP image with Apache
FROM php:8.2-apache

# Install MySQL driver extensions for PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite for custom URLs (.htaccess support)
RUN a2enmod rewrite

# Set working directory inside the container
WORKDIR /var/www/html

# Copy all project files into the container
COPY . /var/www/html/

# Expose port 80
EXPOSE 80