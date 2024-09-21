#!/bin/sh

cron -f &

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf -n &

# php artisan reverb:start

php artisan octane:frankenphp  --host=0.0.0.0



