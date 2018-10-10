#!/usr/bin/env bash

# Update dependencies and docker image end push them taking care to separate by repositories and branches.
echo 'deploy script'
helm dependencies update ./api/helm/api

export PHP_REPOSITORY="eu.gcr.io/${PROJECT_ID}/php";
export NGINX_REPOSITORY="eu.gcr.io/${PROJECT_ID}/nginx";
export VARNISH_REPOSITORY="eu.gcr.io/${PROJECT_ID}/varnish";

if [[ "${MULTI_BRANCH}"== 0 ]]
then
    # Build and push the docker images.
    docker build --pull -t "${PHP_REPOSITORY}" api --target api_platform_php;
    docker build --pull -t "${NGINX_REPOSITORY}" api --target api_platform_nginx;
    docker build --pull -t "${VARNISH_REPOSITORY}" api --target api_platform_varnish;
    gcloud docker -- push "${PHP_REPOSITORY}";
    gcloud docker -- push "${NGINX_REPOSITORY}";
    gcloud docker -- push "${VARNISH_REPOSITORY}";

    # You can get the deployments name by running kubectl get deployments --namespace=you_namespace
    # You can check everything is fine by running kubectl rollout status deployment/your-deployment --namespace=your_namespace
    # You can also rollback if there was ann error by running kubectl rollout undo deployment/your-deployment --namespace=your_namespace
    # Here we check if a running release with named api-platform-demo already exist to update or intall it.
    helm list -q --pending --deployed | grep ${RELEASE_NAME}
    if [ $? == 0 ]; then
        kubectl set image deployments/api-php "${PHP_REPOSITORY}"
        kubectl set image deployments/api-nginx ${NGINX_REPOSITORY}
        kubectl set image deployments/api-varnish ${VARNISH_REPOSITORY}
    else
        helm install --name="${RELEASE_NAME}" ./api/helm/api --namespace="api-platform-demo" --wait \
            --set ingress.annotations.kubernetes.io/ingress.global-static-ip-name: "${STATIC_IP}" \
            --set php.repository="${PHP_REPOSITORY}" \
            --set nginx.repository=${NGINX_REPOSITORY} \
            --set varnish.repository=${VARNISH_REPOSITORY} \
            --set secret="${APP_SECRET}" \
            --set postgresUser=${DATABASE_USER},postgresPassword="${DATABASE_PASSWORD}",postgresDatabase="${DATABASE_NAME}" --set postgresql.persistence.enabled=true;
    fi

    # For the master branch the REACT_APP_API_ENTRYPOINT will be the URL plug on your static IP.
    export API_ENTRYPOINT="${PROD_DNS}";
else
    # Build and push the docker images.
    docker build --pull -t "${PHP_REPOSITORY}":"${BRANCH}" api --target api_platform_php;
    docker build --pull -t ${NGINX_REPOSITORY}:"${BRANCH}" api --target api_platform_nginx;
    docker build --pull -t ${VARNISH_REPOSITORY}:"${BRANCH}" api --target api_platform_varnish;
    gcloud docker -- push "${PHP_REPOSITORY}":"${BRANCH}";
    gcloud docker -- push ${NGINX_REPOSITORY}:"${BRANCH}";
    gcloud docker -- push ${VARNISH_REPOSITORY}:"${BRANCH}";

    kubectl create namespace "${BRANCH}" || echo 'Namespace already exist, updating.';
    # Upgrading the release by forcing pods to recreate if needed this is not a rolling update and downtime may occur.
    helm upgrade "${RELEASE_NAME}" ./api/helm/api --install --reset-values --wait --force --namespace="${BRANCH}" --recreate-pods \
        --set php.repository="${PHP_REPOSITORY}":"${BRANCH}" \
        --set nginx.repository=${NGINX_REPOSITORY}:"${BRANCH}" \
        --set varnish.repository=${VARNISH_REPOSITORY}:"${BRANCH}" \
        --set secret="${APP_SECRET}" \
        --set postgresUser="${DATABASE_USER}",postgresPassword="${DATABASE_PASSWORD}",postgresDatabase="${DATABASE_NAME}" --set postgresql.persistence.enabled=true;

    # For Dev branchs you can use the IP retrievable by the kubectl get ingress command.
    export API_ENTRYPOINT=$(kubectl --namespace `echo "${BRANCH}"` get ingress -o jsonpath='{.items[0].status.loadBalancer.ingress[0].ip}');
fi
# To get the IP of the created release, you can run kubectl --namespace=your_namespace get ingress -o jsonpath='{.items[0].status.loadBalancer.ingress[0].ip}'
# You may wait a little until it is available.
