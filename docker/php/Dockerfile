FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    libpq-dev \
    libzip-dev \
    unzip

RUN docker-php-ext-install pdo pdo_pgsql zip
RUN apt-get update && \
    apt-get install -y net-tools && \
    rm -rf /var/lib/apt/lists/* \
    && pecl install xdebug  && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer

#permissions
RUN groupadd -g 1000 mygroup && useradd -u 1000 -g mygroup -m myuser
WORKDIR /var/www/chain-command
RUN chown -R myuser:mygroup /var/www/chain-command
USER myuser

CMD ["php-fpm"]