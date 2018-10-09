#!/usr/bin/env bash

cd "$(dirname "$0")"/../client

yarn && REACT_APP_ADMIN_HOST_HTTPS=https://${ADMIN_BUCKET} REACT_APP_ADMIN_HOST_HTTP=http://${ADMIN_BUCKET} \
    REACT_APP_API_CACHED_HOST_HTTPS=https://${API_ENTRYPOINT} \
    REACT_APP_API_CACHED_HOST_HTTP=http://${API_ENTRYPOINT} \
    yarn build --environment=prod;
