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
	./vendor/bin/phpunit $(FILE)

local-startup:
	sudo chown -R _mysql:_mysql /usr/local/var/mysql
	sudo /usr/local/mysql/bin/mysqld_safe &

vagrant-refresh:
	vagrant reload --provision
