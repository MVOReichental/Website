FROM composer AS composer

COPY composer.* /app/

WORKDIR /app

RUN composer install --no-dev --ignore-platform-reqs && \
    rm /app/composer.json /app/composer.lock


FROM node:17 AS npm

COPY httpdocs/package.json httpdocs/package-lock.json /app/

WORKDIR /app

RUN npm install


FROM ghcr.io/programie/php-docker

ENV WEB_ROOT=/app/httpdocs
ENV TZ=Europe/Berlin

RUN install-php 8.1 dom gd mbstring pdo-mysql && \
    a2enmod rewrite && \
    mkdir -p /app/twig-cache && \
    chown www-data:www-data /app/twig-cache

COPY --from=composer /app/vendor /app/vendor
COPY --from=npm /app/node_modules /app/httpdocs/node_modules

COPY bootstrap.php /app/
COPY bin /app/bin
COPY httpdocs /app/httpdocs
COPY src /app/src

COPY docker-entrypoint.sh /entrypoint.sh

VOLUME /app/data
WORKDIR /app

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
