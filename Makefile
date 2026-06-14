SHELL:=/bin/bash

# Default: fast tests only
test:
	./vendor/bin/phpunit --testsuite Fast

# Explicitly slow tests
test-page:
	./vendor/bin/phpunit --testsuite Page

# Everything (rare)
test-all:
	./vendor/bin/phpunit --testsuite All

# Escape hatches
test-filter:
	./vendor/bin/phpunit --filter $(F)

test-file:
	@test -n "$(FILE)" || (echo "Usage: make test-file FILE=tests/path/to/SomeTest.php" && exit 1)
	./vendor/bin/phpunit $(FILE)

local-startup:
	sudo chown -R _mysql:_mysql /usr/local/var/mysql
	sudo /usr/local/mysql/bin/mysqld_safe &

vagrant-refresh:
	vagrant reload --provision

docker-up:
	docker compose up -d

docker-down:
	docker compose down

docker-build:
	docker compose build

docker-logs:
	docker compose logs -f
