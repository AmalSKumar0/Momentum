FROM php:8.2-apache

# Install and enable mysqli extension required for MySQL connection
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy application source code to Apache root directory
COPY . /var/www/html/

# Ensure proper permissions for Apache web server
RUN chown -R www-data:www-data /var/www/html
