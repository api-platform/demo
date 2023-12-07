#!/bin/bash
cd $(dirname $0)
if [ -z "$TARGET" ]; then
	echo "Missing TARGET=pr-xxx-demo.api-platform.com" 1>&2
	exit 1
fi
docker run \
  --name k6 \
  --rm -i \
  -v $(pwd):/test \
  -w /test \
  -p 5665:5665 \
  -e TARGET=$TARGET \
  ghcr.io/szkiba/xk6-dashboard:latest \
    run \
      --http-debug \
      --out=dashboard \
      ./script.js
