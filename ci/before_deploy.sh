#!/usr/bin/env bash

curl -LO https://storage.googleapis.com/kubernetes-release/release/$(curl -s https://storage.googleapis.com/kubernetes-release/release/stable.txt)/bin/linux/amd64/kubectl
chmod +x ./kubectl
sudo mv ./kubectl /usr/local/bin/kubectl
echo -n "${CI_SERVICE_ACCOUNT_KEY}" | base64 -d > ci-service-account.json
gcloud auth activate-service-account "${CI_SERVICE_ACCOUNT}" --key-file ci-service-account.json --project="${PROJECT_ID}"
gcloud config set compute/zone europe-west1-c
gcloud config set core/project "${PROJECT_ID}"
gcloud container clusters get-credentials api-platform-demo --zone europe-west1-c --project "${PROJECT_ID}"
helm init --upgrade

gsutil mb -p "${PROJECT_ID}" -l eu gs://"${ADMIN_BUCKET}" || echo "Admin bucket exists"
gsutil mb -p "${PROJECT_ID}" -l eu gs://"${CLIENT_BUCKET}" || echo "Client bucket exists"
