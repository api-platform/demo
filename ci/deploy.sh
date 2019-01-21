#!/usr/bin/env bash

set -e

# Update dependencies and docker image end push them taking care to separate by repositories and branches.
echo 'deploy script'
helm dependencies update ./api/helm/api

# You can customize it to fit your needs, for example for branch naming e.g. PHP_REPOSITORY="eu.gcr.io/${PROJECT_ID}/php-${BRANCH}"
if [[ ${BRANCH} == ${DEPLOYMENT_BRANCH} ]]
then
    export PHP_REPOSITORY="eu.gcr.io/${PROJECT_ID}/php";
    export NGINX_REPOSITORY="eu.gcr.io/${PROJECT_ID}/nginx";
    export VARNISH_REPOSITORY="eu.gcr.io/${PROJECT_ID}/varnish";
else
    export PHP_REPOSITORY="eu.gcr.io/${PROJECT_ID}/php-staging";
    export NGINX_REPOSITORY="eu.gcr.io/${PROJECT_ID}/nginx-staging";
    export VARNISH_REPOSITORY="eu.gcr.io/${PROJECT_ID}/varnish-staging";
fi
export NAMESPACE=demo-${BRANCH};
export RELEASE=${NAMESPACE};
if [[ -z $MERCURE_JWT_KEY ]]; then
    export MERCURE_JWT_KEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1);
    export MERCURE_JWT=$(jwt sign --noCopy '{"mercure": {"publish": ["*"]}}' $MERCURE_JWT_KEY);
fi

# Build and push the docker images.
docker build --pull -t ${PHP_REPOSITORY} -t ${PHP_REPOSITORY}:latest api --target api_platform_php;
docker build --pull -t ${NGINX_REPOSITORY} -t ${NGINX_REPOSITORY}:latest api --target api_platform_nginx;
docker build --pull -t ${VARNISH_REPOSITORY} -t ${VARNISH_REPOSITORY}:latest api --target api_platform_varnish;
gcloud docker -- push ${PHP_REPOSITORY}:latest;
gcloud docker -- push ${NGINX_REPOSITORY}:latest;
gcloud docker -- push ${VARNISH_REPOSITORY}:latest;

echo "Installing or upgrading release '${RELEASE}' on namespace '${NAMESPACE}'"
# Perform a rolling update if a release in the given namespace ever exist, create one otherwise.
# Be aware that we have the static ip for the master branch but it belongs to you to care about others.
helm upgrade --install --reset-values --wait --force --namespace=${NAMESPACE} --recreate-pods ${RELEASE} ./api/helm/api \
    --set php.repository=${PHP_REPOSITORY} \
    --set nginx.repository=${NGINX_REPOSITORY} \
    --set varnish.repository=${VARNISH_REPOSITORY} \
    --set secret=${APP_SECRET} \
    --set php.mercure.jwt=${MERCURE_JWT} \
    --set mercure.jwtKey=${MERCURE_JWT_KEY} \
    --set postgresql.postgresUser=${DATABASE_USER},postgresql.postgresPassword="${DATABASE_PASSWORD}",postgresql.postgresDatabase=${DATABASE_NAME} --set postgresql.persistence.enabled=true;

echo "Waiting for api-php to be up and ready..."
sleep 60
kubectl exec -it $(kubectl --namespace=${NAMESPACE} get pods -l app=api-php -o jsonpath="{.items[0].metadata.name}") --namespace=${NAMESPACE} \
    -- sh -c 'APP_ENV=dev composer install -n && bin/console d:m:m --no-interaction --env=prod && bin/console d:s:u --force --env=prod && bin/console hautelook:fixtures:load -n --env=dev && APP_ENV=prod composer --no-dev install --classmap-authoritative && echo deployed';

# For the master branch the REACT_APP_API_ENTRYPOINT will be the URL plug on your static IP.
# For Dev branchs you can use the IP retrievable by the kubectl get ingress command.
if [[ ${BRANCH} == ${DEPLOYMENT_BRANCH} ]]
then
    export API_ENTRYPOINT=${PROD_DNS};
else
    export API_ENTRYPOINT=$(kubectl --namespace `echo ${NAMESPACE}` get ingress -o jsonpath='{.items[0].status.loadBalancer.ingress[0].ip}');
fi

cd admin && yarn && REACT_APP_API_ENTRYPOINT=https://${API_ENTRYPOINT} CI=false yarn build --environment=prod;
cd ../client && yarn && REACT_APP_ADMIN_HOST_HTTPS=https://${ADMIN_BUCKET} REACT_APP_ADMIN_HOST_HTTP=http://${ADMIN_BUCKET} REACT_APP_API_CACHED_HOST_HTTPS=https://${API_ENTRYPOINT} REACT_APP_API_CACHED_HOST_HTTP=http://${API_ENTRYPOINT} REACT_APP_API_ENTRYPOINT=https://${API_ENTRYPOINT} yarn build --environment=prod && cd ..;

if [[ ${BRANCH} == ${DEPLOYMENT_BRANCH} ]]
then
    gsutil rsync -R admin/build gs://${ADMIN_BUCKET}
    gsutil rsync -R client/build gs://${CLIENT_BUCKET}
    gsutil web set -m index.html -e index.html gs://${ADMIN_BUCKET}
    gsutil web set -m index.html -e index.html gs://${CLIENT_BUCKET}
    gsutil iam ch allUsers:objectViewer gs://${CLIENT_BUCKET}
    gsutil iam ch allUsers:objectViewer gs://${ADMIN_BUCKET}
else
    gsutil rsync -R admin/build gs://${DEV_ADMIN_BUCKET}
    gsutil rsync -R client/build gs://${DEV_CLIENT_BUCKET}
    gsutil web set -m index.html -e index.html gs://${DEV_ADMIN_BUCKET}
    gsutil web set -m index.html -e index.html gs://${DEV_CLIENT_BUCKET}
    gsutil iam ch allUsers:objectViewer gs://${DEV_ADMIN_BUCKET}
    gsutil iam ch allUsers:objectViewer gs://${DEV_CLIENT_BUCKET}
fi
