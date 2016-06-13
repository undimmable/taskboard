# -*- mode: ruby -*-
# vi: set ft=ruby :
begin
  load 'deploy/vagrant-provisioning.rb'
rescue LoadError => error
  puts error.message
end
Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "private_network", ip: "192.168.56.101"
  config.vm.synced_folder "./src/", "/var/www/taskboard/src", create: true, group: "www-data", owner: "www-data"
  config.vm.synced_folder "./public/", "/var/www/taskboard/public", create: true, group: "www-data", owner: "www-data"
  config.vm.synced_folder "./config/", "/home/vagrant/config", create: true, type: "virtualbox"
  config.vm.provider "virtualbox" do |v|
    v.gui = false
    v.name = "taskboard"
    v.memory = 512
    v.cpus = 1
    v.customize ["modifyvm", :id, "--cpuexecutioncap", "50"]
  end
  config.push.define "heroku" do |push|
    push.app = "taskboard-development"
  end
  config.vm.provision "mysql_dev", type: "shell", inline: <<-SHELL
    export INSTALL_LOG=/var/log/taskboards-provisioning.log
    source /home/vagrant/config/env
    service mysql stop >> $INSTALL_LOG 2>&1
    sed -i -e 's/127\.0\.0\.1/0\.0\.0\.0/g' /etc/mysql/my.cnf >> $INSTALL_LOG 2>&1
    ufw allow 3306 >> $INSTALL_LOG 2>&1
    service mysql start >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE USER idea@'%' IDENTIFIED BY '123'"  >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "GRANT ALL ON *.* TO idea@'%' IDENTIFIED BY '123'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE USER phpunit@'%' IDENTIFIED BY '123456'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "GRANT ALL ON *.* TO phpunit@'%' IDENTIFIED BY '123456'" >> $INSTALL_LOG 2>&1
  SHELL
  config.vm.provision "enable_xdebug", type: "shell", inline: <<-SHELL
    export INSTALL_LOG=/var/log/taskboards-provisioning.log
    echo "xdebug.remote_enable=true" >> /etc/php5/mods-available/xdebug.ini
    echo "xdebug.profiler_enable=1" >> /etc/php5/mods-available/xdebug.ini
    echo "xdebug.remote_host=192.168.56.1" >> /etc/php5/mods-available/xdebug.ini
    service php5-fpm restart >> $INSTALL_LOG 2>&1
  SHELL
  config.vm.provision "enable_staging", type: "shell", inline: <<-SHELL
    export INSTALL_LOG=/var/log/taskboards-provisioning.log
    echo "env[TESTS_STAGING] = true" >> /etc/php5/fpm/pool.d/www.conf
    service php5-fpm restart >> $INSTALL_LOG 2>&1
  SHELL
end