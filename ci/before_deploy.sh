#!/usr/bin/env bash

source `dirname $0`/auth.sh

# In order to use branch naming buckets see the link below:
# https://stackoverflow.com/questions/39333431/how-to-enable-additional-users-to-create-domain-named-buckets-in-google-cloud-st
gsutil mb -p "${PROJECT_ID}" -l eu gs://"${ADMIN_BUCKET}" || echo "Admin bucket exists"
gsutil mb -p "${PROJECT_ID}" -l eu gs://"${CLIENT_BUCKET}" || echo "Client bucket exists"
