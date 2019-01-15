#!/usr/bin/env bash

set -e

# Ensure required environment variables are set
ENV_VARS=(
    APP_SECRET
    CI_SERVICE_ACCOUNT
    CLOUDFLARE_CERT
    DEV_DNS
    GCR_API_KEY
    PROD_DNS
    PROD_DATABASE_URL
    PROD_MAILER_URL
    PROJECT_ID
    CLIENT_REPOSITORY
    CLUSTER_NAME
    CURRENT_CI
    DEPLOYMENT_BRANCH
#    DEPLOY_REPOSITORY
#    DOCKER_REPOSITORY
    MULTI_BRANCH
    NGINX_REPOSITORY
    PHP_REPOSITORY
    REPOSITORY
    VARNISH_REPOSITORY
#    MERCURE_JWT
	PROD_CLIENT_NAME
	PROD_ADMIN_NAME
	DEV_CLIENT_NAME
	DEV_ADMIN_NAME
    ADMIN_EMAIL
    PROD_API_DNS
    DEV_API_DNS
)
for i in "${ENV_VARS[@]}"; do
    if [[ -z "${!i}" ]]; then
        echo "$i environment variable must be defined."
        exit 1
    fi
done

# Build environment variables
# If a tag is created on the main repository, deploy to production using DEPLOYMENT_BRANCH (for example: `master:1.0.0`)
# If it's a pull request, deploy to PR number with the commit sha1 (for example: `pr-25:d6cd1e2bd19e03a81132a23b2025920577f84e37`)
# Otherwise, deploy to current branch name with the commit sha1 (for example: `dev:d6cd1e2bd19e03a81132a23b2025920577f84e37`)
if [[ $CURRENT_CI == "circleci" && $CIRCLECI == true ]]; then
# If Current ci is set as circle and the current running ci is circle

	# Set IS_PROD_DEPLOY value
	export IS_PROD_DEPLOY=false
    if [[ -z $CIRCLE_PULL_REQUEST && ! -z $CIRCLE_TAG && $CIRCLE_PROJECT_REPONAME == $REPOSITORY ]]; then
        export IS_PROD_DEPLOY=true

    fi

	# Set the followinf values: DEPLOY_TAG; DEPLOY_NAMESPACE; CLIENT_NAME; ADMIN_NAME; API_DNS; ZONE
	[[ $IS_PROD_DEPLOY = true ]] && export DEPLOY_TAG=$CIRCLE_TAG || export DEPLOY_TAG=$CIRCLE_SHA1
	[[ $IS_PROD_DEPLOY = true ]] && export DEPLOY_NAMESPACE=$DEPLOYMENT_BRANCH || export DEPLOY_NAMESPACE="$(echo $CIRCLE_BRANCH | sed 's/\//-/g')"
	[[ $IS_PROD_DEPLOY = true ]] && export CLIENT_NAME=$PROD_CLIENT_NAME || [[ $MULTI_BRANCH = 0 ]] && export $CLIENT_NAME=$DEV_CLIENT_NAME || export CLIENT_NAME=${DEPLOY_NAMESPACE}-${PROD_CLIENT_NAME}
	[[ $IS_PROD_DEPLOY = true ]] && export ADMIN_NAME=$PROD_ADMIN_NAME || [[ $MULTI_BRANCH = 0 ]] && export $ADMIN_NAME=$DEV_ADMIN_NAME || export ADMIN_NAME=${DEPLOY_NAMESPACE}-${PROD_ADMIN_NAME}
	[[ $IS_PROD_DEPLOY = true ]] && export API_DNS=$PROD_API_DNS || [[ $MULTI_BRANCH = 0 ]] && export API_DNS=$DEV_API_DNS || export API_DNS=${DEPLOY_NAMESPACE}-$PROD_API_DNS
	[[ $IS_PROD_DEPLOY = true ]] && export ZONE=$PROD_DNS || export ZONE=$DEV_DNS

elif [[ $CURRENT_CI == "travis" && $TRAVIS == true ]]; then

# If Current ci is set as circle and the current running ci is circle

	# Set IS_PROD_DEPLOY value
	export IS_PROD_DEPLOY=false
    if [[ -z $TRAVIS_PULL_REQUEST && ! -z $TRAVIS_TAG && $TRAVIS_REPO_SLUG == $REPOSITORY ]]; then
        export IS_PROD_DEPLOY=true

    fi

	# Set DEPLOY_TAG and DEPLOY_NAMESPACE value
	[[ $IS_PROD_DEPLOY = true ]] && export DEPLOY_TAG=$TRAVIS_TAG || export DEPLOY_TAG=$TRAVIS_COMMIT
	[[ $IS_PROD_DEPLOY = true ]] && export DEPLOY_NAMESPACE=$DEPLOYMENT_BRANCH || export DEPLOY_NAMESPACE="$(echo $TRAVIS_BRANCH | sed 's/\//-/g')"
	[[ $IS_PROD_DEPLOY = true ]] && export CLIENT_NAME=$PROD_CLIENT_NAME || [[ $MULTI_BRANCH = 0 ]] && export $CLIENT_NAME=$DEV_CLIENT_NAME || export CLIENT_NAME=${DEPLOY_NAMESPACE}-${PROD_CLIENT_NAME}
	[[ $IS_PROD_DEPLOY = true ]] && export ADMIN_NAME=$PROD_ADMIN_NAME || [[ $MULTI_BRANCH = 0 ]] && export $ADMIN_NAME=$DEV_ADMIN_NAME || export ADMIN_NAME=${DEPLOY_NAMESPACE}-${PROD_ADMIN_NAME}
	[[ $IS_PROD_DEPLOY = true ]] && export ADMIN_NAME=$PROD_ADMIN_NAME || [[ $MULTI_BRANCH = 0 ]] && export $ADMIN_NAME=$DEV_ADMIN_NAME || export ADMIN_NAME=${DEPLOY_NAMESPACE}-${PROD_ADMIN_NAME}
	[[ $IS_PROD_DEPLOY = true ]] && export API_DNS=$PROD_API_DNS || [[ $MULTI_BRANCH = 0 ]] && export API_DNS=$DEV_API_DNS || export API_DNS=${DEPLOY_NAMESPACE}-$PROD_API_DNS
	[[ $IS_PROD_DEPLOY = true ]] && export ZONE=$PROD_DNS || export ZONE=$DEV_DNS

else
    echo "Your CI is not supported."
    exit 1
fi
