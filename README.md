API Platform Demo
=================

This a demonstration application for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Install
=======

    $ git clone https://github.com/api-platform/demo.git
    $ docker-compose up

And go to https://localhost.

Loading Fixtures
================

    $ docker-compose exec php bin/console hautelook:fixtures:load

Deploy on Kubernetes (GCP)
==========================

Everything is pre-configured. Edit the `ci/.env` file to set your project parameters, and declare the following secured
environment variables in your CI:

 * `PROJECT_ID`: GCP project id (i.e: `api-platform-demo-123456`)
 * `CI_SERVICE_ACCOUNT`: GCP service account
 * `CI_SERVICE_ACCOUNT_KEY`: GCP service account key
 * `CF_API_KEY`: Cloudflare API key
 * `CF_API_EMAIL`: Cloudflare email

**Important: do not check "_Display value in build log_"!**

Deployment will be done automatically by the CI.
