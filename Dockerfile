FROM php:8.2-apache

# Install PDO MySQL extension for DB connection
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache Mod_Rewrite for clean URLs (.htaccess)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Adjust permissions for Apache
RUN chown -R www-data:www-data /var/www/html