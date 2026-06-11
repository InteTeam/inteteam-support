FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git curl libpng-dev oniguruma-dev libxml2-dev \
    zip unzip libzip-dev autoconf g++ make linux-headers \
    postgresql-dev

RUN docker-php-ext-install \
    pdo_pgsql pgsql \
    mbstring exif pcntl bcmath gd zip opcache

RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN mkdir -p /var/log/php-fpm && chmod 777 /var/log/php-fpm

RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www

RUN chown -R www:www /var/www

USER www

EXPOSE 9000
CMD ["php-fpm"]
