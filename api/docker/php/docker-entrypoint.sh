#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ] || { [ "$1" = 'php' ] && [ "$2" = 'bin/console' ]; }; then
	mkdir -p var/cache var/log
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	if [ "$APP_ENV" != 'prod' ] && [ ! -f config/jwt/private.pem ]; then
		jwt_passphrase=$(grep '^JWT_PASSPHRASE=' .env | cut -f 2 -d '=')
		if ! echo "$jwt_passphrase" | openssl pkey -in config/jwt/private.pem -passin stdin -noout > /dev/null 2>&1; then
			echo "Generating public / private keys for JWT"
			mkdir -p config/jwt
			echo "$jwt_passphrase" | openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
			echo "$jwt_passphrase" | openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout
			setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
			setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
		fi
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-interaction
	fi

	echo "Waiting for db to be ready..."
	until bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
		sleep 1
	done

	echo "Running migrations"
	bin/console doctrine:migrations:migrate --no-interaction

	if [ "$APP_ENV" != 'prod' ]; then
		echo "Load fixtures"
		bin/console hautelook:fixtures:load --no-interaction
	fi
fi

exec docker-php-entrypoint "$@"
