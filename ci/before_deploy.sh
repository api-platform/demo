#!/usr/bin/env bash

set -e

# Check that all needed environment variables are set.
if [[ $TRAVIS_PULL_REQUEST != 'false' ]]; then echo 'Skipping deployment for pull requests';
else
    if [[ -z $PROJECT_ID ]]; then echo 'PROJECT_ID is not defined in your travis environment variables.'; fi
    if [[ -z $CI_SERVICE_ACCOUNT ]]; then echo 'CI_SERVICE_ACCOUNT is not defined in your ci repository environment variables.'; fi
    if [[ -z $CI_SERVICE_ACCOUNT_KEY ]]; then echo 'CI_SERVICE_ACCOUNT_KEY is not defined in your ci repository environment variables.'; fi
fi

# To enable blackfire, set the BLACKFIRE_SERVER_ID and BLACKFIRE_SERVER_TOKEN variables.
if [[ ! -z $BLACKFIRE_SERVER_ID && ! -z $BLACKFIRE_SERVER_TOKEN ]]; then
    export BLACKFIRE_ENABLED=true
fi

# Generate random key & jwt for Mercure if not set
if [[ -z $MERCURE_JWT_KEY ]]; then
    npm install --global "@clarketm/jwt-cli"
    export MERCURE_JWT_KEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
    export MERCURE_JWT=$(jwt sign --noCopy '{"mercure": {"publish": ["*"]}}' $MERCURE_JWT_KEY)
fi

# Generate random database password if not set
if [[ -z $DATABASE_PASSWORD ]]; then
    export DATABASE_PASSWORD=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
fi

export PHP_REPOSITORY="eu.gcr.io/${PROJECT_ID}/php"
export NGINX_REPOSITORY="eu.gcr.io/${PROJECT_ID}/nginx"
export VARNISH_REPOSITORY="eu.gcr.io/${PROJECT_ID}/varnish"
if [[ $BRANCH == $DEPLOYMENT_BRANCH ]]
then
    export TAG=latest
    export API_ENTRYPOINT="${API_SUBDOMAIN}.${DOMAIN}"
    export MERCURE_ENTRYPOINT="${MERCURE_SUBDOMAIN}.${DOMAIN}"
    export ADMIN_BUCKET="${ADMIN_SUBDOMAIN}.${DOMAIN}"
    export CLIENT_BUCKET="${CLIENT_SUBDOMAIN}.${DOMAIN}"
else
    export TAG=$RELEASE
    export API_ENTRYPOINT="${API_SUBDOMAIN}-${RELEASE}.${DOMAIN}"
    export MERCURE_ENTRYPOINT="${MERCURE_SUBDOMAIN}-${RELEASE}.${DOMAIN}"
    export ADMIN_BUCKET="${ADMIN_SUBDOMAIN}-${RELEASE}.${DOMAIN}"
    export CLIENT_BUCKET="${CLIENT_SUBDOMAIN}-${RELEASE}.${DOMAIN}"
fi

# Get kubectl and make it executable
curl -LO https://storage.googleapis.com/kubernetes-release/release/$(curl -s https://storage.googleapis.com/kubernetes-release/release/stable.txt)/bin/linux/amd64/kubectl
chmod +x ./kubectl
sudo mv ./kubectl /usr/local/bin/kubectl

# Authenticate on GCP
echo -n $CI_SERVICE_ACCOUNT_KEY | base64 -d > travis-service-account.json
gcloud auth activate-service-account $CI_SERVICE_ACCOUNT --key-file travis-service-account.json --project=$PROJECT_ID
gcloud config set compute/zone europe-west1-c
gcloud config set core/project $PROJECT_ID
gcloud container clusters get-credentials api-platform-demo --zone europe-west1-c --project $PROJECT_ID
