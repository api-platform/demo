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

Then load the demo fixtures:

    $ docker-compose exec php composer run load-fixtures

You can now go to https://localhost

What's included ? 
=================

This demo application contains several things you may be interested in.   

Tests
-----

All entities used in this project are fully tested. Each test class extends the
`ApiTestCase` which contains specific "API" assertions. It will make your tests
much more straightforward than using the standard `WebTestCase` provided by Symfony. 

* [Tests documentation](https://api-platform.com/docs/core/testing/)
* [Code in test/](src/tests)

Custom data provider
--------------------

This is an example where a CSV file is exposed like a standard API Platform endpoint.
It also shows how to make this endpoint paginated.

* [Data providers documentation](https://api-platform.com/docs/core/data-providers/)
* [Code in src/DataProvider](src/DataProvider)

Contributing 
============

Seen something that is wrong? A bug? Something that could be improved? Every contributions
are welcome. 

Prepare the test environment:

    $ docker-compose exec php composer run prepare-test-env

Then run the tests:

    $ docker-compose exec php composer run tests

If tests are green (you may see some deprecations warnings), you are ready to contribute!
Don't forget to modify the `.github/workflows/test.yml` file if needed.
