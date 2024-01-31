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

RUN apt install  cron -y
RUN apt install  supervisor -y

RUN mkdir -p /etc/supervisor/conf.d
COPY ./docker/php/supervisor.conf /etc/supervisor.conf


WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R $USER:$USER /var/www

RUN chown -R laravel:laravel /var/www/html


CMD ["chmod", "+x", "crontab"]
COPY ./docer/cron /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN cron /etc/cron.d/crontab
CMD ["cron", "-f"]

RUN chmod +x ./docker/php/start.sh
CMD ["chmod", "+x", "./docker/php/start.sh"]
RUN chown -Rf $USER:$USER ./docker/php/start.sh
ENTRYPOINT ["./docker/php/start.sh"]

