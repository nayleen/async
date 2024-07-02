DEPENDENCY_VERSIONS ?= stable

.DEFAULT_GOAL := help
.PHONY: cleanup check-style coverage fix-style shell static tests update

-include Makefile.local

check-style: vendor ## validate code against styleguide
	@docker compose run --rm php php vendor/bin/php-cs-fixer fix --dry-run --diff --verbose 2>/dev/null

ci: check-style static tests cleanup ## run continuous integration checks

coverage: vendor ## generate code coverage report
	@docker compose run --rm -eXDEBUG_MODE=coverage php php vendor/bin/phpunit --coverage-html=coverage/ 2>/dev/null

fix-style: vendor ## reformat code against styleguide
	@docker compose run --rm php php vendor/bin/php-cs-fixer fix 2>/dev/null

shell: ## enter dev shell
	@docker compose run --rm php bash 2>/dev/null

static: vendor ## run static analysis
	@docker compose run --rm php php vendor/bin/phpstan 2>/dev/null

tests: vendor ## run tests
	@docker compose run --rm php php vendor/bin/phpunit 2>/dev/null

update: ## update dependencies
	@docker compose pull --quiet 2>/dev/null
	@docker compose run --rm php composer update --no-cache 2>/dev/null

# helpers
cleanup:
	@docker compose down -t0 -v 2>/dev/null

composer.lock:
	@docker compose run --rm php composer update --no-cache --prefer-$(DEPENDENCY_VERSIONS) --prefer-stable 2>/dev/null

help:
	@egrep -h '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort -n | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-12s\033[0m %s\n", $$1, $$2}'

vendor: composer.lock
