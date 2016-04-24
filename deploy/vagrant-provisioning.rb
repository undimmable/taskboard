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
    sed -i -e 's/rplc_account_password/'"$MYSQL_ACCOUNT_PASS"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_account_host/'"$MYSQL_ACCOUNT_HOST"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_customer_password/'"$MYSQL_CUSTOMER_PASS"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_customer_host/'"$MYSQL_CUSTOMER_HOST"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_login_password/'"$MYSQL_LOGIN_PASS"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_login_host/'"$MYSQL_LOGIN_HOST"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_performer_password/'"$MYSQL_PERFORMER_PASS"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_performer_host/'"$MYSQL_PERFORMER_HOST"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_system_password/'"$MYSQL_SYSTEM_PASS"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_system_host/'"$MYSQL_SYSTEM_HOST"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_task_password/'"$MYSQL_TASK_PASS"'/g' /home/vagrant/config/db/db_config.ini
    sed -i -e 's/rplc_task_host/'"$MYSQL_TASK_HOST"'/g' /home/vagrant/config/db/db_config.ini
    service nginx stop
    echo "xdebug.remote_enable=true" >> /etc/php5/mods-available/xdebug.ini
    echo "xdebug.profiler_enable=1" >> /etc/php5/mods-available/xdebug.ini
    echo "xdebug.remote_host=192.168.56.1" >> /etc/php5/mods-available/xdebug.ini
    mv /home/vagrant/config/db/db_config.ini /etc/php5/fpm/conf.d/
    rm -rf /etc/nginx/sites-enabled/default
    ln -s /home/vagrant/config/nginx/nginx.conf /etc/nginx/sites-enabled/taskboard.dev
    ln -s /home/vagrant/config/nginx/mobile-rewrite.conf /etc/nginx/mobile-rewrite.conf
    ln -s /home/vagrant/config/fpm/fpm-config.ini /etc/php5/fpm/conf.d/fpm-taskboard.ini
    openssl genrsa -des3 -passout pass:x -out /etc/ssl/taskboard.dev.pass.key 2048 > /dev/null 2>&1
    openssl rsa -passin pass:x -in /etc/ssl/taskboard.dev.pass.key -out /etc/ssl/taskboard.dev.key
    rm /etc/ssl/taskboard.dev.pass.key
    openssl req -new -key /etc/ssl/taskboard.dev.key -out /etc/ssl/taskboard.dev.csr -days 365 -subj '/CN=taskboard.dev/C=RU/ST=NW/L=Saint-Petersburg/O=TaskBoard/OU=TB Team/emailAddress=na@notexists.com/subjectAltName=DNS.1=taskboard.dev' -batch
    openssl x509 -req -days 365 -in /etc/ssl/taskboard.dev.csr -signkey /etc/ssl/taskboard.dev.key -out /etc/ssl/taskboard.dev.cert
    service nginx start
    service php5-fpm start
  SHELL
end