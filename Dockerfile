FROM ghcr.io/msalehipro/laravel-octane:latest

ARG USER
ARG GROUP
ARG UID
ARG GID

RUN groupadd -g 1000 laravel \
    && useradd -m -g laravel -u 1000 -s /bin/sh laravel \
    && chown -R laravel:laravel /var/www \
    && usermod -u 1000 laravel \
    && groupmod -g 1000 laravel

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R laravel:laravel /var/www/html

RUN composer i

RUN php artisan key:generate
