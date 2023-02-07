FROM arm32v6/php:8.0-fpm-alpine

ENV docker 1
RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /bin/composer.phar

ENV PHPIZE_DEPS \
    git \
    file \
    re2c \
    autoconf \
    make \
    zlib \
    zlib-dev \
    g++

RUN apk add --update --no-cache --virtual .build-deps ${PHPIZE_DEPS} rrdtool-dev \
    && pecl install rrd-2.0.3 \
    && apk del .build-deps \
    && apk add --update --no-cache ttf-dejavu rrdtool \
    && apk add --update --no-cache sqlite \
    && apk add --update --no-cache supervisor \
    && docker-php-ext-enable rrd \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl


ENV PHP_PM_MODE="dynamic" \
    PHP_PM_MAX_CHILDREN="5" \
    PHP_PM_START_SERVERS="2" \
    PHP_PM_MIN_SPARE_SERVERS="1" \
    PHP_PM_MAX_SPARE_SERVERS="3" \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS="1" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="10000" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="48" \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE="10"

RUN mkdir -p /var/run/supervisor && chown www-data:www-data /var/run/supervisor
COPY supervisord.conf /etc/supervisord.conf

COPY php.conf /usr/local/etc/php-fpm.d/www.conf
COPY php-opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

WORKDIR /var/www/html/poulailler

COPY composer.* ./
RUN composer.phar install --no-dev

COPY . .

