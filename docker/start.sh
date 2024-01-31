#!/bin/sh
RUN composer i

RUN php artisan key:generate

php artisan storage:link

service cron restart

/usr/bin/supervisord -c /etc/supervisor.conf -n &



