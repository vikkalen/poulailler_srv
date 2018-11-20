FROM arm32v6/php:7.0-fpm-alpine

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

RUN apk add --update --no-cache --virtual .build-deps ${PHPIZE_DEPS} \
    && apk add --update --no-cache --virtual .build-deps rrdtool-dev \
    && pecl install rrd-2.0.1 \
    && apk del .build-deps \
    && apk add --update --no-cache rrdtool \
    && apk add --update --no-cache sqlite \
    && docker-php-ext-enable rrd

WORKDIR /var/www/html/poulailler

COPY . .
