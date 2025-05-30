FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
        cron \
        git \
        libzip-dev \
        zip \
        unzip \
        # - for pdo_pgsql (PostgreSQL PDO extension)
        libpq-dev \
        # - for SOAP (Required for working with SOAP APIs)
        libxml2-dev \
        # for GD (Image processing library)
        libwebp-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libfreetype6-dev \
        # for intl (Internationalization support)
        libicu-dev \
    && docker-php-ext-configure gd \
            --with-webp \
            --with-jpeg \
            --with-freetype \
    && docker-php-ext-configure intl \
	&& docker-php-ext-install \
	     zip \
	     bcmath \
	     gd \
	     intl \
	     pdo_pgsql \
	     soap \
	&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# for supervisor & redis
# RUN apt-get install -y supervisor && pecl install -o -f redis && rm -rf /tmp/pear && docker-php-ext-enable redis

WORKDIR /var/www/html
# for cron
COPY cron-file /etc/cron.d/cron-file
COPY run-daemons.sh /run-daemons.sh

ENTRYPOINT /bin/bash /run-daemons.sh
