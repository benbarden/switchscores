SHELL:=/bin/bash

unit-test:
	./vendor/bin/phpunit

unit-test-game-title-match:
	./vendor/bin/phpunit --filter TitleMatchTest

unit-test-feed-importer-generic:
	./vendor/bin/phpunit --filter FeedImporterGeneric

local-startup:
	sudo chown -R _mysql:_mysql /usr/local/var/mysql
	sudo /usr/local/mysql/bin/mysqld_safe &

vagrant-refresh:
	vagrant reload --provision
