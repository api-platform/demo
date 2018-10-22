#!/usr/bin/env bash

cd "$(dirname "$0")"/..

# Admin build deployment to bucket
yarn install --pure-lockfile
REACT_APP_API_ENTRYPOINT=https://${API_ENTRYPOINT} yarn build --environment=prod

gsutil rsync -R admin/build gs://"${ADMIN_BUCKET}"
gsutil web set -m index.html gs://"${ADMIN_BUCKET}"
gsutil iam ch allUsers:objectViewer gs://"${ADMIN_BUCKET}"
