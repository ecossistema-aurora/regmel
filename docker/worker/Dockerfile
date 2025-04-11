FROM php:8.4-fpm as base

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    apt-utils \
    libonig-dev \
    libpq-dev \
    libssl-dev \
    pkg-config \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_pgsql zip mbstring exif \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

FROM base as worker

RUN apt-get update && apt-get install -y supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY docker/worker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/log/supervisor /var/www

USER www-data

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf", "-n"]