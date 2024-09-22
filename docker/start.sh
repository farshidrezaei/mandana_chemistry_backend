#!/bin/sh

cron -f &

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf -n &

php artisan octane:start  --host=0.0.0.0



