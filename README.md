API Platform Demo
=================

This a demonstration application for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Install
=======

    $ git clone https://github.com/api-platform/demo.git
    $ cd demo
    $ docker-compose build
    $ docker-compose up -d

And go to https://localhost

Loading Fixtures
================

    $ docker-compose exec php composer load-fixtures


What's included ? 
=================

This demo application contains several things you may be interested in.   

Tests
-----

All entities used in this project are fully tested. Each test class extends the
`ApiTestCase` which contains specific assertions for writing your tests. It will
make your tests much more straightforward than using the standard `WebTestCase`
provided by Symfony. 

* [Tests documentation](https://api-platform.com/docs/core/testing/)
* [Code in tests/](src/tests)

Custom data provider
--------------------

Example where a CSV file is exposed like a standard API Platform endpoint.
It also shows how to make this endpoint paginated.

* [Data providers documentation](https://api-platform.com/docs/core/data-providers/)
* [Code in src/DataProvider](src/DataProvider)
