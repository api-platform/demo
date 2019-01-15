#!/usr/bin/env bash

set -e

helm lint api/helm/api/
helm init --upgrade --service-account tiller
helm repo add cloudflare https://cloudflare.github.io/helm-charts
helm repo update
helm dependencies update ./api/helm/api

gcloud auth configure-docker --quiet

echo "Deploy to $DEPLOY_NAMESPACE"
# Build and push the docker images.
docker build --pull -t $PHP_REPOSITORY:$DEPLOY_TAG api --target data_b_php
docker build --pull -t $NGINX_REPOSITORY:$DEPLOY_TAG api --target data_b_nginx
docker build --pull -t $VARNISH_REPOSITORY:$DEPLOY_TAG api --target data_b_varnish
if [ $IS_PROD_DEPLOY = true ]; then
    docker build --pull -t $PHP_REPOSITORY:latest api --target data_b_php
    docker build --pull -t $NGINX_REPOSITORY:latest api --target data_b_nginx
    docker build --pull -t $VARNISH_REPOSITORY:latest api --target data_b_varnish
fi
docker push $PHP_REPOSITORY:$DEPLOY_TAG
docker push $NGINX_REPOSITORY:$DEPLOY_TAG
docker push $VARNISH_REPOSITORY:$DEPLOY_TAG
if [[ $DEPLOY_NAMESPACE == $DEPLOYMENT_BRANCH ]]; then
    docker push $PHP_REPOSITORY:latest
    docker push $NGINX_REPOSITORY:latest
    docker push $VARNISH_REPOSITORY:latest
fi

# You can get the deployments name by running kubectl get deployments --namespace=your_namespace
# You can check everything is fine by running kubectl rollout status deployment/your-deployment --namespace=your_namespace
# You can also rollback if there was an error by running kubectl rollout undo deployment/your-deployment --namespace=your_namespace
if [ $IS_PROD_DEPLOY != true ]; then
    kubectl delete namespace $DEPLOY_NAMESPACE --wait --cascade=true || echo "Namespace does not exist"
fi

# Check if a release already exist in namespace in order to know if we have to create one or perform a rolling update
kubectl config set-context $(kubectl config current-context) --namespace=$DEPLOY_NAMESPACE
if helm list -q --pending --deployed | grep NAME; then
    kubectl set image deployments/api-php $PHP_REPOSITORY:$DEPLOY_TAG
    kubectl set image deployments/api-nginx $NGINX_REPOSITORY:$DEPLOY_TAG
    kubectl set image deployments/api-varnish $VARNISH_REPOSITORY:$DEPLOY_TAG
else
    kubectl delete clusterrole $DEPLOY_NAMESPACE || echo "ClusterRole doesn't exist"
    kubectl delete clusterrolebinding $DEPLOY_NAMESPACE-binding || echo "ClusterRoleBinding doesn't exist"
    kubectl create clusterrolebinding $DEPLOY_NAMESPACE-binding --clusterrole=$DEPLOY_NAMESPACE  --serviceaccount=$DEPLOY_NAMESPACE:default
    [ $IS_PROD_DEPLOY = true ] && POSTGRESQL_ENABLED=false || POSTGRESQL_ENABLED=true

    kubectl create namespace $DEPLOY_NAMESPACE

    echo $CLOUDFLARE_CERT | base64 -d > cert.pem
    kubectl create secret generic cloudflare-certificate --namespace=$DEPLOY_NAMESPACE --from-file=cert.pem
    kubectl create secret generic google-credentials --namespace=$DEPLOY_NAMESPACE --from-file=service-account=google-service-account.json

    helm install ./api/helm/api --namespace=$DEPLOY_NAMESPACE --wait \
        --set namespace=$DEPLOY_NAMESPACE \
        --set apiDomain=$API_DNS \
        --set adminEmail=$ADMIN_EMAIL \
        --set php.repository=$PHP_REPOSITORY --set php.tag=$DEPLOY_TAG \
        --set nginx.repository=$NGINX_REPOSITORY --set nginx.tag=$DEPLOY_TAG \
        --set varnish.repository=$VARNISH_REPOSITORY --set varnish.tag=$DEPLOY_TAG \
        --set secret=$APP_SECRET \
        --set postgresql.url="${PROD_DATABASE_URL//,/\\,}",postgresql.enabled=$POSTGRESQL_ENABLED \
        --set mailer.url="${PROD_MAILER_URL//,/\\,}" \
        --set bucketName=$BUCKET_NAME \
        --set google.projectId=$PROJECT_ID \
        --set corsAllowOrigin="^https?://.*?.$ZONE$"
fi
