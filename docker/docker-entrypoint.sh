#!/bin/sh

set -e

# first arg is `-f` or `--some-option`
if [ -z "$1" -o "${1#-}" != "$1" ]; then
      (nginx -t &&
      php bin/console cache:warmup &&
      nginx) || exit 1
      set -- php-fpm "$@"
fi

exec "$@"

