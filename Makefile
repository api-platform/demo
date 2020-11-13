DC=docker-compose
CONTAINER=php
DATABASE_CONTAINER=database
EXEC=$(DC) exec $(CONTAINER)
PHP = php
CON = $(PHP) bin/console
AWK := $(shell command -v awk 2> /dev/null)

.DEFAULT_GOAL := help
.PHONY: help

help: ## Show this help
ifndef AWK
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'
else
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
endif

##
## Project setup
##---------------------------------------------------------------------------
.PHONY: install update vendor-install vendor-update
.PRECIOUS: .env docker-compose.override.yml

install: ## Process all step in order to setup the projects
install: up vendor-install db-reset

update: ## Update the project
update: up vendor-install db-migrate

vendor-install:
	$(EXEC) composer install --no-suggest --no-progress

vendor-update:
	$(EXEC) composer update

##
## Database
##---------------------------------------------------------------------------
.PHONY: db-reset db-create db-drop db-make-migration db-migrate db-schema-validate db-schema-drop db-schema-update db-fixtures db-test

db-reset: ## Reset database
db-reset: db-drop db-create db-migrate

db-create: ## Create database
	$(EXEC) $(CON) doctrine:database:create --if-not-exists

db-drop: ## Drop database
	$(EXEC) $(CON) doctrine:database:drop --force --if-exists

db-gen-migration: ## Drop database
	$(EXEC) $(CON) doctrine:migration:diff

db-make-migration: ## Migrate database schema to the latest available version
	$(EXEC) $(CON) make:migration

db-migrate: ## Migrate database schema to the latest available version
	$(EXEC) $(CON) doctrine:migrations:migrate -n

db-schema-validate: ## Validate the mapping files
	$(EXEC) $(CON) doctrine:schema:validate

db-schema-drop: ## Executes (or dumps) the SQL needed to drop the current database schema
	$(EXEC) $(CON) doctrine:schema:drop --force

db-schema-update: ## Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata
	$(EXEC) $(CON) doctrine:schema:update --force

db-fixtures: ## Reset the database fixtures
	$(EXEC) $(CON) hautelook:fixtures:load --purge-with-truncate -q

##
## TESTS
##

unit-tests: ## Run unit tests
	$(EXEC) bin/phpunit

unit-tests-coverage: ## Run functional tests
	$(EXEC) bin/phpunit --coverage-html public/coverage

behat-tests: ## Run behat tests
	$(EXEC) vendor/bin/behat

##
## Tools
##---------------------------------------------------------------------------

cc: ## Clear and warm up the cache in dev env
cc:
	$(EXEC) $(CON) cache:clear --no-warmup
	$(EXEC) $(CON) cache:warmup
.PHONY: cc

##
## Docker
##---------------------------------------------------------------------------
.PHONY: docker-files up down clear perm bash mysql-bash cs-fix-dry cs-fix
.PRECIOUS: .env docker-compose.override.yml

docker-files: .env docker-compose.override.yml

# .env: .env.dist
# 	@if [ -f .env ]; \
# 	then\
# 		echo '\033[1;41m/!\ The .env.dist file has changed. Please check your .env file (this message will not be displayed again).\033[0m';\
# 		touch .env;\
# 		exit 1;\
# 	else\
# 		echo cp .env.dist .env;\
# 		cp .env.dist .env;\
# 	fi

docker-compose.override.yml: docker-compose.yml
	@if [ -f docker-compose.override.yml ]; \
	then\
		echo '\033[1;41m/!\ The docker-compose.yml file has changed. Please check your docker-compose.override.yml file (this message will not be displayed again).\033[0m';\
		touch docker-compose.override.yml;\
		exit 1;\
	fi

up: ## Mount the containers
up: docker-files
	$(DC) up -d

clear:  ## Remove everything: the cache, the logs, the sessions
clear: clear-files down

clear-files: docker-files perm
	-$(EXEC) rm -rf var/cache/*
	-$(EXEC) rm -rf var/sessions/*
	-$(EXEC) rm -rf var/logs/*

down: ## Stops, remove the containers and their volumes
down: docker-files
	$(DC) down -v --remove-orphans

perm: docker-files
	-$(EXEC) chmod -R u+rwX,go+rX,go-w var

bash: ## Access the api container via shell
	$(DC) exec $(CONTAINER)  sh


jwt: docker-files perm
	-$(EXEC) mkdir -p config/jwt
	-$(EXEC) php -r "require'vendor/autoload.php';file_put_contents('passphrase.txt',\Symfony\Component\Yaml\Yaml::parse(file_get_contents('config/packages/lexik_jwt_authentication.yaml'))['lexik_jwt_authentication']['pass_phrase']);"
	-$(EXEC) openssl genpkey -out ./config/jwt/private.pem -aes256 -pass file:passphrase.txt -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	-$(EXEC) openssl pkey -in ./config/jwt/private.pem -passin file:passphrase.txt -out config/jwt/public.pem -pubout
	-$(EXEC) rm -f passphrase.txt
	-$(EXEC) chown -R www-data:www-data ./config/jwt

build: docker-files
	-$(EXEC) rm -rf config/jwt/*
	-$(DC) build
	-make jwt
	-$(EXEC) chmod -R 777 ./var/*

build-no-chache: docker-files
	-$(EXEC) rm -rf config/jwt/*
	-$(DC) build --no-cache
	-make jwt
	-$(EXEC) chmod -R 777 ./var/*
	-docker-compose up -d --force-recreate
	-make db-drop
	-make db-create
	-make db-migrate


mysql-bash: ## Access the database container via shell
	$(DC) exec $(DATABASE_CONTAINER) bash

cs-fix-dry:  ## Runs the CS fixer in "dry-run" mode to fix the project coding style
cs-fix-dry: docker-files vendor
	$(EXEC) vendor/bin/php-cs-fixer fix src -vvv --config=.php_cs --cache-file=.php_cs.cache --dry-run

cs-fix:  ## Runs the CS fixer to fix the project coding style
cs-fix: docker-files vendor
	$(EXEC) vendor/bin/php-cs-fixer fix src -vvv --config=.php_cs --cache-file=.php_cs.cache
