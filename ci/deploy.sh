#!/usr/bin/env bash

# Update dependencies and docker image end push them taking care to separate by repositories and branches.
echo 'deploy script'

export PHP_REPOSITORY="eu.gcr.io/${PROJECT_ID}/php";
export NGINX_REPOSITORY="eu.gcr.io/${PROJECT_ID}/nginx";
export VARNISH_REPOSITORY="eu.gcr.io/${PROJECT_ID}/varnish";

if [[ "${MULTI_BRANCH}"== 0 ]]
then
    # You can get the deployments name by running kubectl get deployments --namespace=you_namespace
    # You can check everything is fine by running kubectl rollout status deployment/your-deployment --namespace=your_namespace
    # You can also rollback if there was ann error by running kubectl rollout undo deployment/your-deployment --namespace=your_namespace
    # Here we check if a running release with named api-platform-demo already exist to update or intall it.
    kubectl create namespace prod || echo "prod namespace already exist."
    kubectl config set-context $(kubectl config current-context) --namespace=prod
    helm list -q --pending --deployed | grep NAME
    # This line check what the last command return in order to know if we should rolling update or create the release
    if [ $? == 0 ]; then
        kubectl set image deployments/api-php "${PHP_REPOSITORY}"
        kubectl set image deployments/api-nginx "${NGINX_REPOSITORY}"
        kubectl set image deployments/api-varnish "${VARNISH_REPOSITORY}"
    else
        helm install ./api/helm/api --wait \
            --set ingress.annotations.kubernetes.io/ingress.global-static-ip-name: "${STATIC_IP}" \
            --set php.repository="${PHP_REPOSITORY}" \
            --set nginx.repository="${NGINX_REPOSITORY}" \
            --set varnish.repository="${VARNISH_REPOSITORY}" \
            --set secret="${APP_SECRET}" \
            --set postgresUser="${DATABASE_USER}",postgresPassword="${DATABASE_PASSWORD}",postgresDatabase="${DATABASE_NAME}" --set postgresql.persistence.enabled=true;
    fi

    # For the master branch the REACT_APP_API_ENTRYPOINT will be the URL plug on your static IP.
    export API_ENTRYPOINT="${PROD_DNS}";
else
    kubectl create namespace "${COMMIT}" || echo 'Namespace already exist, updating.';
    # Upgrading the release by forcing pods to recreate if needed this is not a rolling update and downtime may occur.
    helm upgrade "${PROJECT_NAME}" ./api/helm/api --install --reset-values --wait --force --namespace="${COMMIT}" --recreate-pods \
        --set php.repository="${PHP_REPOSITORY}":"${COMMIT}" \
        --set nginx.repository="${NGINX_REPOSITORY}":"${COMMIT}" \
        --set varnish.repository="${VARNISH_REPOSITORY}":"${COMMIT}" \
        --set secret="${APP_SECRET}" \
        --set postgresUser="${DATABASE_USER}",postgresPassword="${DATABASE_PASSWORD}",postgresDatabase="${DATABASE_NAME}" --set postgresql.persistence.enabled=true;

    # For Dev branchs you can use the IP retrievable by the kubectl get ingress command.
    export API_ENTRYPOINT=$(kubectl --namespace `echo "${COMMIT}"` get ingress -o jsonpath='{.items[0].status.loadBalancer.ingress[0].ip}');
fi
# To get the IP of the created release, you can run kubectl --namespace=your_namespace get ingress -o jsonpath='{.items[0].status.loadBalancer.ingress[0].ip}'
# You may wait a little until it is available.
