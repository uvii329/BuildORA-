FROM php:8.2-apache

# 1. Install required MySQL PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 2. Enable Apache rewrite and headers modules
RUN a2enmod rewrite headers

# 3. Configure Apache to listen on dynamic Railway $PORT (defaults to 80)
ENV PORT=80
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. Set working directory and copy application source code
WORKDIR /var/www/html
COPY . /var/www/html/

# 5. Set correct web permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

# 6. Start Apache foreground process
CMD ["apache2-foreground"]
