#!/bin/sh
set -e

if [ ! -f cert.key ]; then
    cp /usr/local/share/cert.key ./
fi
if [ ! -f cert.crt ]; then
    cp /usr/local/share/cert.crt ./
fi

exec "$@"
