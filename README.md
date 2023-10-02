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
    $ docker compose up --wait

You can now go to https://localhost

## What Can I Find In This Demo?

This demo application contains several things you may be interested:

| Feature                                                                                                                                                                                               | Usage                                                                                                                                                                                 |
|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [Bringing your Own Model](https://api-platform.com/docs/distribution/#bringing-your-own-model)                                                                                                        | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22%23%5BApiResource%22&type=code)                                                             |
| [Model Scaffolding](https://api-platform.com/docs/schema-generator/getting-started/#model-scaffolding)                                                                                                | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22%23%5BApiProperty%28types%3A%22&type=code)                                                  |
| [Plugging the Persistence System](https://api-platform.com/docs/distribution/#plugging-the-persistence-system)                                                                                        | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22%23%5BORM%22&type=code)                                                                     |
| [Exposing Enums with API Platform](https://les-tilleuls.coop/blog/exposez-vos-enums-avec-api-platform)                                                                                                | [Search usage](api/src/Enum)                                                                                                                                                          |
| [Validating Data](https://api-platform.com/docs/distribution/#validating-data)                                                                                                                        | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22%23%5BAssert%22&type=code)                                                                  |
| [Configuring Operations](https://api-platform.com/docs/core/operations/)                                                                                                                              | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22operations%3A%22&type=code)                                                                 |
| [Defining Which Operation to Use to Generate the IRI](https://api-platform.com/docs/core/operations/#defining-which-operation-to-use-to-generate-the-iri)                                             | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22itemUriTemplate%3A%22&type=code)                                                            |
| [Subresources](https://api-platform.com/docs/core/subresources/)                                                                                                                                      | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc%2FEntity+%22uriTemplate%3A+%27%2Fbooks%2F%7BbookId%7D%2Freviews%7B._format%7D%27%22&type=code) |
| [Doctrine ORM Filters](https://api-platform.com/docs/core/filters/)                                                                                                                                   | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22%23%5BApiFilter%22&type=code)                                                               |
| [Creating Custom Doctrine ORM Filters](https://api-platform.com/docs/core/filters/#creating-custom-doctrine-orm-filters)                                                                              | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi+%22NameFilter%22+OR+%22app.filter.user.admin.name%22&type=code)                                      |
| [Overriding Default Order](https://api-platform.com/docs/core/default-order/)                                                                                                                         | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22order%3A%22&type=code)                                                                      |
| [Changing the Number of Items per Page Client-side For a Specific Resource](https://api-platform.com/docs/core/pagination/#changing-the-number-of-items-per-page-client-side-for-a-specific-resource) | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22paginationClientItemsPerPage%22&type=code)                                                  |
| [Advanced serialization](https://api-platform.com/docs/core/serialization/)                                                                                                                           | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc+%22%23%5BGroups%22&type=code)                                                                  |
| [User Support](https://api-platform.com/docs/core/user/)                                                                                                                                              | [Search usage](api/src/Entity/User.php)                                                                                                                                               |
| [Custom Doctrine ORM Extension](https://api-platform.com/docs/core/extensions/)                                                                                                                       | [Search usage](api/src/Doctrine/Orm/Extension)                                                                                                                                        |
| [Custom State Processor](https://api-platform.com/docs/core/state-processors/)                                                                                                                        | [Search usage](api/src/State/Processor)                                                                                                                                               |
| [Creating Async APIs using the Mercure Protocol](https://api-platform.com/docs/core/mercure/)                                                                                                         | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc%2FEntity+%22mercure%3A%22&type=code)                                                           |
| [Advanced Authentication and Authorization Rules](https://api-platform.com/docs/core/security/)                                                                                                       | [Search usage](https://github.com/search?q=repo%3Aapi-platform%2Fdemo+path%3Aapi%2Fsrc%2FEntity+%22security%3A%22+OR+%22securityPostDenormalize%3A%22&type=code)                      |
| [API Testing](https://api-platform.com/docs/core/testing/)                                                                                                                                            | [Search usage](api/tests)                                                                                                                                                             |
| [The Admin](https://api-platform.com/docs/distribution/#the-admin)                                                                                                                                    | [Search usage](pwa/pages/admin)                                                                                                                                                       |
| [A Next.js Web App](https://api-platform.com/docs/distribution/#a-nextjs-web-app)                                                                                                                     | [Search usage](pwa)                                                                                                                                                                   |
| [Deploying to a Kubernetes Cluster](https://api-platform.com/docs/deployment/kubernetes)                                                                                                              | [Search usage](helm/api-platform)                                                                                                                                                     |

> Note: this demo application implements [OpenID Connect Specification Support](https://openid.net/developers/specs/)
> (using [Keycloak](https://www.keycloak.org/)). See [usage in API](api/config/packages/security.yaml) and
> [usage in PWA](pwa/pages/api/auth/%5B...nextauth%5D.tsx).

## Contributing

[Read the contributing guide](.github/CONTRIBUTING.md)

## Credits

Created by [Kévin Dunglas](https://dunglas.fr/). Commercial support available
at [Les-Tilleuls.coop](https://les-tilleuls.coop/).
