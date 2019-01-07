#!/usr/bin/env bash

# Ensure required environment variables are set.
ENV_VARS=(
    ADMIN_BUCKET
    APP_SECRET
    BRANCH
    CI_SERVICE_ACCOUNT
    CLIENT_BUCKET
    CLUSTER_NAME
    COMMIT
    CURRENT_CI
    DEPLOYMENT_BRANCH
    DEV_DNS
    MULTI_BRANCH
    NGINX_REPOSITORY
    PHP_REPOSITORY
    PRODUCTION_DEPLOY
    PROD_DNS
    PROD_DATABASE_URL
    PROJECT_ID
    REPOSITORY
    VARNISH_REPOSITORY
)
for i in "${ENV_VARS[@]}"; do
    if [[ -z "${!i}" ]]; then
        echo "$i environment variable must be defined."
        exit 1
    fi
done

if [[ $CURRENT_CI == "circleci" && $CIRCLECI == true ]]; then
# Dev environment without checking if this is a PR or not.
    export DEPLOY_NAMESPACE="$(echo $CIRCLE_BRANCH | sed 's/\//-/g')"
    export DEPLOY_TAG=$CIRCLE_SHA1
    export ZONE=$DEV_DNS
    # Set CLIENT_BUCKET and ADMIN_BUCKET depending on whether we are on multi-branch or not.
    export CLIENT_BUCKET=dev-client.$DEV_DNS
    export ADMIN_BUCKET=dev-admin.$DEV_DNS
    if [[ $MULTI_BRANCH == 1 ]]; then
        export CLIENT_BUCKET=$BRANCH-client.$DEV_DNS
        export ADMIN_BUCKET=$BRANCH-admin.$DEV_DNS
    fi
    export API_DNS=api-$DEPLOY_NAMESPACE.$DEV_DNS
    export API_DNS_PREFIX=api-$DEPLOY_NAMESPACE
    export ADMIN_DNS=admin-$DEPLOY_NAMESPACE.$DEV_DNS
    export IS_PROD_DEPLOY=false
    if [[ ! -z $CIRCLE_PULL_REQUEST ]]; then
    # If it is a PR.
        export DEPLOY_NAMESPACE="pr-$(echo $CIRCLE_PULL_REQUEST | sed 's/[^0-9]*//g')"
        export API_DNS=api-$DEPLOY_NAMESPACE.$DEV_DNS
        export API_DNS_PREFIX=api-$DEPLOY_NAMESPACE
        export ADMIN_DNS=admin-$DEPLOY_NAMESPACE.$DEV_DNS
    elif [[ ! -z $CIRCLE_TAG && $CIRCLE_PROJECT_REPONAME == $REPOSITORY ]]; then
    # If it is prod (tag is set and circle project reponame is the good one).
        export DEPLOY_NAMESPACE=$DEPLOYMBUCKET_NAMEENT_BRANCH
        export DEPLOY_TAG=$CIRCLE_TAG
        export ZONE=$PROD_DNS
        export API_DNS=api.$PROD_DNS
        export API_DNS_PREFIX=api
        export ADMIN_DNS=admin.$PROD_DNS
        export IS_PROD_DEPLOY=true
        export CLIENT_BUCKET=$PROD_CLIENT_BUCKET_NAME
        export ADMIN_BUCKET=$PROD_ADMIN_BUCKET_NAME
    fi
elif [[ $CURRENT_CI == "travis" && $TRAVIS == true ]]; then
# Dev environment without checking if this is a PR or not.
    export DEPLOY_NAMESPACE="$(echo $TRAVIS_BRANCH | sed 's/[^0-9]*//g')"
    export DEPLOY_TAG=$TRAVIS_COMMIT
    export ZONE=$DEV_DNS
    # Set CLIENT_BUCKET and ADMIN_BUCKET depending on whether we are on multi-branch or not.
    export CLIENT_BUCKET=dev-client.$DEV_DNS
    export ADMIN_BUCKET=dev-admin.$DEV_DNS
    if [[ $MULTI_BRANCH == 1 ]]; then
        export CLIENT_BUCKET=$BRANCH-client.$DEV_DNS
        export ADMIN_BUCKET=$BRANCH-admin.$DEV_DNS
    fi
    export API_DNS=api-$DEPLOY_NAMESPACE.$DEV_DNS
    export API_DNS_PREFIX=api-$DEPLOY_NAMESPACE
    export ADMIN_DNS=admin-$DEPLOY_NAMESPACE.$DEV_DNS
    export IS_PROD_DEPLOY=false
    # If it is a PR.
    if [[ ! -z $TRAVIS_PULL_REQUEST ]]; then
    # If it is a PR.
        export DEPLOY_NAMESPACE="pr-$(echo $TRAVIS_PULL_REQUEST | sed 's/[^0-9]*//g')"
        export API_DNS=api-$DEPLOY_NAMESPACE.$DEV_DNS
        export API_DNS_PREFIX=api-$DEPLOY_NAMESPACE
        export ADMIN_DNS=admin-$DEPLOY_NAMESPACE.$DEV_DNS
    # If it is prod (tag is set and circle project reponame is the good one).
    elif [[ ! -z $TRAVIS_TAG && $TRAVIS_REPO_SLUG == $REPOSITORY ]]; then
    # If it is prod (tag is set and circle project reponame is the good one).
        export DEPLOY_NAMESPACE=$DEPLOYMENT_BRANCH
        export DEPLOY_TAG=$TRAVIS_TAG
        export ZONE=$PROD_DNS
        export API_DNS=api.$PROD_DNS
        export API_DNS_PREFIX=api
        export ADMIN_DNS=admin.$PROD_DNS
        export IS_PROD_DEPLOY=true
        export CLIENT_BUCKET=$PROD_CLIENT_BUCKET_NAME
        export ADMIN_BUCKET=$PROD_ADMIN_BUCKET_NAME
    fi
else
    echo "Your CI is not supported."
    exit 1
fi

