FROM php:8.3-fpm-alpine

# Installer les outils nécessaires pour MongoDB et la compilation
RUN apk --no-cache add \
    libcurl \
    libssl3 \
    pkgconfig \
    autoconf \
    gcc \
    make \
    build-base \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Installer les extensions PHP supplémentaires
RUN docker-php-ext-install mysqli pdo pdo_mysql