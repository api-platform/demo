API Platform Demo
=================

This a demonstration application for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Install
=======

    $ git clone https://github.com/api-platform/demo.git
    $ cd demo
    $ docker-compose up -d

And go to https://localhost

Loading Fixtures
================

    $ docker-compose exec php bin/console hautelook:fixtures:load --no-interaction --no-bundles


What's included ? 
=================

This demo application contains several things you may be interested in.   

Custom data provider
--------------------

Example where a CSV file is exposed like a standard API Platform endpoint.
It also shows how to make this endpoint paginated.

* [Data providers documentation](https://api-platform.com/docs/core/data-providers/)
* [Code in src/DataProvider](src/DataProvider)
