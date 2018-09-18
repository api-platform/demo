#!/usr/bin/env bash

curl -LO https://storage.googleapis.com/kubernetes-release/release/$(curl -s https://storage.googleapis.com/kubernetes-release/release/stable.txt)/bin/linux/amd64/kubectl
chmod +x ./kubectl
sudo mv ./kubectl /usr/local/bin/kubectl
echo -n ${TRAVIS_SERVICE_ACCOUNT_KEY} | base64 -d > travis-service-account.json
gcloud auth activate-service-account ${TRAVIS_SERVICE_ACCOUNT} --key-file travis-service-account.json --project=${PROJECT_NAME}
gcloud config set compute/zone europe-west1-c
gcloud config set project ${PROJECT_NAME}
gcloud container clusters get-credentials api-platform-demo --zone europe-west1-c --project ${PROJECT_NAME}
helm init --upgrade
kubectl delete namespace $(kubectl get namespaces -l app=api-demo-${KUBERNETES_ENV} -o jsonpath="{.items[0].metadata.name}" --ignore-not-found) --ignore-not-found
helm dependencies update ./api/helm/api
docker build --pull -t eu.gcr.io/${PROJECT_NAME}/php-${KUBERNETES_ENV} -t eu.gcr.io/${PROJECT_NAME}/php-${KUBERNETES_ENV}:latest api --target api_platform_php
docker build --pull -t eu.gcr.io/${PROJECT_NAME}/nginx-${KUBERNETES_ENV} -t eu.gcr.io/${PROJECT_NAME}/nginx-${KUBERNETES_ENV}:latest api --target api_platform_nginx
docker build --pull -t eu.gcr.io/${PROJECT_NAME}/varnish-${KUBERNETES_ENV} -t eu.gcr.io/${PROJECT_NAME}/varnish-${KUBERNETES_ENV}:latest api --target api_platform_varnish
gcloud docker -- push eu.gcr.io/${PROJECT_NAME}/php-${KUBERNETES_ENV}:latest
gcloud docker -- push eu.gcr.io/${PROJECT_NAME}/nginx-${KUBERNETES_ENV}:latest
gcloud docker -- push eu.gcr.io/${PROJECT_NAME}/varnish-${KUBERNETES_ENV}:latest
helm install --wait --namespace=${TRAVIS_COMMIT} ./api/helm/api --set php.repository=eu.gcr.io/${PROJECT_NAME}/php-${KUBERNETES_ENV} --set nginx.repository=eu.gcr.io/${PROJECT_NAME}/nginx-${KUBERNETES_ENV} --set varnish.repository=eu.gcr.io/${PROJECT_NAME}/varnish-${KUBERNETES_ENV} --set secret=${APP_SECRET} --set postgresUser=${DATABASE_USER},postgresPassword="${DATABASE_PASSWORD}",postgresDatabase=${DATABASE_NAME} --set postgresql.persistence.enabled=true ${IP}
sleep 60
kubectl exec -it $(kubectl --namespace=${TRAVIS_COMMIT} get pods -l app=api-php -o jsonpath="{.items[0].metadata.name}") --namespace=${TRAVIS_COMMIT} -- ash -c'export APP_ENV=dev && composer install -n && bin/console d:s:u --force --env=dev && bin/console hautelook:fixtures:load -n && APP_ENV=prod composer --no-dev install --classmap-authoritative && bin/console d:s:u --env=prod'
kubectl label namespace ${TRAVIS_COMMIT} app=api-demo-${KUBERNETES_ENV}
export REACT_APP_API_ENTRYPOINT_IP=$(kubectl --namespace `echo ${TRAVIS_COMMIT}` get ingress -o jsonpath='{.items[0].status.loadBalancer.ingress[0].ip}')
cd admin && yarn && REACT_APP_API_ENTRYPOINT=http://${REACT_APP_API_ENTRYPOINT_IP} yarn build --environment=prod
cd ../client && yarn && REACT_APP_ADMIN_HOST_HTTP=http://demo-admin.les-tilleuls${BUCKET_SUFFIX} REACT_APP_ADMIN_HOST_HTTPS=https://demo-admin.les-tilleuls${BUCKET_SUFFIX} REACT_APP_API_CACHED_HOST_HTTP=http://${REACT_APP_API_ENTRYPOINT_IP}:80 REACT_APP_API_CACHED_HOST_HTTPS=https://${REACT_APP_API_ENTRYPOINT_IP}:443 yarn build --environment=prod
