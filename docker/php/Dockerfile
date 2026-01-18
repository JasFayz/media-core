FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl unzip \
    libpq-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql zip gd \
    && pecl install imagick redis \
    && docker-php-ext-enable imagick redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

ARG WWWUSER=1000
ARG WWWGROUP=1000
RUN groupadd -g ${WWWGROUP} app \
    && useradd -u ${WWWUSER} -g app -m app
