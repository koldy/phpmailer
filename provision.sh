#!/bin/bash

# SWAP file:
fallocate -l 256M /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
cp /etc/fstab /etc/fstab.bak
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

sysctl vm.swappiness=10
echo 'vm.swappiness=10' | sudo tee -a /etc/sysctl.conf

sysctl vm.vfs_cache_pressure=50
echo 'vm.vfs_cache_pressure=50' | sudo tee -a /etc/sysctl.conf

echo "Configuring PHP repo"
LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php -y

apt update
apt upgrade

echo "Configuring UTF-8 locale..."
apt install -y language-pack-en-base && export LC_ALL=en_US.UTF-8 && export LANG=en_US.UTF-8
apt install zip unzip

# Prepare Logs folders for easier troubleshooting
rm -rf /vagrant/logs
mkdir /vagrant/logs
chmod 0777 /vagrant/logs

echo "Installing PHP 8.1"
LC_ALL=en_US.UTF-8 apt install php8.1-fpm php8.1-cli php8.1-common php8.1-mysql php8.1-mbstring php8.1-pgsql php8.1-intl php8.1-gd php8.1-curl php8.1-memcached php8.1-bcmath php-xdebug -y

VAGRANT_USER="user = $(stat -c %U /vagrant)"
VAGRANT_GROUP="group = $(stat -c %U /vagrant)"

sed -i 's#\;catch_workers_output = yes#catch_workers_output = yes#g' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's#error_log = /var/log/php8.1-fpm.log#error_log = /vagrant/logs/php.error.log#g' /etc/php/8.1/fpm/php-fpm.conf
sed -i 's#user = www-data#'"$VAGRANT_USER"'#g' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's#group = www-data#'"$VAGRANT_GROUP"'#g' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's#\;php_admin_flag\[log_errors\] = on#php_admin_flag\[log_errors\] = on#g' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's#\;php_admin_value\[error_log\] = /var/log/fpm-php.www.log#php_admin_value\[error_log\] = /vagrant/logs/php.error.log#g' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's#post_max_size = 8M#post_max_size = 32M#g' /etc/php/8.1/fpm/php.ini
sed -i 's#upload_max_filesize = 2M#upload_max_filesize = 32M#g' /etc/php/8.1/fpm/php.ini

touch /vagrant/logs/php.error.log
chmod -R 0777 /vagrant/logs/*
service php8.1-fpm restart

cd /vagrant
curl https://getcomposer.org/installer > composer-setup.php
php composer-setup.php
unlink composer-setup.php
