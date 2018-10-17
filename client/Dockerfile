FROM node:11.5-alpine

RUN mkdir -p /usr/src/client

ARG PUPPETEER_VERSION=1.10.0

WORKDIR /usr/src/client

RUN apk add --no-cache ca-certificates python bash make g++ chromium; \
    apk add --no-cache --virtual .build-deps openssl; \
    yarn global add @api-platform/client-generator puppeteer@${PUPPETEER_VERSION}; \
    yarn cache clean

ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser
ENV NODE_PATH="/usr/local/share/.config/yarn/global/node_modules:${NODE_PATH}"

RUN openssl genrsa -des3 -passout pass:NotSecure -out cert.pass.key 2048; \
    openssl rsa -passin pass:NotSecure -in cert.pass.key -out cert.key; \
    openssl req -new -passout pass:NotSecure -key cert.key -out cert.csr -subj '/C=SS/ST=SS/L=Gotham City/O=API Platform Dev/CN=localhost'; \
    openssl x509 -req -sha256 -days 365 -in cert.csr -signkey cert.key -out cert.crt; \
    apk del .build-deps

# Prevent the reinstallation of node modules at every changes in the source code
COPY package.json yarn.lock ./
RUN yarn install --pure-lockfile

COPY . ./

EXPOSE 3000
EXPOSE 5000

CMD yarn start
