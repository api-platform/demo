#!/usr/bin/env bash

# Choose the CI you want to run the deployments.
# Both CI will make tests but only the one specified will deploy.
# Current available choices are travis and circleci.
export CURRENT_CI='travis'
