API Platform Demo
=================

This application is a demonstration for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

Install
=======

    $ git clone https://github.com/api-platform/demo.git
    $ cd demo
    $ docker-compose up -d

Then load the demo fixtures:

    $ docker-compose exec php composer load-fixtures

You can now go to https://localhost

What can I find in this demo? 
=============================

This demo application contains features you may be interested in:

API Testing
-----------

All entities used in this project are thoroughly tested. Each test class extends
the `ApiTestCase`, which contains specific API assertions. It will make your tests
much more straightforward than using the standard `WebTestCase` provided by Symfony.

* [Tests documentation](https://api-platform.com/docs/core/testing/)
* [Code in api/tests/](api/tests)

Custom data provider
--------------------

This example shows how to expose a CSV file as a standard API Platform endpoint.
It also shows how to make this endpoint paginated with an extension.

* [Data providers documentation](https://api-platform.com/docs/core/data-providers/)
* [Code in api/src/DataProvider](api/src/DataProvider)

Overriding the OpenAPI Specification
------------------------------------

This example shows how to document an API endpoint that isn't handled by API Platform.
This "legacy" endpoint is listed and testable like the other ones thanks to the
Swagger interface.
 
* [Overriding the OpenAPI Specification documentation](https://api-platform.com/docs/core/swagger/#overriding-the-openapi-specification)
* [Code in api/src/Swagger/SwaggerDecorator.php](api/src/Swagger/SwaggerDecorator.php)

Custom Doctrine ORM Filter
--------------------------

This example shows how to implement a custom API filter using Doctrine. It's a generic
filter that is applied by default when getting a collection but one can disable
it with a GET parameter.

* [Creating Custom Doctrine ORM Filters documentation](https://api-platform.com/docs/core/filters/#creating-custom-doctrine-orm-filters)
* [Code in api/src/Filter](api/src/Filter)

Contributing
============

* [Read the contributing guide](.github/CONTRIBUTING.md)
