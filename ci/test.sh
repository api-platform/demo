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
docker-compose exec php bin/behat
curl http://localhost
curl http://localhost:81
curl http://localhost:8080
curl http://localhost:8081
curl -k https://localhost
curl -k https://localhost:444
curl -k https://localhost:8443
curl -k https://localhost:8444