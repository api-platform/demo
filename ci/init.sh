#!/usr/bin/env bash



if [[ "${CURRENT_CI}" != "travis" ]] && [[ "${CURRENT_CI}" != "circle" ]];
then
    echo "Your CI is not supported by the autodeploy process.";
    exit;
fi

# Ensure required environment variables are set.
ENV_VARS=(
    APP_SECRET
    CI_SERVICE_ACCOUNT
    DEV_DNS
    PROD_DNS
    PROD_DATABASE_URL
    PROJECT_ID
    CLIENT_REPOSITORY
    ADMIN_REPOSITORY
    CLUSTER_NAME
    CURRENT_CI
    DEPLOYMENT_BRANCH
    DEPLOY_REPOSITORY
    DOCKER_REPOSITORY
    MULTI_BRANCH
    NGINX_REPOSITORY
    PHP_REPOSITORY
    VARNISH_REPOSITORY
    REPOSITORY
    BRANCH
    COMMIT
    ADMIN_BUCKET
    CLIENT_BUCKET
    PRODUCTION_DEPLOY
)
for i in "${ENV_VARS[@]}"; do
    if [[ -z "${!i}" ]]; then
        echo "$i environment variable must be defined."
        exit 1
    fi
done

# Set COMMIT and CURRENT_CI environment variables values depending on which CI is working.
if [[ -z "${CURRENT_CI}" ]];
then
    if [ "${CIRCLECI}" = "true" ];
    then
        export COMMIT = ${CIRCLE_SHA1}
        export CURRENT_CI="circle";
    elif [ "${TRAVIS}" = "true" ];
    then
        export COMMIT = ${TRAVIS_COMMIT}
        export CURRENT_CI="travis";
    fi
fi

# Check if we are in production or branch naming deployment.
if [[ ${MULTI_BRANCH} == 1 ]];
then
    export ADMIN_BUCKET="${BRANCH}.${ADMIN_BUCKET}"
    export CLIENT_BUCKET="${BRANCH}.${CLIENT_BUCKET}"
fi

# Check in which kind of deployment we are.
# If you want to deploy on tag you can do this instead: ! [[ "${MULTI_BRANCH}" == 0 ]] && [[ "${TRAVIS_PULL_REQUEST}" == "false" ]] && [[ -n "${CI_TAG}" ]]
! [[ "${MULTI_BRANCH}" == 0 ]] && [[ "${TRAVIS_PULL_REQUEST}" == "false" ]] && [[ "${BRANCH}" == ${DEPLOYMENT_BRANCH} ]]
export PRODUCTION_DEPLOY=$?

! [[ "${MULTI_BRANCH}" == 1 ]] && [[ "${TRAVIS_PULL_REQUEST}" == "true" ]]
export MULTI_BRANCH=$?

! [[ "${MULTI_BRANCH}" == 1 ]] || [[ "${PRODUCTION_DEPLOY}" == 1 ]]
export DEPLOYMENT=$?