#!/usr/bin/env bash

set -e

export BUCKETNAME="${1}_NAME"
export BUCKETNAME="${BUCKETNAME^^}"
export BUCKETNAME="${!BUCKETNAME}"

BUCKET=gs://$BUCKETNAME
gsutil mb $BUCKET || echo "Bucket already exists"
gsutil defacl ch -u AllUsers:R $BUCKET
BUILD_PATH=$1
gsutil -q rsync -d -r $BUILD_PATH/build $BUCKET
gsutil web set -m index.html -e index.html $BUCKET

flarectl dns c --zone=$ZONE --name=$BUCKETNAME --type=CNAME --content=c.storage.googleapis.com --proxy
