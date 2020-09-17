API Platform Demo
=================

This a demonstration application for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Install
=======

    $ git clone https://github.com/api-platform/demo.git
    $ cd demo
    $ docker-compose up

And go to https://localhost.

Loading Fixtures
================

    $ docker-compose exec php bin/console hautelook:fixtures:load --no-interaction
