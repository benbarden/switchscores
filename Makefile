SHELL:=/bin/bash

unit-test:
	./vendor/bin/phpunit

unit-test-game-title-match:
	./vendor/bin/phpunit --filter TitleMatchTest

unit-test-feed-importer-generic:
	./vendor/bin/phpunit --filter FeedImporterGeneric

unit-test-page-staff:
	./vendor/bin/phpunit --filter Staff

unit-test-models:
	./vendor/bin/phpunit --filter Models

local-startup:
	sudo chown -R _mysql:_mysql /usr/local/var/mysql
	sudo /usr/local/mysql/bin/mysqld_safe &

vagrant-refresh:
	vagrant reload --provision
