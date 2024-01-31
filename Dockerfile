FROM ghcr.io/msalehipro/laravel-octane:latest

RUN groupadd -g 1000 laravel \
    && useradd -m -g laravel -u 1000 -s /bin/sh laravel \
    && chown -R laravel:laravel /var/www \
    && usermod -u 1000 laravel \
    && groupmod -g 1000 laravel

RUN apt install cron -y

RUN mkdir -p /etc/supervisor/conf.d
COPY docker/supervisor.conf /etc/supervisor.conf

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R laravel:laravel /var/www/html

RUN composer i

RUN php artisan key:generate
RUN php artisan storage:link

CMD ["chmod", "+x", "crontab"]
COPY docker/cron /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN cron /etc/cron.d/crontab
CMD ["cron", "-f"]
ENTRYPOINT ["service", "cron", "restart"]

ENTRYPOINT ["/usr/bin/supervisord", "-c", "/etc/supervisor.conf", "-n", "&"]
