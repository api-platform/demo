#!/usr/bin/env bash

export IMAGE_REPOSITORY="eu.gcr.io/${PROJECT_ID}/${1}";

if [[ "${MULTI_BRANCH}"== 0 ]]
then
    # Build and push the docker images.
    docker build --pull -t "${IMAGE_REPOSITORY}" api --target api_platform_"${1}";
    gcloud docker -- push "${IMAGE_REPOSITORY}";
else
    # Build and push the docker images.
    docker build --pull -t "${IMAGE_REPOSITORY}":"${COMMIT}" api --target api_platform_"${1}";
    gcloud docker -- push "${IMAGE_REPOSITORY}":"${COMMIT}";
fi
