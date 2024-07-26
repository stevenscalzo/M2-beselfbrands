PHP_CONTAINER=beselfbrands_php
DB_CONTAINER=beselfbrands_db
NGINX_CONTAINER=beselfbrands_nginx

default:
	@printf "$$HELP"

# Docker commands
docker-build:
	docker-compose -f .docker/docker-compose.yml build

docker-composer-install:
	@docker exec -it $(PHP_CONTAINER) bash -c "composer install --prefer-source --no-interaction"

composer-install:
	@docker exec -it $(PHP_CONTAINER) bash -c "composer install --no-interaction"

docker-down:
	docker-compose -f .docker/docker-compose.yml down

docker-start:
	docker-compose -f .docker/docker-compose.yml up -d

docker-stop:
	docker-compose -f .docker/docker-compose.yml stop

docker-ssh-php:
	@docker exec -it $(PHP_CONTAINER) bash

docker-ssh-web:
	@docker exec -it $(NGINX_CONTAINER) bash

docker-clean-images:
	docker -f .docker/docker-compose.yml rmi $(docker images | tail -n +2 | awk '$1 == "<none>" {print $'3'}')

docker-clean-all:
	 docker-compose -f .docker/docker-compose.yml down && docker rmi $(PHP_CONTAINER):latest $(DB_CONTAINER):latest $(NGINX_CONTAINER):latest

docker-set-dev-permissions:
	@docker exec -it $(PHP_CONTAINER) bash -c 'echo "Set permissions" && chmod 777 -R ./{var/,generated/,pub/static/,pub/media/,app/etc/,vendor/}'

only-di-compile:
	@docker exec -it $(PHP_CONTAINER) bash -c 'php bin/magento setup:di:compile'

only-cache-flush:
	@docker exec -it $(PHP_CONTAINER) bash -c 'php bin/magento cache:flush'

cache-flush: only-cache-flush docker-set-dev-permissions

di-compile:  only-di-compile cache-flush docker-set-dev-permissions

reindex:
	@docker exec -it $(PHP_CONTAINER) bash -c 'php bin/magento indexer:reset; php bin/magento indexer:reindex'

setup-upgrade:
	@docker exec -it $(PHP_CONTAINER) bash -c 'php bin/magento setup:upgrade'

compile-beselfbrands-theme:
	@docker exec $(PHP_CONTAINER) bash -c 'cd magento/vendor/snowdog/frontools && gulp styles --theme momfuncompany'

compile-all-themes:
	@docker exec $(PHP_CONTAINER) bash -c 'cd magento/vendor/snowdog/frontools && gulp styles'

npm-install-natives:
	@docker exec $(PHP_CONTAINER) bash -c 'cd magento/vendor/snowdog/frontools && npm install && npm install -i natives'

delete-vendors:
	@docker exec $(PHP_CONTAINER) bash -c 'rm -rf vendor/*'

initialize-project: delete-vendors composer-install setup-upgrade only-di-compile docker-set-dev-permissions

only-set-production-mode:
	@docker exec $(PHP_CONTAINER) bash -c 'php bin/magento deploy:mode:set production --skip-compilation'

only-set-dev-mode:
	@docker exec $(PHP_CONTAINER) bash -c 'php bin/magento deploy:mode:set developer'

delete-static-content:
	@docker exec $(PHP_CONTAINER) bash -c 'rm -rf magento/pub/static/*'

only-static-content-deploy:
	@docker exec $(PHP_CONTAINER) bash -c 'php bin/magento setup:static-content:deploy -f'

docker-remove-git-hooks:
	@docker exec -it $(PHP_CONTAINER) bash -c 'echo "Remove pre-commit and commit-msg Git hooks" && rm .git/hooks/pre-commit .git/hooks/commit-msg'

set-production-mode: only-set-production-mode delete-static-content only-static-content-deploy cache-flush docker-set-dev-permissions

set-dev-mode: only-set-dev-mode docker-set-dev-permissions cache-flush

define HELP
# Docker
	- default:
	- docker-build:
	- docker-composer-install:
	- composer-install:
	- docker-stop
	- docker-down:
	- docker-start:
	- docker-ssh-php:
	- docker-ssh-web:
	- docker-clean-images:
	- docker-clean-all:
	- docker-set-dev-permissions:
	- cache-flush:
	- di-compile:
	- reindex:
	- setup-upgrade:
	- compile-beselfbrands-theme:
	- delete-vendors:
	- initialize-project:


endef

export HELP
