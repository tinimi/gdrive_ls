FROM php:8.2-apache

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y unzip && mkdir /.composer && chmod 777 /.composer
