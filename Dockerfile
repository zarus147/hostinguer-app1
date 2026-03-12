FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN sed -i 's!/var/www/html!/var/www/html/public_html!g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's!/var/www/!/var/www/html/public_html!g' /etc/apache2/apache2.conf