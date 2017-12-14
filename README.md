API Platform Demo
=================

This a demonstration application for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Installation (recommended)
==========================

```shell

$ git clone https://github.com/api-platform/demo.git

# Copy .env.dist to .env and custom the envrionment vars

$ docker-compose up
```

Installation (manual)
=====================

```shell

$ git clone https://github.com/api-platform/demo.git

# Create a user and a database in MySQL and enter the credentials during composer install

# Copy .env.dist to .env and custom the envrionment vars

$ composer install

$ php bin/console doctrine:schema:update --force
```