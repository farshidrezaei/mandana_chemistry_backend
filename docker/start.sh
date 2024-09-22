#!/bin/sh

cron -f &

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf -n &

su -c "php artisan octane:start --host=0.0.0.0" laravel
