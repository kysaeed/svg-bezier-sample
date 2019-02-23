#!/bin/sh -e

cd `dirname $0`

if [ "$REFRESH" == 'true' ]; then
  cd kuaru_main
  php artisan migrate:fresh && APP_ENV=testing php artisan db:seed
  cd -
fi

[ -z "$HOST" ] && HOST=localhost
[ -z "$PORT" ] && PORT=8989

php -S $HOST:$PORT -t .
