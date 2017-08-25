API Platform Demo
=================

This a demonstration application for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Installation
============

```shell

$ git clone https://github.com/api-platform/demo.git

$ composer install

# Create a user and a database in MySQL and enter the credentials during composer install
$ php bin/console doctrine:schema:update --force
```