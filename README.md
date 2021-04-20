<h1 align="center"><a href="https://api-platform.com"><img src="https://api-platform.com/logo-250x250.png" alt="API Platform"></a></h1>

This application is a demonstration for the [API Platform Framework](https://api-platform.com).
Try it online at <https://demo.api-platform.com>.

[![GitHub Actions](https://github.com/api-platform/api-platform/workflows/CI/badge.svg)](https://github.com/api-platform/api-platform/actions?workflow=CI)
[![GitHub Actions](https://github.com/api-platform/api-platform/workflows/CD/badge.svg)](https://github.com/api-platform/api-platform/actions?workflow=CD)

## Install

[Read the official "Getting Started" guide](https://api-platform.com/docs/distribution).

    $ git clone https://github.com/api-platform/demo.git
    $ cd demo
    $ docker-compose up -d

Then load the demo fixtures:

    $ docker-compose exec php composer load-fixtures

You can now go to https://localhost

## What can I find in this demo? 

This demo application contains several things you may be interested.   

### API Testing

All entities used in this project are thoroughly tested. Each test class extends
the `ApiTestCase`, which contains specific API assertions. It will make your tests
much more straightforward than using the standard `WebTestCase` provided by Symfony.

* [Tests documentation](https://api-platform.com/docs/core/testing/)
* [Code in api/tests/](api/tests)

### Custom data provider

This example shows how to expose a CSV file as a standard API Platform endpoint.
It also shows how to make this endpoint paginated with an extension.

* [Data providers documentation](https://api-platform.com/docs/core/data-providers/)
* [Code in api/src/DataProvider](api/src/DataProvider)

### Overriding the OpenAPI Specification

This example shows how to document an API endpoint that isn't handled by API Platform.
This "legacy" endpoint is listed and testable like the other ones thanks to the
Swagger interface.
 
* [Overriding the OpenAPI Specification documentation](https://api-platform.com/docs/core/openapi/#overriding-the-openapi-specification)
* [Code in api/src/OpenApi/OpenApiFactory.php](api/src/OpenApi/OpenApiFactory.php)

## Contributing

[Read the contributing guide](.github/CONTRIBUTING.md)

## Credits

Created by [Kévin Dunglas](https://dunglas.fr). Commercial support available at [Les-Tilleuls.coop](https://les-tilleuls.coop).
