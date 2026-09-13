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

# UID/GID must match the deploying SSH user on the target host (e.g. upanel=1002
# on the Panel-managed hosts) since the repo is bind-mounted at runtime — files
# on disk are owned by that user, not by whatever UID this image bakes in.
# Defaults to 1000 for local dev. See inte-playbook's UID/GID deploy gotcha
# (cms.inte.team, 2026-09-08).
ARG UID=1000
ARG GID=1000

RUN addgroup -g ${GID} www && adduser -u ${UID} -G www -s /bin/sh -D www

RUN chown -R www:www /var/www

USER www

EXPOSE 9000
CMD ["php-fpm"]
