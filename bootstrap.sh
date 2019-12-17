#!/usr/bin/env bash

sudo apt-get update

sudo apt-get install make -y
sudo apt-get install htop -y
sudo apt-get install zip -y
sudo apt-get install curl -y
sudo apt-get install ntp -y

sudo apt-get install -y apache2
if ! [ -L /var/www ]; then
    sudo rm -rf /var/www
    sudo ln -fs /vagrant /var/www
fi

# Apache modules
sudo a2enmod rewrite
sudo a2enmod dir

# Apache config
sudo cp /vagrant/vagrant-deploy/apache2/apache2.conf /etc/apache2/
sudo cp /vagrant/vagrant-deploy/apache2/envvars /etc/apache2/
sudo cp /vagrant/vagrant-deploy/apache2/dir.conf /etc/apache2/mods-available/

# PHP
sudo apt-get install php7.2 -y
sudo apt-get install php7.2-cli -y
sudo apt-get install php7.2-common -y
sudo apt-get install php7.2-curl -y
sudo apt-get install php7.2-json -y
sudo apt-get install php7.2-opcache -y
sudo apt-get install php7.2-mysql -y
sudo apt-get install php7.2-mbstring -y
sudo apt-get install php7.2-zip -y
sudo apt-get install php7.2-fpm -y
sudo apt-get install php7.2-xml -y
sudo apt-get install libapache2-mod-php7.2 -y

# Additional dependencies
sudo phpenmod mbstring
sudo phpenmod curl

# PHP settings
sudo sed -i 's,^;upload_tmp_dir =.*$,upload_tmp_dir = /vagrant/storage/tmp,' /etc/php/7.2/apache2/php.ini

# Apache
sudo a2dissite 000-default

sudo cp /vagrant/vagrant-deploy/apache2/wos-local.conf /etc/apache2/sites-available
sudo a2ensite wos-local

sudo service apache2 stop
sudo service apache2 start

# MySQL
debconf-set-selections <<< 'mysql-server mysql-server/root_password password pass'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password pass'

sudo apt-get install mysql-server -y

sudo service mysql stop
sudo service mysql start

mysql -u root -ppass -e "create database wos;"
mysql -u root -ppass -e "create user 'wos'@'localhost' identified by 'pass';"
mysql -u root -ppass -e "grant all privileges on wos.* to 'wos'@'localhost';"
mysql -u root -ppass -e "flush privileges;"

mysql -u root -ppass wos < /vagrant/db.sql

# Composer
cd /home/vagrant/
curl  -k -sS https://getcomposer.org/installer | php
sudo mv /home/vagrant/composer.phar /usr/local/bin/composer
echo 'export COMPOSER_HOME="/home/vagrant/.composer"' | tee -a .bashrc
source ~/.bashrc
echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' | tee -a .bashrc
source ~/.bashrc
sudo runuser -l vagrant -c 'source ~/.bashrc'

cd /var/www
/usr/local/bin/composer install
/usr/local/bin/composer dump-autoload
