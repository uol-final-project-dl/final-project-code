#FROM php:7.4.30-fpm-alpine3.15
FROM php:8.2-fpm-alpine3.18

RUN apk update && apk add --no-cache \
    zip \
    unzip \
    dos2unix \
    supervisor \
    libpng-dev \
    libzip-dev \
    freetype-dev \
    $PHPIZE_DEPS \
    libjpeg-turbo-dev \
    lz4-dev \
    mysql-client \
    imagemagick \
    imagemagick-dev \
    postgresql-dev

RUN apk add nano

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo gd pcntl bcmath mysqli pdo_mysql pgsql pdo_pgsql

RUN pecl install zip && docker-php-ext-enable zip \
    && pecl install igbinary && docker-php-ext-enable igbinary \
    && pecl install msgpack && docker-php-ext-enable msgpack \
    && yes | pecl install redis && docker-php-ext-enable redis

RUN pecl install imagick && docker-php-ext-enable imagick

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html/bootstrap
RUN chown -R www-data:www-data /var/www/html/storage

# SETUP PHP-FPM CONFIG SETTINGS (max_children / max_requests)
RUN echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.max_children = 15' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.max_requests = 2500' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.start_servers  = 5' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.min_spare_servers   = 5' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.max_spare_servers   = 10' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.process_idle_timeout  = 30s' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
