#!/usr/bin/env bash

cd "$(dirname "$0")"/../admin

yarn && REACT_APP_API_ENTRYPOINT=https://"${API_ENTRYPOINT}" yarn build --environment=prod;