# HTTP Cache with Souin

* Status: accepted
* Deciders: @vincentchalamon

## Context and Problem Statement

The demo application exposes public HTTP cache headers (`Cache-Control: public`) on API responses, but no cache layer actually stores and serves cached responses. Every request hits the PHP backend, even for unchanged resources. The demo needs an HTTP cache that integrates natively with FrankenPHP (Caddy) and API Platform's automatic cache invalidation, while also serving as an example for distributed deployments.

## Considered Options

**Varnish** is the traditional HTTP cache for API Platform projects. It is battle-tested and well-documented. However, it requires a separate service (reverse proxy) in front of the application, adding operational complexity. It does not integrate natively with Caddy/FrankenPHP — the two processes must be orchestrated separately, and Varnish uses its own configuration language (VCL).

**Souin** is an HTTP cache module written in Go that compiles directly into Caddy as a plugin. Since FrankenPHP is built on Caddy, Souin runs in the same process — no additional service, no network hop for cache lookups. It is RFC 7234 compliant, supports surrogate key invalidation (which API Platform uses natively via `SouinPurger`), and offers pluggable storage backends. The trade-off is that it requires a custom FrankenPHP build via `xcaddy`.

**CloudFlare / CDN** would offload caching entirely to an external provider. This works well in production but cannot be demonstrated locally, and ties the demo to a specific vendor.

## Decision Outcome

We chose **Souin** because it integrates natively with the existing FrankenPHP/Caddy stack — no extra service to manage, no network hop, and built-in support in API Platform via `api_platform.http_cache.purger.souin`. This makes the demo a realistic showcase of HTTP caching with automatic invalidation.

For storage, we use a **two-tier architecture: Otter (L1) + Redis (L2)**. Otter is an in-memory cache local to each process, providing the fastest possible read path. Redis is a shared, persistent store that serves two purposes: distributing the cache across multiple pods in a Kubernetes deployment, and surviving process restarts so the cache is not lost on every deploy. This combination demonstrates a production-ready pattern for distributed projects, which is a key goal of the demo application.

The cache is compiled into all images (dev and prod) but **only activated in production** via environment variables. This keeps the developer experience unchanged while ensuring the production setup is always tested with the same binary.

## Links

* [Souin — HTTP cache module for Caddy](https://github.com/darkweak/souin)
* [Souin Caddy plugin documentation](https://docs.souin.io/docs/middlewares/caddy/)
* [API Platform HTTP Cache documentation](https://api-platform.com/docs/core/performance/)
* [FrankenPHP custom builds](https://frankenphp.dev/docs/compile/)
