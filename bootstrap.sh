#!/usr/bin/env bash

sudo apt-get update

sudo apt-get install htop -y

sudo apt-get install -y apache2
if ! [ -L /var/www ]; then
    rm -rf /var/www
    ln -fs /vagrant /var/www
fi

sudo cp /vagrant/vagrant-deploy/apache2/apache2.conf /etc/apache2/
sudo cp /vagrant/vagrant-deploy/apache2/envvars /etc/apache2/
sudo cp /vagrant/vagrant-deploy/apache2/dir.conf /etc/apache2/mods-available/

sudo a2enmod rewrite
sudo a2enmod dir

sudo apt-get install php libapache2-mod-php php-mcrypt php-mysql -y

debconf-set-selections <<< 'mysql-server mysql-server/root_password password pass'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password pass'

sudo apt-get install mysql-server -y

sudo a2dissite 000-default

sudo cp /vagrant/vagrant-deploy/apache2/wos-local.conf /etc/apache2/sites-available
sudo a2ensite wos-local

sudo service apache2 stop
sudo service apache2 start

sudo service mysql stop
sudo service mysql start

# MySQL setup
mysql -u root -ppass -e "create database wos;"
mysql -u root -ppass -e "create user 'wos'@'localhost' identified by 'pass';"
mysql -u root -ppass -e "grant all privileges on wos.* to 'wos'@'localhost';"
mysql -u root -ppass -e "flush privileges;"

mysql -u root -ppass wos < /vagrant/db.sql
