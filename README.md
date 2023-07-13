<h1 align="center">
    <a href="https://api-platform.com">
        <img width="400" height="400" src="https://api-platform.com/images/zeus.svg" alt="API Platform">
        <br />API Platform - Demo
    </a>
</h1>

This application is a demonstration for the [API Platform Framework](https://api-platform.com/).
Try it online at <https://demo.api-platform.com>.

[![GitHub Actions](https://github.com/api-platform/demo/workflows/CI/badge.svg)](https://github.com/api-platform/demo/actions?workflow=CI)
[![GitHub Actions](https://github.com/api-platform/demo/workflows/CD/badge.svg)](https://github.com/api-platform/demo/actions?workflow=CD)

## Install

[Read the official "Getting Started" guide](https://api-platform.com/docs/distribution/).

    $ git clone https://github.com/api-platform/demo.git
    $ cd demo
    $ docker compose up -d

You can now go to https://localhost

## What Can I Find In This Demo? 

This demo application contains several things you may be interested.   

### API Testing

All entities used in this project are thoroughly tested. Each test class extends
the `ApiTestCase`, which contains specific API assertions. It will make your tests
much more straightforward than using the standard `WebTestCase` provided by Symfony.

* [Documentation](https://api-platform.com/docs/core/testing/)
* [Code in api/tests/](api/tests)

### Extensions

The `Download` collection is restricted to the current user, except for admin users. The Doctrine Query is overridden
using a Doctrine Extension.

* [Documentation](https://api-platform.com/docs/core/extensions/)
* [Code in api/src/Doctrine/Orm/Extension](api/src/Doctrine/Orm/Extension)

### State Processors

The `Download` and `Review` entities require dynamic properties set before save: a date of creation, and a link to the
current user. This is done using State Processors.

* [Documentation](https://api-platform.com/docs/core/state-processors/)
* [Code in api/src/State/Processor](api/src/State/Processor)

## Contributing

[Read the contributing guide](.github/CONTRIBUTING.md)

## Credits

Created by [Kévin Dunglas](https://dunglas.fr/). Commercial support available at [Les-Tilleuls.coop](https://les-tilleuls.coop/).
