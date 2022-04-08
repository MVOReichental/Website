FROM composer AS composer

COPY composer.* /app/

WORKDIR /app

RUN composer install --no-dev --ignore-platform-reqs && \
    rm /app/composer.json /app/composer.lock


FROM node:17 AS webpack

WORKDIR /app

COPY package.json package-lock.json /app/
RUN npm install

COPY webpack.config.js /app/
COPY src/main/resources /app/src/main/resources
RUN npm run build


FROM php:8.1-apache

ENV TZ=Europe/Berlin
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone && \
    echo "date.timezone=\"$TZ\"" > $PHP_INI_DIR/conf.d/timezone.ini

RUN savedAptMark="$(apt-mark showmanual)" && \
    apt-get update && \
    apt-get install -y --no-install-recommends libfreetype6-dev libjpeg-dev libpng-dev && \
    docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
    docker-php-ext-install -j "$(nproc)" gd pdo_mysql && \
    apt-mark auto '.*' > /dev/null && \
    apt-mark manual $savedAptMark && \
    ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
        | awk '/=>/ { print $3 }' \
        | sort -u \
        | xargs -r dpkg-query -S \
        | cut -d: -f1 \
        | sort -u \
        | xargs -rt apt-mark manual && \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false && \
    rm -rf /var/lib/apt/lists/*

RUN sed -ri -e 's!/var/www/html!/app/httpdocs!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!/app/httpdocs!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    echo "ServerTokens Prod" > /etc/apache2/conf-enabled/z-server-tokens.conf && \
    a2enmod rewrite && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    mkdir -p /app/twig-cache && \
    chown www-data: /app/twig-cache

COPY --from=composer /app/vendor /app/vendor
COPY --from=webpack /app/httpdocs/assets /app/httpdocs/assets
COPY --from=webpack /app/webpack.assets.json /app/webpack.assets.json

COPY bootstrap.php /app/
COPY bin /app/bin
COPY httpdocs /app/httpdocs
COPY src /app/src

COPY docker-entrypoint.sh /entrypoint.sh

VOLUME /app/data
WORKDIR /app

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]