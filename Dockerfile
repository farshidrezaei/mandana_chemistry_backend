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
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
RUN chmod 0644 /etc/supervisor/conf.d/supervisor.conf

# Get latest Composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies and set up Laravel
RUN composer install
RUN php artisan key:generate
RUN php artisan storage:link

# Setup cron job
COPY docker/cron /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN crontab /etc/cron.d/crontab


RUN chmod +x /app/docker/start.sh

RUN chmod -R 775 /app/storage

CMD ["chmod", "+x", "./docker/start.sh"]
# Use ENTRYPOINT for the startup script
ENTRYPOINT ["/app/docker/start.sh"]
