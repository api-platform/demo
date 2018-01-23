API Platform Demo
=================

This a demonstration application for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Installation (recommended)
==========================

```shell

$ git clone https://github.com/api-platform/demo.git

$ docker-compose up
```

And go to https://localhost

Installation (manual)
=====================

```shell

$ git clone https://github.com/api-platform/demo.git

# Create a user and a database in PostgreSQL and enter the credentials into .env

$ composer install

$ php bin/console doctrine:schema:update --force
```