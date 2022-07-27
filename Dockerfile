FROM composer:1.9.1 as composer-deps
WORKDIR /app
COPY ./composer.json .
COPY ./composer.lock .
ARG BUILD_ENV=prod
RUN composer install -n --no-plugins --no-scripts --ignore-platform-reqs \
   $(if [ "${BUILD_ENV}" = "prod" ]; then \
      echo "--no-dev"; \
   fi)

FROM php:7.4-fpm-alpine

RUN apk update && \
    apk add nginx curl && \
    mkdir -p /run/nginx

RUN apk add --no-cache \
            postgresql-dev \
            libzip-dev \
            yaml-dev
RUN apk add --no-cache --virtual .build-deps \
            zlib-dev \
            $PHPIZE_DEPS \
            autoconf \
            gcc

RUN set -e; docker-php-ext-install \
       zip \
       pgsql \
       pdo_pgsql && \
    pecl install -o -f redis && \
    pecl install -o -f yaml && \
    docker-php-ext-enable redis && \
    docker-php-ext-enable yaml && \
    docker-php-ext-enable opcache && \
    rm -rf /tmp/pear

RUN apk add zip

RUN apk del .build-deps

ARG BUILD_ENV=prod
ENV APP_ENV=${BUILD_ENV}

WORKDIR /var/www/html
COPY . .
COPY --from=composer-deps /app/vendor ./vendor
COPY --from=composer-deps /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload $(if [ "${BUILD_ENV}" = "prod" ]; then \
      echo "--no-dev -a -o"; \
    fi)

COPY docker/docker-entrypoint.sh /
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini-production $PHP_INI_DIR/php.ini-production
COPY docker/php.ini-development $PHP_INI_DIR/php.ini-development
COPY docker/php.ini-production $PHP_INI_DIR/conf.d/php.ini-development
RUN if [ "${BUILD_ENV}" = "prod" ]; then \
      cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/conf.d/php.ini; \
    else \
      cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/conf.d/php.ini; \
    fi;

COPY ./docker/gost.so /usr/lib/x86_64-linux-gnu/engines-1.1/gost.so
COPY ./docker/openssl.cnf /etc/ssl/openssl.cnf

EXPOSE 80
ENTRYPOINT ["sh", "/docker-entrypoint.sh"]
HEALTHCHECK --interval=5s --start-period=5s --retries=5 CMD pgrep nginx && pgrep php-fpm || exit 1
