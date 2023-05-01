FROM node:18 AS webpack

WORKDIR /app

COPY package.json package-lock.json /app/
RUN npm install

COPY webpack.config.js tsconfig.json /app/
COPY assets /app/assets
RUN npm run build


FROM composer AS composer

COPY composer.* symfony.lock /app/

WORKDIR /app

RUN composer install --no-dev --ignore-platform-reqs && \
    rm /app/composer.json /app/composer.lock


FROM php:8.2-apache

RUN sed -ri -e 's!/var/www/html!/app/public!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!/app/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    echo "ServerTokens Prod" > /etc/apache2/conf-enabled/z-server-tokens.conf && \
    a2enmod rewrite && \
    apt-get -y update && \
    docker-php-ext-install pdo_mysql && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    mkdir -p /app/var && \
    chown www-data: /app/var

ENV PATH="${PATH}:/app/bin"
WORKDIR /app

COPY --from=composer /app/vendor /app/vendor
COPY --from=webpack /app/public/assets /app/public/assets

COPY bin /app/bin
COPY config /app/config
COPY data/models /app/models
COPY data/default-profile-picture.svg /app/
COPY public /app/public
COPY src /app/src
COPY templates /app/templates
COPY .env bootstrap.php database.sql docker-entrypoint.sh /app/