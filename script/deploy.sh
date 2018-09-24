#!/usr/bin/env bash

# Get kubectl and make it executable
curl -LO https://storage.googleapis.com/kubernetes-release/release/$(curl -s https://storage.googleapis.com/kubernetes-release/release/stable.txt)/bin/linux/amd64/kubectl
chmod +x ./kubectl
sudo mv ./kubectl /usr/local/bin/kubectl
# Create the travis service account access file from travis environment variables
echo -n ${TRAVIS_SERVICE_ACCOUNT_KEY} | base64 -d > travis-service-account.json
# Connect to the project as travis service account by gcloud using the travis service account access file we just created above and configure project.
gcloud auth activate-service-account ${TRAVIS_SERVICE_ACCOUNT} --key-file travis-service-account.json --project=${PROJECT_ID}
gcloud config set compute/zone europe-west1-c
gcloud config set core/project ${PROJECT_ID}
gcloud container clusters get-credentials api-platform-demo --zone europe-west1-c --project ${PROJECT_ID}
helm init --upgrade
# If exist, delete the last namespace we created with app label set to api-demo
kubectl delete namespace $(kubectl get namespaces -l app=api-demo -o jsonpath="{.items[0].metadata.name}" --ignore-not-found) --ignore-not-found
# Update dependencies and docker image end push them.
helm dependencies update ./api/helm/api
docker build --pull -t eu.gcr.io/${PROJECT_ID}/php -t eu.gcr.io/${PROJECT_ID}/php:latest api --target api_platform_php
docker build --pull -t eu.gcr.io/${PROJECT_ID}/nginx -t eu.gcr.io/${PROJECT_ID}/nginx:latest api --target api_platform_nginx
docker build --pull -t eu.gcr.io/${PROJECT_ID}/varnish -t eu.gcr.io/${PROJECT_ID}/varnish:latest api --target api_platform_varnish
gcloud docker -- push eu.gcr.io/${PROJECT_ID}/php:latest
gcloud docker -- push eu.gcr.io/${PROJECT_ID}/nginx:latest
gcloud docker -- push eu.gcr.io/${PROJECT_ID}/varnish:latest
# Perform a rolling update if a release in the given namespace ever exist, create one otherwise.
helm upgrade --install --reset-values --wait --force --namespace=${TRAVIS_COMMIT} --recreate-pods demo ./api/helm/api  \
    --set php.repository=eu.gcr.io/${PROJECT_ID}/php \
    --set nginx.repository=eu.gcr.io/${PROJECT_ID}/nginx \
    --set varnish.repository=eu.gcr.io/${PROJECT_ID}/varnish \
    --set secret=${APP_SECRET} \
    --set postgresUser=${DATABASE_USER},postgresPassword="${DATABASE_PASSWORD}",postgresDatabase=${DATABASE_NAME} --set postgresql.persistence.enabled=true \
    --set ingress.annotations.kubernetes.io/ingress.global-static-ip-name=api-platform-demo-ip
# Install what needed on the php pod of the release using composer and label the namespace as the new api-demo
kubectl exec -it $(kubectl --namespace=${TRAVIS_COMMIT} get pods -l app=api-php -o jsonpath="{.items[0].metadata.name}") --namespace=${TRAVIS_COMMIT} \
    -- ash -c'export APP_ENV=dev && composer install -n && bin/console d:s:u --force --env=dev && bin/console hautelook:fixtures:load -n && APP_ENV=prod composer --no-dev install --classmap-authoritative && bin/console d:s:u --env=prod'
kubectl label namespace ${TRAVIS_COMMIT} app=api-demo
# Build and push the client and the admin
export REACT_APP_API_ENTRYPOINT_IP=$(kubectl --namespace `echo ${TRAVIS_COMMIT}` get ingress -o jsonpath='{.items[0].status.loadBalancer.ingress[0].ip}')
#TODO use REACT_APP_API_ENTRYPOINT instead of REACT_APP_API_ENTRYPOINT_IP, remove REACT_APP_API_ENTRYPOINT_IP as we doesnt need it anymore.
cd admin && yarn && REACT_APP_API_ENTRYPOINT=https://${REACT_APP_API_ENTRYPOINT_IP} yarn build --environment=prod
cd ../client && yarn &&  REACT_APP_ADMIN_HOST_HTTPS=https://demo-admin.api-platform.com REACT_APP_API_CACHED_HOST_HTTPS=https://${REACT_APP_API_ENTRYPOINT_IP}:443 yarn build --environment=prod
