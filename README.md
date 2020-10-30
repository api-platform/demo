API Platform Demo
=================

This application is a demonstration for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Install
=======

    $ git clone https://github.com/api-platform/demo.git
    $ cd demo
    $ docker-compose build
    $ docker-compose up -d

Then load the demo fixtures:

    $ docker-compose exec php composer run load-fixtures

You can now go to https://localhost

What can I find in this demo? 
=============================

This demo application contains several things you may be interested.   

Tests
-----

All entities used in this project are thoroughly tested. Each test class extends
the `ApiTestCase`, which contains specific API assertions. It will make your tests
much more straightforward than using the standard `WebTestCase` provided by Symfony. 

* [Tests documentation](https://api-platform.com/docs/core/testing/)
* [Code in api/src/tests/](api/src/tests)

Custom data provider
--------------------

This example shows how to expose a CSV file as a standard API Platform endpoint
It also shows how to make this endpoint paginated.

* [Data providers documentation](https://api-platform.com/docs/core/data-providers/)
* [Code in api/src/DataProvider](api/src/DataProvider)
