# Get User Data

* Status: accepted
* Deciders: @gregoirehebert, @vincentchalamon

## Context and Problem Statement

When a user downloads a book, a Download object is created. An admin can list all those objects to check all the books
that have been downloaded. For each of them, an admin must see the data of the user (`firstName` and `lastName`).

Users come from an OIDC server.

## Considered Options

A Download object should save the IRI of the user from the OIDC server
(e.g.: `https://demo.api-platform.com/oidc/users/{id}`). Then, the admin client could authenticate on the OIDC API, and
request this IRI to retrieve the user data. This project currently uses [Keycloak](https://keycloak.org/) OIDC server,
which only enables the API for administrators of the OIDC server for security reasons. The admin client would not be
able to request it, as the admin client user is not the same as an administrator of the OIDC server.

Another option would be to create exactly the same users on [Keycloak](https://keycloak.org/) and the API. But what if a
new user is added on [Keycloak](https://keycloak.org/)? It won't be automatically synchronized on the API, some data
might be different.

Last solution would be on the API side. The authentication process is already done by [Keycloak](https://keycloak.org/).
A check is done on the API side thanks to Symfony. If the user is valid and fully authenticated according to this
authenticator and [Keycloak](https://keycloak.org/), we could try to find the user in the database or create it, and
update it if necessary.

## Decision Outcome

The last solution would be the best compromise. Thanks to it, the users on the API will always be synchronized with
[Keycloak](https://keycloak.org/), and we're able to expose an API over the users restricted to admins
(e.g.: `https://demo.api-platform.com/users/{id}`).

## Links

* [Keycloak](https://keycloak.org/)
* [Symfony AccessToken Authenticator](https://symfony.com/doc/current/security/access_token.html)
