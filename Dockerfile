FROM php:8.2-apache

# Install MySQL extension for PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Disable conflicting Apache MPM modules so Railway won't crash
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

# Copy project files into Apache web server root
COPY . /var/www/html/

# Enable Apache rewrite module if your app uses routing
RUN a2enmod rewrite

EXPOSE 80