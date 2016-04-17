# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure(2) do |config|
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
    echo "create user_account"
    mysql --user=root --password=\"$MYSQL_PASS\"  -e "CREATE user user_account identified by '$MYSQL_ACCOUNT_PASS'"
    echo "create user_customer"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_customer identified by '$MYSQL_CUSTOMER_PASS'"
    echo "create user_login"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_login identified by '$MYSQL_LOGIN_PASS'"
    echo "create user_performer"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_performer identified by '$MYSQL_PERFORMER_PASS'"
    echo "create user_system"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_system identified by '$MYSQL_SYSTEM_PASS'"
    echo "create user_task"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_task identified by '$MYSQL_TASK_PASS'"
    echo "execute account"
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/account.sql
    echo "execute customer"
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/customer.sql
    echo "execute login"
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/login.sql
    echo "execute performer"
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/performer.sql
    echo "execute system"
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/system.sql
    echo "execute task"
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/task.sql
    service php5-fpm stop
    sed -i -e 's/rplc_account_password/'"$MYSQL_ACCOUNT_PASS"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_account_host/'"$MYSQL_ACCOUNT_HOST"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_customer_password/'"$MYSQL_CUSTOMER_PASS"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_customer_host/'"$MYSQL_CUSTOMER_HOST"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_login_password/'"$MYSQL_LOGIN_PASS"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_login_host/'"$MYSQL_LOGIN_HOST"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_performer_password/'"$MYSQL_PERFORMER_PASS"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_performer_host/'"$MYSQL_PERFORMER_HOST"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_system_password/'"$MYSQL_SYSTEM_PASS"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_system_host/'"$MYSQL_SYSTEM_HOST"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_task_password/'"$MYSQL_TASK_PASS"'/g' /home/vagrant/config/db_config.ini
    sed -i -e 's/rplc_task_host/'"$MYSQL_TASK_HOST"'/g' /home/vagrant/config/db_config.ini
    service nginx stop
    echo "xdebug.remote_enable=true" >> /etc/php5/mods-available/xdebug.ini
    echo "xdebug.profiler_enable=1" >> /etc/php5/mods-available/xdebug.ini
    mv /home/vagrant/config/db_config.ini /etc/php5/fpm/conf.d/
    service php5-fpm start
    rm -rf /etc/nginx/sites-enabled/default
    ln -s /home/vagrant/config/nginx.conf /etc/nginx/sites-enabled/taskboard.dev
    openssl genrsa -out /etc/ssl/taskboard.dev.key 2048 > /dev/null 2>&1
    openssl req -new -x509 -key /etc/ssl/taskboard.dev.key -out /etc/ssl/taskboard.dev.cert -days 365 -subj /CN=taskboard.dev -batch
    service nginx start
  SHELL
end