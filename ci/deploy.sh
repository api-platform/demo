#!/usr/bin/env bash

set -e

# Update dependencies and docker image end push them taking care to separate by repositories and branches.
helm init --upgrade
helm repo add blackfire https://tech.sparkfabrik.com/blackfire-chart/
helm dependencies update ./api/helm/api

gsutil mb -p $PROJECT_ID -l eu gs://$ADMIN_BUCKET || echo "Admin bucket exists"
gsutil mb -p $PROJECT_ID -l eu gs://$CLIENT_BUCKET || echo "Client bucket exists"

# Build and push the docker images.
docker build --build-arg INSTALL_BLACKFIRE=$BLACKFIRE_ENABLED --pull -t $PHP_REPOSITORY api --target api_platform_php
docker build --pull -t $NGINX_REPOSITORY api --target api_platform_nginx
docker build --pull -t $VARNISH_REPOSITORY api --target api_platform_varnish
gcloud docker -- push $PHP_REPOSITORY
gcloud docker -- push $NGINX_REPOSITORY
gcloud docker -- push $VARNISH_REPOSITORY

if [[ $BRANCH != $DEPLOYMENT_BRANCH ]]
then
    helm delete --purge $RELEASE || echo "No release to purge"
    kubectl delete namespace $NAMESPACE --wait --cascade=true || echo "No namespace to purge"
fi

# Perform a rolling update if a release in the given namespace ever exist, create one otherwise.
echo "Installing/upgrading release '${RELEASE}' on namespace '${NAMESPACE}'"
helm upgrade --install --reset-values --force --namespace=${NAMESPACE} --recreate-pods ${RELEASE} ./api/helm/api \
    --set php.repository=$PHP_REPOSITORY,php.tag=$TAG \
    --set nginx.repository=$NGINX_REPOSITORY,nginx.tag=$TAG \
    --set varnish.repository=$VARNISH_REPOSITORY,varnish.tag=$TAG \
    --set blackfire.blackfire.server_id=$BLACKFIRE_SERVER_ID \
    --set blackfire.blackfire.server_token=$BLACKFIRE_SERVER_TOKEN \
    --set blackfire.blackfire.enabled=$BLACKFIRE_ENABLED \
    --set php.mercure.jwt=$MERCURE_JWT \
    --set mercure.jwtKey=$MERCURE_JWT_KEY \
    --set postgresql.postgresqlPassword=$DATABASE_PASSWORD \
    --set ingress.hosts.api.host=$API_ENTRYPOINT \
    --set ingress.hosts.mercure.host=$MERCURE_ENTRYPOINT \
    --set mercure.subscribeUrl="${MERCURE_ENTRYPOINT}/hub" \
    --set external-dns.cloudflare.apiKey=$CLOUDFLARE_API_KEY \
    --set external-dns.cloudflare.email=$CLOUDFLARE_API_EMAIL

# Reload fixtures: this is specific for this project!
echo "Waiting for the PHP container to be up and ready..."
sleep 60
kubectl exec --namespace=$NAMESPACE -it $(kubectl --namespace=$NAMESPACE get pods -l app=api-php -o jsonpath="{.items[0].metadata.name}") \
    -- sh -c 'APP_ENV=dev composer install -n && bin/console d:s:u --force -e prod && bin/console h:f:l -n -e dev && APP_ENV=prod composer --no-dev install --classmap-authoritative && echo deployed'

# Build & deploy the admin.
cd admin && CI=false yarn install && REACT_APP_API_ENTRYPOINT=https://$API_ENTRYPOINT CI=false yarn build --environment=prod && cd ..
gsutil rsync -R admin/build gs://$ADMIN_BUCKET
gsutil web set -m index.html -e index.html gs://$ADMIN_BUCKET
gsutil iam ch allUsers:objectViewer gs://$ADMIN_BUCKET

# Build & deploy the client.
cd client && yarn install && REACT_APP_ADMIN_HOST_HTTPS=https://$ADMIN_BUCKET REACT_APP_API_CACHED_HOST_HTTPS=https://$API_ENTRYPOINT REACT_APP_API_ENTRYPOINT=https://$API_ENTRYPOINT yarn build --environment=prod && cd ..
gsutil rsync -R client/build gs://$CLIENT_BUCKET
gsutil web set -m index.html -e index.html gs://$CLIENT_BUCKET
gsutil iam ch allUsers:objectViewer gs://$CLIENT_BUCKET
