# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure(2) do |config|
  config.vm.provision "shell", inline: <<-SHELL
    export INSTALL_LOG=/var/log/taskboards-provisioning.log
    rm -f $INSTALL_LOG
    echo "Provisioning: STARTED" | tee -a $INSTALL_LOG
    echo "Provisioning: fix console locales" | tee -a $INSTALL_LOG
    export DEBIAN_FRONTEND=noninteractive
    export LANGUAGE=en_US.UTF-8
    export LANG=en_US.UTF-8
    export LC_ALL=en_US.UTF-8
    source /home/vagrant/config/env
    locale-gen en_US.UTF-8 >> $INSTALL_LOG 2>&1
    rm -v /etc/apt/apt.conf.d/70debconf >> $INSTALL_LOG 2>&1
    dpkg-reconfigure locales >> $INSTALL_LOG 2>&1
    echo "Provisioning: remove default mysql" >> $INSTALL_LOG 2>&1
    apt-key adv --keyserver ha.pool.sks-keyservers.net --recv-keys 5072E1F5 >> $INSTALL_LOG 2>&1
    echo "deb http://repo.mysql.com/apt/ubuntu/ trusty mysql-5.7" >> /etc/apt/sources.list.d/mysql.list
    apt-get -y purge mysql-server mysql-client mysql-common mysql-server-5.5 >> $INSTALL_LOG 2>&1
    apt-get -qq update >> $INSTALL_LOG 2>&1

    echo "Provisioning: Install MySQL" | tee -a $INSTALL_LOG
    apt-get -q -y install mysql-server >> $INSTALL_LOG 2>&1
    mysqladmin -u root password $MYSQL_PASS >> $INSTALL_LOG 2>&1

    echo "Provisioning: install PHP, FPM and mailutils" | tee -a $INSTALL_LOG
    apt-get install -y mysql-client nginx php5-fpm php5-mysql php5-common php5-dev php5-cli php5-fpm php5-xdebug mailutils  >> $INSTALL_LOG 2>&1
    yes | pecl install channel://pecl.php.net/libevent-0.1.0  >> $INSTALL_LOG 2>&1

    echo "Provisioning: creating MySQL users" | tee -a $INSTALL_LOG
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_account identified by '$MYSQL_ACCOUNT_PASS'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_login identified by '$MYSQL_LOGIN_PASS'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_message identified by '$MYSQL_MESSAGE_PASS'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_task identified by '$MYSQL_TASK_PASS'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_text_idx identified by '$MYSQL_TEXT_IDX_PASS'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_tx identified by '$MYSQL_TX_PASS'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_user identified by '$MYSQL_USER_PASS'" >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS  -e "CREATE user user_user_info identified by '$MYSQL_USER_INFO_PASS'" >> $INSTALL_LOG 2>&1

    echo "Provisioning: creating MySQL databases" | tee -a $INSTALL_LOG
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/account.sql >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/login.sql >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/message.sql >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/task.sql >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/text_idx.sql >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/tx.sql >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/user.sql >> $INSTALL_LOG 2>&1
    mysql --user=root --password=$MYSQL_PASS < /home/vagrant/config/db/user_info.sql >> $INSTALL_LOG 2>&1

    echo "Provisioning: stopping services" | tee -a $INSTALL_LOG
    service nginx stop >> $INSTALL_LOG 2>&1
    service php5-fpm stop >> $INSTALL_LOG 2>&1
    service postfix stop >> $INSTALL_LOG 2>&1
    echo "Provisioning: configure nginx" >> $INSTALL_LOG 2>&1
    rm -rf /etc/nginx/sites-enabled/default >> $INSTALL_LOG 2>&1
    rm -rf /etc/nginx/nginx.conf >> $INSTALL_LOG 2>&1
    ln -s /home/vagrant/config/nginx/nginx.conf /etc/nginx/ >> $INSTALL_LOG 2>&1
    ln -s /home/vagrant/config/nginx/taskboards.top /etc/nginx/sites-enabled/ >> $INSTALL_LOG 2>&1
    ln -s /home/vagrant/config/nginx/mobile-rewrite.conf /etc/nginx/mobile-rewrite.conf >> $INSTALL_LOG 2>&1
    ln -s /home/vagrant/config/fpm/fpm-config.ini /etc/php5/fpm/conf.d/fpm-taskboard.ini >> $INSTALL_LOG 2>&1
    echo "extension=libevent.so" > /etc/php5/mods-available/libevent.ini >> $INSTALL_LOG 2>&1
    rm /etc/php5/fpm/pool.d/www.conf >> $INSTALL_LOG 2>&1
    ln -s /home/vagrant/config/fpm/www.conf /etc/php5/fpm/pool.d/www.conf >> $INSTALL_LOG 2>&1

    echo "Provisioning: configure mailutils" | tee -a $INSTALL_LOG
    rm -rf /etc/postfix/main.cf >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/mail/main.cf /etc/postfix/main.cf >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/mail/sasl_passwd /etc/postfix/sasl_passwd >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_username/'"$GOOGLE_USERNAME"'/g' /etc/postfix/sasl_passwd >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_password/'"$GOOGLE_PASS"'/g' /etc/postfix/sasl_passwd >> $INSTALL_LOG 2>&1
    echo "taskboard.dev" >> /etc/mailname
    postmap /etc/postfix/sasl_passwd  >> $INSTALL_LOG 2>&1
    chmod 600 /etc/postfix/sasl_passwd >> $INSTALL_LOG 2>&1
    chmod 600 /etc/postfix/sasl_passwd.db >> $INSTALL_LOG 2>&1

    echo "Provisioning: configure php" | tee -a $INSTALL_LOG
    PHP_ADDITIONAL_INCLUDE_PATH=/var/www/taskboard_config/
    DB_CONFIG_FILE="taskboard_db_config.php"
    DB_CONFIG_FULL_PATH=$PHP_ADDITIONAL_INCLUDE_PATH/$DB_CONFIG_FILE
    SECURITY_CONFIG_FILE="taskboard_security_config.php"
    SECURITY_CONFIG_FULL_PATH=$PHP_ADDITIONAL_INCLUDE_PATH/$SECURITY_CONFIG_FILE
    VALIDATION_CONFIG_FILE="taskboard_validation_config.php"
    VALIDATION_CONFIG_FULL_PATH=$PHP_ADDITIONAL_INCLUDE_PATH/$VALIDATION_CONFIG_FILE
    mkdir -p $PHP_ADDITIONAL_INCLUDE_PATH >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/php/*.php $PHP_ADDITIONAL_INCLUDE_PATH >> $INSTALL_LOG 2>&1

    echo "Provisioning: PHP database configuration inside $DB_CONFIG_FULL_PATH" | tee -a $INSTALL_LOG
    sed -i -e 's/rplc_account_password/'"$MYSQL_ACCOUNT_PASS"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_account_host/'"$MYSQL_ACCOUNT_HOST"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_login_password/'"$MYSQL_LOGIN_PASS"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_login_host/'"$MYSQL_LOGIN_HOST"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_message_password/'"$MYSQL_MESSAGE_PASS"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_message_host/'"$MYSQL_MESSAGE_HOST"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_task_password/'"$MYSQL_TASK_PASS"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_task_host/'"$MYSQL_TASK_HOST"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_text_idx_password/'"$MYSQL_TEXT_IDX_PASS"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_text_idx_host/'"$MYSQL_TEXT_IDX_HOST"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_tx_password/'"$MYSQL_TX_PASS"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_tx_host/'"$MYSQL_TX_HOST"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_user_password/'"$MYSQL_USER_PASS"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_user_host/'"$MYSQL_USER_HOST"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_user_info_password/'"$MYSQL_USER_INFO_PASS"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_user_info_host/'"$MYSQL_USER_INFO_HOST"'/g' $DB_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1

    echo "Provisioning PHP keys configuration inside $SECURITY_CONFIG_FULL_PATH" | tee -a $INSTALL_LOG
    sed -i -e 's/rplc_jwt_secret/'"$JWT_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_confirmation_key/'"$CONFIRMATION_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_vk_client_id/'"$VK_APP_ID"'/g' $SECURITY_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_vk_secret/'"$VK_APP_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_login_csrf_secret/'"$LOGIN_CSRF_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_task_csrf_secret/'"$TASK_CSRF_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_account_csrf_secret/'"$ACCOUNT_CSRF_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1
    sed -i -e 's/rplc_payload_secret/'"$PAYLOAD_SECRET"'/g' $SECURITY_CONFIG_FULL_PATH >> $INSTALL_LOG 2>&1

    echo "Provisioning: add certs" | tee -a $INSTALL_LOG
    openssl genrsa -des3 -passout pass:x -out /etc/ssl/taskboards.top.pass.key 2048  >> $INSTALL_LOG 2>&1
    openssl rsa -passin pass:x -in /etc/ssl/taskboards.top.pass.key -out /etc/ssl/taskboards.top.key >> $INSTALL_LOG 2>&1
    rm /etc/ssl/taskboards.top.pass.key >> $INSTALL_LOG 2>&1
    openssl req -new -key /etc/ssl/taskboards.top.key -out /etc/ssl/taskboards.top.csr -days 365 -subj '/CN=taskboards.top/C=RU/ST=NW/L=Saint-Petersburg/O=TaskBoard/OU=TB Team/emailAddress=inbox@taskboards.top/subjectAltName=DNS.1=taskboards.top' -batch >> $INSTALL_LOG 2>&1
    openssl x509 -req -days 365 -in /etc/ssl/taskboards.top.csr -signkey /etc/ssl/taskboards.top.key -out /etc/ssl/taskboards.top.cert >> $INSTALL_LOG 2>&1

    echo "Provisioning: monitoring tools" | tee -a $INSTALL_LOG
    apt-get -q -y install monit apache2-utils >> $INSTALL_LOG 2>&1
    wget --quiet --directory-prefix=/tmp/ https://mmonit.com/dist/mmonit-3.5.1-linux-x64.tar.gz >> $INSTALL_LOG 2>&1
    sudo tar -xvf /tmp/mmonit-3.5.1-linux-x64.tar.gz -C /opt/ >> $INSTALL_LOG 2>&1
    sudo mv /opt/mmonit-3.5.1 /opt/mmonit >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/monit/monitrc /etc/monit/ >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/monit/mmonit /etc/monit/monitrc.d/ >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/monit/server.xml /opt/mmonit/conf/ >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/monit/mmonit.conf /etc/init/ >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/monit/lemp /etc/monit/conf.d/ >> $INSTALL_LOG 2>&1
    cp /home/vagrant/config/nginx/monit /etc/nginx/sites-enabled/ >> $INSTALL_LOG 2>&1
    sudo htpasswd -c -b /etc/nginx/.htpasswd $MONIT_USER $MONIT_PASSWORD >> $INSTALL_LOG 2>&1
    rm -rf /tmp/mmonit-3.5.1-linux-x64.tar.gz >> $INSTALL_LOG 2>&1

    echo "Provisioning: firewall rules" | tee -a $INSTALL_LOG
    ufw allow ssh >> $INSTALL_LOG 2>&1
    ufw allow 2222/tcp >> $INSTALL_LOG 2>&1
    ufw --force default deny incoming >> $INSTALL_LOG 2>&1
    ufw allow 3736 >> $INSTALL_LOG 2>&1
    ufw allow 80 >> $INSTALL_LOG 2>&1
    ufw allow 443 >> $INSTALL_LOG 2>&1
    ufw --force enable >> $INSTALL_LOG 2>&1

    echo "Tuning system" | tee -a $INSTALL_LOG
    echo "net.core.somaxconn = 65536" >> /etc/sysctl.conf
    echo "net.ipv4.tcp_max_tw_buckets = 1440000" >> /etc/sysctl.conf
    echo "* soft nofile 8192" >> /etc/security/limits.conf
    echo "* hard nofile 16384" >> /etc/security/limits.conf
    echo "root soft nofile 8192" >> /etc/security/limits.conf
    echo "root hard nofile 16384" >> /etc/security/limits.conf
    echo "session required pam_limits.so" >> /etc/pam.d/common-session

    echo "Provisioning: starting services" | tee -a $INSTALL_LOG
    initctl reload-configuration >> $INSTALL_LOG 2>&1
    sysctl --system >> $INSTALL_LOG 2>&1
    service postfix start >> $INSTALL_LOG 2>&1
    service php5-fpm start >> $INSTALL_LOG 2>&1
    service nginx start >> $INSTALL_LOG 2>&1
    service mmonit restart >> $INSTALL_LOG 2>&1
    service monit restart >> $INSTALL_LOG 2>&1

    echo "Provisioning: DONE" | tee -a $INSTALL_LOG
  SHELL
end