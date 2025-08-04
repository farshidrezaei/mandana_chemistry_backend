FROM dunglas/frankenphp:latest-php8.3


RUN apt update -y \
    && apt upgrade -y \
    && apt install -y  nano \
        ca-certificates \
        curl \
        gnupg \
        libicu-dev \
        default-mysql-client \
        libzip-dev \
        unzip \
        libfreetype6-dev \
        libonig-dev \
        libjpeg62-turbo-dev \
        libpng-dev supervisor \
        cron \
        default-mysql-client \
    && docker-php-ext-install zip \
        exif \
        sockets \
        bcmath \
        ctype \
        pdo \
        pdo_mysql \
        intl \
        pcntl \
        gd \
        mbstring \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-enable redis

COPY . /app
WORKDIR /app

# Setup Supervisor
RUN mkdir -p /etc/supervisor/conf.d
COPY ./supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Get latest Composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer

# Create custom php.ini setting to increase memory limit
RUN echo "memory_limit=10G" > /usr/local/etc/php/conf.d/memory-limit.ini

# Install PHP dependencies and set up Laravel
RUN composer install
RUN php artisan key:generate
RUN php artisan storage:link

ENTRYPOINT ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]
