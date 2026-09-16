#syntax=docker/dockerfile:1.4




# Versions
FROM quay.io/keycloak/keycloak:26.7 AS keycloak_upstream

# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# Builder
FROM keycloak_upstream AS keycloak_builder

WORKDIR /opt/keycloak

COPY --link --chown=keycloak:keycloak --chmod=644 providers/owner-policy.jar /opt/keycloak/providers/owner-policy.jar

# Since Keycloak 26.6, deploying the JavaScript-based "Owner" policy of owner-policy.jar
# requires the scripts feature to be enabled explicitly.
RUN /opt/keycloak/bin/kc.sh build \
    --db=postgres \
    --http-relative-path=/oidc/ \
    --health-enabled=true \
    --metrics-enabled=true \
    --features=scripts

# Keycloak image
FROM keycloak_upstream AS keycloak

COPY --from=keycloak_builder /opt/keycloak/ /opt/keycloak/

COPY --link --chown=1000:0 themes/api-platform-demo /opt/keycloak/themes/api-platform-demo

ENTRYPOINT ["/opt/keycloak/bin/kc.sh"]
