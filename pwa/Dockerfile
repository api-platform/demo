#syntax=docker/dockerfile:1


# Versions
FROM docker.io/node:lts AS node_upstream


# Base stage for dev and build
FROM node_upstream AS base

WORKDIR /srv/app

ENV PNPM_HOME="/root/.local/share/pnpm"
ENV PATH="$PNPM_HOME:$PATH"

RUN npm install -g corepack@latest && \
    corepack enable && \
	corepack prepare --activate pnpm@latest && \
	pnpm config -g set store-dir /.pnpm-store

# Next.js collects completely anonymous telemetry data about general usage.
# Learn more here: https://nextjs.org/telemetry
# Delete the following line in case you want to enable telemetry during dev and build.
ENV NEXT_TELEMETRY_DISABLED=1


# Development image
FROM base AS dev

EXPOSE 3000
ENV PORT=3000
ENV HOSTNAME=localhost

CMD ["sh", "-c", "pnpm install; node migrate.mjs; pnpm dev"]


FROM base AS builder

COPY --link pnpm-lock.yaml ./
RUN pnpm fetch --prod

COPY --link . .

# https://nextjs.org/docs/app/building-your-application/configuring/environment-variables#bundling-environment-variables-for-the-browser
ARG NEXT_PUBLIC_OIDC_SERVER_URL

RUN	pnpm install --frozen-lockfile --offline --prod && \
	pnpm run build


# Production image, copy all the files and run next
FROM node_upstream AS prod

WORKDIR /srv/app

ENV NODE_ENV=production
# Delete the following line in case you want to enable telemetry during runtime.
ENV NEXT_TELEMETRY_DISABLED=1

RUN addgroup --system --gid 1001 nodejs; \
	adduser --system --uid 1001 nextjs

COPY --from=builder --link /srv/app/public ./public

# Set the correct permission for prerender cache
RUN mkdir .next; \
	chown nextjs:nodejs .next

# Automatically leverage output traces to reduce image size
# https://nextjs.org/docs/advanced-features/output-file-tracing
COPY --from=builder --link --chown=1001:1001 /srv/app/.next/standalone ./
COPY --from=builder --link --chown=1001:1001 /srv/app/.next/static ./.next/static
COPY --from=builder --link --chown=1001:1001 /srv/app/migrate.mjs ./

USER nextjs

EXPOSE 3000

ENV PORT=3000
ENV HOSTNAME="0.0.0.0"

CMD ["sh", "-c", "node migrate.mjs && node server.js"]
