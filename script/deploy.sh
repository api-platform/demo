#!/usr/bin/env bash


# Update dependencies and docker image end push them taking care to separate by repositories and branches.
helm dependencies update ./api/helm/api
if [[ ${BRANCH} == "master" ]]
then
    export PHP_REPOSITORY="eu.gcr.io/${PROJECT_ID}/php";
    export NGINX_REPOSITORY="eu.gcr.io/${PROJECT_ID}/nginx";
    export VARNISH_REPOSITORY="eu.gcr.io/${PROJECT_ID}/varnish";
else
    export PHP_REPOSITORY="eu.gcr.io/${PROJECT_ID}/php-staging";
    export NGINX_REPOSITORY="eu.gcr.io/${PROJECT_ID}/nginx-staging";
    export VARNISH_REPOSITORY="eu.gcr.io/${PROJECT_ID}/varnish-staging";
fi

docker build --pull -t ${PHP_REPOSITORY} -t ${PHP_REPOSITORY}:latest api --target api_platform_php;
docker build --pull -t ${NGINX_REPOSITORY} -t ${NGINX_REPOSITORY}:latest api --target api_platform_nginx;
docker build --pull -t ${VARNISH_REPOSITORY} -t ${VARNISH_REPOSITORY}:latest api --target api_platform_varnish;
gcloud docker -- push ${PHP_REPOSITORY}:latest;
gcloud docker -- push ${NGINX_REPOSITORY}:latest;
gcloud docker -- push ${VARNISH_REPOSITORY}:latest;

# Perform a rolling update if a release in the given namespace ever exist, create one otherwise.
helm upgrade --install --reset-values --wait --force --namespace=${BRANCH} --recreate-pods demo ./api/helm/api  \
    --set php.repository=${PHP_REPOSITORY} \
    --set nginx.repository=${NGINX_REPOSITORY} \
    --set varnish.repository=${VARNISH_REPOSITORY} \
    --set secret=${APP_SECRET} \
    --set postgresUser=${DATABASE_USER},postgresPassword="${DATABASE_PASSWORD}",postgresDatabase=${DATABASE_NAME} --set postgresql.persistence.enabled=true \
    --set ingress.annotations.kubernetes.io/ingress.global-static-ip-name: api-platform-demo-ip;
# Install what needed on the php pod of the release using composer and label the namespace.
kubectl exec -it $(kubectl --namespace=${BRANCH} get pods -l app=api-php -o jsonpath="{.items[0].metadata.name}") --namespace=${BRANCH} \
    -- ash -c'export APP_ENV=dev && composer install -n && bin/console d:s:u --force --env=dev && bin/console hautelook:fixtures:load -n && APP_ENV=prod composer --no-dev install --classmap-authoritative && bin/console d:s:u --env=prod';
# Build and push the client and the admin
export REACT_APP_API_ENTRYPOINT_IP=$(kubectl --namespace `echo ${BRANCH}` get ingress -o jsonpath='{.items[0].status.loadBalancer.ingress[0].ip}');
cd admin && yarn && REACT_APP_API_ENTRYPOINT=https://${REACT_APP_API_ENTRYPOINT_IP} yarn build --environment=prod;
cd ../client && yarn &&  REACT_APP_ADMIN_HOST_HTTPS=https://demo-admin.api-platform.com REACT_APP_API_CACHED_HOST_HTTPS=https://${REACT_APP_API_ENTRYPOINT_IP}:443 yarn build --environment=prod;
