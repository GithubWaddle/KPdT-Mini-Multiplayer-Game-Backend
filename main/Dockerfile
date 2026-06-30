# Build grpc_php_plugin

FROM debian:bookworm AS grpc-builder

RUN apt-get update && apt-get install -y \
    git \
    cmake \
    build-essential \
    protobuf-compiler \
    libprotobuf-dev \
    && rm -rf /var/lib/apt/lists/*

RUN git clone --recurse-submodules -b v1.81.1 https://github.com/grpc/grpc.git

WORKDIR /grpc/cmake/build

RUN cmake ../.. \
    && make grpc_php_plugin

# Laravel PHP Image

FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    zlib1g-dev \
    libssl-dev \
    protobuf-compiler \
    libprotobuf-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

ADD --chmod=0755 \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/

RUN install-php-extensions redis
RUN install-php-extensions grpc
RUN install-php-extensions protobuf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the glibc-built grpc plugin
COPY --from=grpc-builder \
    /grpc/cmake/build/grpc_php_plugin \
    /usr/local/bin/grpc_php_plugin

WORKDIR /var/www

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY . .

RUN composer install --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
