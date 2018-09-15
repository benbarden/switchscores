SHELL:=/bin/bash

unit-test:
	./vendor/bin/phpunit

local-startup:
	sudo chown -R _mysql:_mysql /usr/local/var/mysql
	sudo /usr/local/mysql/bin/mysqld_safe &

vagrant-refresh:
	vagrant reload --provision
