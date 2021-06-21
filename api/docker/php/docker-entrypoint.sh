#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

	mkdir -p var/cache var/log
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	if [ "$APP_ENV" != 'prod' ]; then
		if [ ! -f config/jwt/private.pem ]; then
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

		composer install --prefer-dist --no-progress --no-interaction
	fi

	if grep -q DATABASE_URL= .env; then
		echo "Waiting for database to be ready..."
		ATTEMPTS_LEFT_TO_REACH_DATABASE=60
		until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
			if [ $? -eq 255 ]; then
				# If the Doctrine command exits with 255, an unrecoverable error occurred
				ATTEMPTS_LEFT_TO_REACH_DATABASE=0
				break
			fi
			sleep 1
			ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
			echo "Still waiting for database to be ready... Or maybe the database is not reachable. $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left."
		done

		if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
			echo "The database is not up or not reachable:"
			echo "$DATABASE_ERROR"
			exit 1
		else
			echo "The database is now ready and reachable"
		fi

		if ls -A migrations/*.php >/dev/null 2>&1; then
			echo "Execute migrations"
			bin/console doctrine:migrations:migrate --no-interaction
		fi

		if [ "$APP_ENV" != 'prod' ]; then
			echo "Load fixtures"
			bin/console hautelook:fixtures:load --no-interaction
		fi
	fi
fi

exec docker-php-entrypoint "$@"
