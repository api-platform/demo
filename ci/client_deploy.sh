#!/usr/bin/env bash

cd "$(dirname "$0")"/..

# Client build deployment to bucket
gsutil rsync -R client/build gs://"${CLIENT_BUCKET}"
gsutil web set -m index.html gs://"${CLIENT_BUCKET}"
gsutil iam ch allUsers:objectViewer gs://"${CLIENT_BUCKET}"

yarn install --pure-lockfile
REACT_APP_ADMIN_HOST_HTTPS=https://${ADMIN_BUCKET} \
    REACT_APP_ADMIN_HOST_HTTP=http://${ADMIN_BUCKET} \
    REACT_APP_API_CACHED_HOST_HTTPS=https://${API_ENTRYPOINT} \
    REACT_APP_API_CACHED_HOST_HTTP=http://${API_ENTRYPOINT} \
    yarn build --environment=prod
