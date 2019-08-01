FROM composer AS composer

COPY bin /app/bin
COPY httpdocs /app/httpdocs
COPY src /app/src
COPY bootstrap.php composer.json composer.lock /app/

WORKDIR /app

RUN composer install --no-dev && \
    rm /app/composer.json /app/composer.lock


FROM node:current AS npm

COPY httpdocs/package.json httpdocs/package-lock.json /app/

WORKDIR /app

RUN npm install


FROM php:7.3-apache

RUN sed -ri -e 's!/var/www/html!/app/httpdocs!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!/app/httpdocs!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    echo "ServerTokens Prod" > /etc/apache2/conf-enabled/z-server-tokens.conf && \
    a2enmod rewrite && \
    docker-php-ext-install pdo_mysql && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer /app /app
COPY --from=npm /app/node_modules /app/httpdocs/node_modules

VOLUME /app/data
WORKDIR /app