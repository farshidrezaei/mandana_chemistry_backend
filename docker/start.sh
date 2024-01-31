#!/bin/sh
RUN php artisan key:generate

service cron restart

/usr/bin/supervisord -c /etc/supervisor.conf -n &



