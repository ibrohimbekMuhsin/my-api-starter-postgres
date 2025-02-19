#!/bin/bash
# uncommit for cron
# cron -f &
# uncomment if you use workers
#service supervisor start
#supervisorctl reread
#supervisorctl update
#supervisorctl start messenger-consume:*
docker-php-entrypoint php-fpm
