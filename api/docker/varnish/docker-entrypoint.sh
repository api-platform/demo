#!/bin/sh
set -e

envsubst < /usr/local/etc/varnish/default.tmpl > /etc/varnish/default.vcl

exec "$@"
