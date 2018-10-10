#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
	mkdir -p var/cache var/log
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-suggest --no-interaction
		>&2 echo "Waiting for Postgres to be ready..."
		until bin/console doctrine:query:sql "SELECT 1" --quiet; do
			sleep 1
		done
		bin/console doctrine:schema:update --force --no-interaction
	else
	    # This is a hack for use hautelook fixtures in prod, just for the needs of the demo.
        APP_ENV=dev composer install --prefer-dist --no-progress --no-suggest --no-interaction
        >&2 echo "Waiting for Postgres to be ready..."
        until bin/console doctrine:query:sql "SELECT 1" --quiet; do
            sleep 1
        done
        bin/console doctrine:schema:update --force --no-interaction
        bin/console hautelook:fixtures:load -n
        composer install --classmap-authoritative --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-suggest
        composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-suggest
        composer clear-cache
    fi
fi

exec docker-php-entrypoint "$@"
