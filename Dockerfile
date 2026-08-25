FROM php:8.2-apache

# 1. Install MySQL PHP extensions required for BuildORA
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 2. Enable Apache rewrite and headers modules
RUN a2enmod rewrite headers

# 3. Set working directory
WORKDIR /var/www/html

# 4. Copy project files
COPY . /var/www/html/

# 5. Setup dynamic port entrypoint for Railway
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 6. Set correct permissions for web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 7. Expose default port
EXPOSE 80

# 8. Start Apache with dynamic port configuration
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
