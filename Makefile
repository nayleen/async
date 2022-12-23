ci: csdiff psalm phpunit cleanup

cleanup:
	@docker-compose down -v 2>/dev/null

composer:
	@docker-compose run --rm php composer validate 2>/dev/null
	@docker-compose run --rm php composer install --quiet --no-cache 2>/dev/null

coverage: composer
	@docker-compose run --rm php php -dxdebug.mode=coverage vendor/bin/phpunit 2>/dev/null

csdiff: composer
	@docker-compose run --rm php php vendor/bin/php-cs-fixer fix --dry-run --diff --verbose 2>/dev/null

csfix: composer
	@docker-compose run --rm php php vendor/bin/php-cs-fixer fix 2>/dev/null

normalize:
	@docker-compose run --rm php composer normalize --quiet 2>/dev/null
	@docker-compose run --rm php php vendor/bin/php-cs-fixer fix 2>/dev/null

phpunit: composer
	@docker-compose run --rm php php -dzend.assertions=1 -dassert.exception=1 -dxdebug.mode=off vendor/bin/phpunit 2>/dev/null

psalm: composer
	@docker-compose run --rm php php -dzend.assertions=1 -dassert.exceptions=1 -dxdebug.mode=off vendor/bin/psalm.phar --show-info=true 2>/dev/null
