FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite headers

ENV PORT=80
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
