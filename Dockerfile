FROM php:8.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_mysql

COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf