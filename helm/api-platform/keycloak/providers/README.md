# Building Providers

Keycloak comes with a bunch of providers.

To create a custom JavaScript Policy (https://www.keycloak.org/docs/24.0.1/server_development/#_script_providers),
it must be packed in a JAR file.

Build the provider as following:

```shell
zip -r owner-policy.jar owner/*
```
