FROM php:8.2-apache

# 1. Install MySQL PHP extensions required for BuildORA
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 2. Enable Apache rewrite and headers modules
RUN a2enmod rewrite headers

# 3. Ensure only mpm_prefork is loaded and disable others to prevent AH00534
RUN a2dismod -f mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

# 4. Configure Apache to dynamically listen on Railway's $PORT (defaults to 80)
ENV PORT=80
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 5. Set working directory and copy application files
WORKDIR /var/www/html
COPY . /var/www/html/

# 6. Set correct permissions for web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

# 7. Start Apache using the official standard foreground command
CMD ["apache2-foreground"]
