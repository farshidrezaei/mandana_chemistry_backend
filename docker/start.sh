#!/bin/sh

service cron restart

/usr/bin/supervisord -c /etc/supervisor.conf -n &



