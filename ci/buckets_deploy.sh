#!/usr/bin/env bash

cd "$(dirname "$0")"/..

# Admin build deployment to bucket
gsutil rsync -R admin/build gs://${ADMIN_BUCKET}
gsutil web set -m index.html gs://${ADMIN_BUCKET}
gsutil iam ch allUsers:objectViewer gs://${ADMIN_BUCKET}

# Client build deployment to bucket
gsutil rsync -R client/build gs://${CLIENT_BUCKET}
gsutil web set -m index.html gs://${CLIENT_BUCKET}
gsutil iam ch allUsers:objectViewer gs://${CLIENT_BUCKET}
