API Platform Demo
=================

This a demonstration application for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Install
=======

$ docker-compose up # Run the containers
$ docker-compose exec app bin/console doctrine:schema:create # Create tables
$ docker-compose exec app bin/console hautelook:fixtures:load # Load fixtures
