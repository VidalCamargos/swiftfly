FROM php:8.4.1-fpm-alpine

WORKDIR /var/www

RUN apk add --no-cache \
    bash \
    curl \
    oniguruma-dev \
    libzip-dev \
    zlib-dev \
    unzip \
    zip

RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mbstring \
    zip

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
  && sed -i "/^listen = .*/c\listen = [::]:9000" /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

EXPOSE 9000

CMD ["php-fpm"]
