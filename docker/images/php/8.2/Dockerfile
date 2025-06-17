FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    git \
    libzip-dev \
    libpq-dev \
    netcat-openbsd \
    && docker-php-ext-install zip pdo pdo_mysql pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY ./docker/images/php/8.2/start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
