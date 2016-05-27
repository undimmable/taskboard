# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure(2) do |config|
  config.vm.provision "shell", inline: <<-SHELL
    echo "Provisioning: STARTED"
    echo "Provisioning: fix console locales"
    export DEBIAN_FRONTEND=noninteractive
    export LANGUAGE=en_US.UTF-8
    export LANG=en_US.UTF-8
    export LC_ALL=en_US.UTF-8
    source /home/vagrant/config/env
    locale-gen en_US.UTF-8
    rm -v /etc/apt/apt.conf.d/70debconf
    dpkg-reconfigure locales
    echo "Provisioning: remove default mysql"
    apt-key adv --keyserver ha.pool.sks-keyservers.net --recv-keys 5072E1F5
    echo "deb http://repo.mysql.com/apt/ubuntu/ trusty mysql-5.7" | tee -a /etc/apt/sources.list.d/mysql.list
    apt-get -y purge mysql-server mysql-client mysql-common mysql-server-5.5
    apt-get -qq update

    echo "Provisioning: Install MySQL"
    apt-get -q -y install mysql-server
    mysqladmin -u root password $MYSQL_PASS

    echo "Provisioning: install PHP, FPM and mailutils"
    apt-get install -y mysql-client nginx php5-fpm php5-mysql php5-common php5-dev php5-cli php5-fpm php5-xdebug mailutils 2>/dev/null

    echo "Provisioning: creating MySQL users"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_account identified by '$MYSQL_ACCOUNT_PASS'"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_login identified by '$MYSQL_LOGIN_PASS'"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_task identified by '$MYSQL_TASK_PASS'"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_text_idx identified by '$MYSQL_TEXT_IDX_PASS'"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_tx identified by '$MYSQL_TX_PASS'"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_user identified by '$MYSQL_USER_PASS'"
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_user_info identified by '$MYSQL_USER_INFO_PASS'"

    echo "Provisioning: creating MySQL databases"
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/account.sql
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/login.sql
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/task.sql
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/text_idx.sql
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/tx.sql
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/user.sql
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/user_info.sql

    echo "Provisioning: stopping services"
    service nginx stop
    service php5-fpm stop
    service postfix stop
    echo "Provisioning: configure nginx"
    rm -rf /etc/nginx/sites-enabled/default
    rm -rf /etc/nginx/nginx.conf
    ln -s /home/vagrant/config/nginx/nginx.conf /etc/nginx/
    ln -s /home/vagrant/config/nginx/taskboards.top /etc/nginx/sites-enabled/
    ln -s /home/vagrant/config/nginx/mobile-rewrite.conf /etc/nginx/mobile-rewrite.conf
    ln -s /home/vagrant/config/fpm/fpm-config.ini /etc/php5/fpm/conf.d/fpm-taskboard.ini

    echo "Provisioning: configure mailutils"
    rm -rf /etc/postfix/main.cf
    cp /home/vagrant/config/mail/main.cf /etc/postfix/main.cf
    cp /home/vagrant/config/mail/sasl_passwd /etc/postfix/sasl_passwd
    sed -i -e 's/rplc_username/'"$GOOGLE_USERNAME"'/g' /etc/postfix/sasl_passwd
    sed -i -e 's/rplc_password/'"$GOOGLE_PASS"'/g' /etc/postfix/sasl_passwd
    echo "taskboard.dev" >> /etc/mailname
    postmap /etc/postfix/sasl_passwd 2>/dev/null
    chmod 600 /etc/postfix/sasl_passwd
    chmod 600 /etc/postfix/sasl_passwd.db

    echo "Provisioning: configure php"
    PHP_ADDITIONAL_INCLUDE_PATH=/var/www/taskboard_config/
    DB_CONFIG_FILE="taskboard_db_config.php"
    DB_CONFIG_FULL_PATH=$PHP_ADDITIONAL_INCLUDE_PATH/$DB_CONFIG_FILE
    SECURITY_CONFIG_FILE="taskboard_security_config.php"
    SECURITY_CONFIG_FULL_PATH=$PHP_ADDITIONAL_INCLUDE_PATH/$SECURITY_CONFIG_FILE
    VALIDATION_CONFIG_FILE="taskboard_validation_config.php"
    VALIDATION_CONFIG_FULL_PATH=$PHP_ADDITIONAL_INCLUDE_PATH/$VALIDATION_CONFIG_FILE
    mkdir -p $PHP_ADDITIONAL_INCLUDE_PATH
    cp /home/vagrant/config/php/*.php $PHP_ADDITIONAL_INCLUDE_PATH

    echo "Provisioning: PHP database configuration inside $DB_CONFIG_FULL_PATH"
    sed -i -e 's/rplc_account_password/'"$MYSQL_ACCOUNT_PASS"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_account_host/'"$MYSQL_ACCOUNT_HOST"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_login_password/'"$MYSQL_LOGIN_PASS"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_login_host/'"$MYSQL_LOGIN_HOST"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_task_password/'"$MYSQL_TASK_PASS"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_task_host/'"$MYSQL_TASK_HOST"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_text_idx_password/'"$MYSQL_TEXT_IDX_PASS"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_text_idx_host/'"$MYSQL_TEXT_IDX_HOST"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_tx_password/'"$MYSQL_TX_PASS"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_tx_host/'"$MYSQL_TX_HOST"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_user_password/'"$MYSQL_USER_PASS"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_user_host/'"$MYSQL_USER_HOST"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_user_info_password/'"$MYSQL_USER_INFO_PASS"'/g' $DB_CONFIG_FULL_PATH
    sed -i -e 's/rplc_user_info_host/'"$MYSQL_USER_INFO_HOST"'/g' $DB_CONFIG_FULL_PATH

    echo "Provisioning PHP keys configuration inside $SECURITY_CONFIG_FULL_PATH"
    sed -i -e 's/rplc_jwt_secret/'"$JWT_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH
    sed -i -e 's/rplc_confirmation_key/'"$CONFIRMATION_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH
    sed -i -e 's/rplc_vk_client_id/'"$VK_APP_ID"'/g' $SECURITY_CONFIG_FULL_PATH
    sed -i -e 's/rplc_vk_secret/'"$VK_APP_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH

    echo "Provisioning: add certs"
    openssl genrsa -des3 -passout pass:x -out /etc/ssl/taskboards.top.pass.key 2048 > /dev/null 2>&1
    openssl rsa -passin pass:x -in /etc/ssl/taskboards.top.pass.key -out /etc/ssl/taskboards.top.key
    rm /etc/ssl/taskboards.top.pass.key   2>/dev/null
    openssl req -new -key /etc/ssl/taskboards.top.key -out /etc/ssl/taskboards.top.csr -days 365 -subj '/CN=taskboards.top/C=RU/ST=NW/L=Saint-Petersburg/O=TaskBoard/OU=TB Team/emailAddress=inbox@taskboards.top/subjectAltName=DNS.1=taskboards.top' -batch 2>/dev/null
    openssl x509 -req -days 365 -in /etc/ssl/taskboards.top.csr -signkey /etc/ssl/taskboards.top.key -out /etc/ssl/taskboards.top.cert 2>/dev/null

    echo "Provisioning: monitoring tools"
    apt-get -q -y install monit apache2-utils
    wget --quiet --directory-prefix=/tmp/ https://mmonit.com/dist/mmonit-3.5.1-linux-x64.tar.gz
    sudo tar -xvf /tmp/mmonit-3.5.1-linux-x64.tar.gz -C /opt/
    sudo mv /opt/mmonit-3.5.1 /opt/mmonit
    cp /home/vagrant/config/monit/monitrc /etc/monit/
    cp /home/vagrant/config/monit/mmonit /etc/monit/monitrc.d/
    cp /home/vagrant/config/monit/server.xml /opt/mmonit/conf/
    cp /home/vagrant/config/monit/mmonit.conf /etc/init/
    cp /home/vagrant/config/monit/lemp /etc/monit/conf.d/
    cp /home/vagrant/config/nginx/monit /etc/nginx/sites-enabled/
    sudo htpasswd -c -b /etc/nginx/.htpasswd $MONIT_USER $MONIT_PASSWORD
    rm -rf /tmp/mmonit-3.5.1-linux-x64.tar.gz

    echo "Provisioning: firewall rules"
    ufw allow ssh
    ufw allow 2222/tcp
    ufw --force default deny incoming
    ufw allow 3736
    ufw allow 80
    ufw allow 443
    ufw --force enable

    echo "Provisioning: starting services"
    initctl reload-configuration
    service postfix start
    service php5-fpm start
    service nginx start
    service mmonit restart
    service monit restart

    echo "Provisioning: DONE"
  SHELL
end