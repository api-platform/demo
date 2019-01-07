#!/usr/bin/env bash

set -e

source `dirname $0`/.env

# Authenticate on gcloud services
echo -n "${CI_SERVICE_ACCOUNT_KEY}" | base64 -d > ci-service-account.json
gcloud auth activate-service-account "${CI_SERVICE_ACCOUNT}" --key-file ci-service-account.json --project="${PROJECT_ID}"
gcloud config set compute/zone europe-west1-c
gcloud config set core/project "${PROJECT_ID}"
gcloud container clusters get-credentials api-platform-demo --zone europe-west1-c --project "${PROJECT_ID}"
