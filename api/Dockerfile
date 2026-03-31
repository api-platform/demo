#syntax=docker/dockerfile:1

# Adapted from https://github.com/dunglas/symfony-docker


# Versions
FROM docker.io/dunglas/frankenphp:1-php8.5 AS frankenphp_upstream


# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# Build FrankenPHP with Mercure, Vulcain and Souin (HTTP cache) modules
FROM docker.io/dunglas/frankenphp:1-builder AS builder

# Install xcaddy to build a custom FrankenPHP binary with additional Caddy modules
# hadolint ignore=DL3062
RUN go install github.com/caddyserver/xcaddy/cmd/xcaddy@latest

# hadolint ignore=DL3062
RUN CGO_ENABLED=1 \
	CGO_CFLAGS="$(php-config --includes)" \
	CGO_LDFLAGS="$(php-config --ldflags) $(php-config --libs)" \
	XCADDY_GO_BUILD_FLAGS="-tags=nobadger,nomysql,nopgx" \
	xcaddy build \
		--output /usr/local/bin/frankenphp \
		--with github.com/dunglas/frankenphp/caddy \
		--with github.com/dunglas/mercure/caddy \
		--with github.com/dunglas/vulcain/caddy \
		--with github.com/darkweak/souin/plugins/caddy \
		--with github.com/darkweak/storages/otter/caddy \
		--with github.com/darkweak/storages/redis/caddy


# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

COPY --from=builder /usr/local/bin/frankenphp /usr/local/bin/frankenphp

WORKDIR /app

# persistent / runtime deps
# hadolint ignore=DL3008
RUN apt-get update && apt-get install --no-install-recommends -y \
	acl \
	file \
	gettext \
	git \
	zip \
	&& rm -rf /var/lib/apt/lists/*

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		bcmath \
		intl \
		opcache \
		zip \
	;

###> recipes ###
###> doctrine/doctrine-bundle ###
RUN set -eux; \
	install-php-extensions pdo_pgsql
###< doctrine/doctrine-bundle ###
###< recipes ###

COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/frankenphp/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile" ]

# Dev FrankenPHP image
FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev
ENV XDEBUG_MODE=off
ENV FRANKENPHP_WORKER_CONFIG=watch
VOLUME /app/var/

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
	install-php-extensions \
		xdebug \
	;

COPY --link frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--watch" ]

# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --link . ./
RUN rm -Rf frankenphp/

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync; \
	cp public/assets/react.production.min.js public/bundles/apiplatform/react/react.production.min.js;
