#!/usr/bin/env bash

docker-compose up -d
helm lint api/helm/api/
sleep 20
docker-compose exec php composer install -o -n
docker-compose exec php bin/console security:check
docker-compose exec php bin/console doctrine:schema:validate --skip-sync
docker-compose exec php bin/console doctrine:schema:drop --force
docker-compose exec php bin/console doctrine:schema:create
docker-compose exec php bin/console hautelook:fixtures:load -n
docker-compose exec php bin/console doctrine:schema:drop --env=test --force
docker-compose exec php bin/console cache:warmup --env=test
docker-compose exec php bin/behat --format=progress
curl -s http://localhost # Client
curl -s http://localhost:81 # Admin
curl -s http://localhost:8080 # API
curl -s http://localhost:8081 # Varnish
curl -k -s https://localhost # Client (HTTP/2)
curl -k -s https://localhost:444 # Admin (HTTP/2)
curl -k -s https://localhost:8443 # API (HTTP/2)
curl -k -s https://localhost:8444 # Varnish (HTTP/2)
