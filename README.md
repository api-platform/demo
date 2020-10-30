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

This demo application contains several things you may be interested in.   

Tests
-----

All entities used in this project are thoroughly tested. Each test class extends
he `ApiTestCase`, which contains specific "API" assertions. It will make your tests
much more straightforward than using the standard `WebTestCase` provided by Symfony. 

* [Tests documentation](https://api-platform.com/docs/core/testing/)
* [Code in test/](src/tests)

Custom data provider
--------------------

This example shows how to expose a CSV file as a standard API Platform endpoint
It also shows how to make this endpoint paginated.

* [Data providers documentation](https://api-platform.com/docs/core/data-providers/)
* [Code in src/DataProvider](src/DataProvider)

Contributing 
============

If you see something that is wrong, a bug or something that could be improved, 
you are welcome to contribute. 

Prepare the test environment:

    $ docker-compose exec php composer run prepare-test-env

Then run the tests:

    $ docker-compose exec php composer run tests

If tests are green (you may see some deprecations warnings), you are ready to contribute!
Don't forget to modify the `.github/workflows/test.yml` file if needed.
