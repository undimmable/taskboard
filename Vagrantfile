# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "private_network", ip: "192.168.56.101"
  config.vm.synced_folder "./src/", "/var/www/taskboard/src", create: true, group: "www-data", owner: "www-data"
  config.vm.synced_folder "./public/", "/var/www/taskboard/public", create: true, group: "www-data", owner: "www-data"
  config.vm.synced_folder "./config/", "/home/vagrant/config", create: true
  config.vm.provider "virtualbox" do |v|
    v.gui = false
    v.name = "taskboard"
    v.memory = 512
    v.cpus = 1
    v.customize ["modifyvm", :id, "--cpuexecutioncap", "50"]
  end
  config.vm.provision "fix-no-tty", type: "shell" do |s|
    s.privileged = false
    s.inline = "sudo sed -i '/tty/!s/mesg n/tty -s \\&\\& mesg n/' /root/.profile"
  end
  config.vm.provision "shell", inline: <<-SHELL
    export DEBIAN_FRONTEND=noninteractive
    export LANGUAGE=en_US.UTF-8
    export LANG=en_US.UTF-8
    export LC_ALL=en_US.UTF-8
    source /home/vagrant/config/env
    locale-gen en_US.UTF-8
    rm -v /etc/apt/apt.conf.d/70debconf
    dpkg-reconfigure locales
    apt-get -y purge mysql-server mysql-client mysql-common mysql-server-5.5
    apt-get -qq update
    apt-get -q -y install mysql-server
    mysqladmin -u root password $MYSQL_PASS
    apt-get install -y mysql-client nginx php5-fpm php5-mysql php5-common php5-dev php5-cli php5-fpm php5-xdebug
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_account identified by \"$MYSQL_ACCOUNT_PASS\""
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_customer identified by \"$MYSQL_CUSTOMER_PASS\""
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_login identified by \"$MYSQL_LOGIN_PASS\""
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_performer identified by \"$MYSQL_PERFORMER_PASS\""
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_system identified by \"$MYSQL_SYSTEM_PASS\""
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_task identified by \"$MYSQL_TASK_PASS\""
    service php5-fpm stop
    service nginx stop
    echo "xdebug.remote_enable=true" >> /etc/php5/mods-available/xdebug.ini
    echo "xdebug.profiler_enable=1" >> /etc/php5/mods-available/xdebug.ini
    service php5-fpm start
    rm -rf /etc/nginx/sites-enabled/default
    ln -s /home/vagrant/config/nginx.conf /etc/nginx/sites-enabled/taskboard.dev
    openssl genrsa -out /etc/ssl/taskboard.dev.key 2048 > /dev/null 2>&1
    openssl req -new -x509 -key /etc/ssl/taskboard.dev.key -out /etc/ssl/taskboard.dev.cert -days 365 -subj /CN=taskboard.dev -batch
    service nginx start
  SHELL
end