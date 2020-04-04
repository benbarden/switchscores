SHELL:=/bin/bash

test:
	./vendor/bin/phpunit

test-feature:
	./vendor/bin/phpunit --testsuite Feature

test-page:
	./vendor/bin/phpunit --testsuite Page

test-unit:
	./vendor/bin/phpunit --testsuite Unit

test-data:
	./vendor/bin/phpunit --testsuite DataSources

test-eshop:
	./vendor/bin/phpunit --testsuite Eshop

test-game-import-rule:
	./vendor/bin/phpunit --filter GameImportRule

test-html-loader:
	./vendor/bin/phpunit --filter HtmlLoader

test-game-title-match:
	./vendor/bin/phpunit --filter TitleMatchTest

test-feed-importer-generic:
	./vendor/bin/phpunit --filter FeedImporterGeneric

test-page-staff:
	./vendor/bin/phpunit --filter Staff

test-models:
	./vendor/bin/phpunit --filter Models

local-startup:
	sudo chown -R _mysql:_mysql /usr/local/var/mysql
	sudo /usr/local/mysql/bin/mysqld_safe &

vagrant-refresh:
	vagrant reload --provision
