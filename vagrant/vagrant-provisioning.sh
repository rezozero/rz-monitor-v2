#!/bin/bash
#
export DEBIAN_FRONTEND=noninteractive

DBHOST="localhost"
DBNAME="rzmonitor2"
DBUSER="rzmonitor2"
DBPASSWD="rzmonitor2"

echo -e "\n--- Okay, installing now... ---\n"
sudo apt-get -qq update;

echo -e "\n--- Install base packages ---\n"
sudo locale-gen fr_FR.utf8;

echo -e "\n--- Add some repos to update our distro ---\n"
sudo add-apt-repository ppa:ondrej/php5 > /dev/null 2>&1

echo -e "\n--- Updating packages list ---\n"
sudo apt-get -qq update;

echo -e "\n--- Install MySQL specific packages and settings ---\n"
sudo debconf-set-selections <<< "mariadb-server-10.0 mysql-server/root_password password $DBPASSWD"
sudo debconf-set-selections <<< "mariadb-server-10.0 mysql-server/root_password_again password $DBPASSWD"

echo -e "\n--- Install base servers and packages ---\n"
sudo apt-get -qq -f -y install git nginx mariadb-server mariadb-client sqlite php5-fpm curl rabbitmq-server > /dev/null 2>&1;
echo -e "\n--- Install all php5 extensions ---\n"
sudo apt-get -qq -f -y install php5-cli php5-mysqlnd php5-curl php5-gd php5-intl php5-imagick php5-imap php5-mcrypt php5-memcached php5-ming php5-ps php5-pspell php5-recode php5-sqlite php5-tidy php5-xmlrpc php5-xsl php5-xcache php5-xdebug > /dev/null 2>&1;

echo -e "\n--- Setting up our MySQL user and db ---\n"
sudo mysql -uroot -p$DBPASSWD -e "CREATE DATABASE $DBNAME"
mysql -uroot -p$DBPASSWD -e "grant all privileges on $DBNAME.* to '$DBUSER'@'localhost' identified by '$DBPASSWD'"

echo -e "\n--- We definitly need to see the PHP errors, turning them on ---\n"
sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/fpm/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/fpm/php.ini

echo -e "\n--- We definitly need to upload large files ---\n"
sed -i "s/server_tokens off;/server_tokens off;\\n\\tclient_max_body_size 256M;/" /etc/nginx/nginx.conf

echo -e "\n--- Configure Nginx virtual host for Roadiz and phpmyadmin ---\n"
sudo rm /etc/nginx/sites-available/default;
sudo touch /etc/nginx/sites-available/default;
sudo cat >> /etc/nginx/sites-available/default <<'EOF'
server {
    listen   80;
    root /var/www/web;
    index index.php index.html index.htm;
    # Make site accessible from http://localhost/
    server_name localhost;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    # Specify a character set
    charset utf-8;

    # Don't log robots.txt or favicon.ico files
    location = /favicon.ico { log_not_found off; access_log off; }
    location = /robots.txt  { allow all; access_log off; log_not_found off; }

    location / {
        # try to serve file directly, fallback to app.php
        try_files $uri /app.php$is_args$args;
    }

    # DEV
    # This rule should only be placed on your development environment
    # In production, don't include this and don't deploy app_dev.php or config.php
    location ~ ^/(app_dev|config)\.php(/|$) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME  $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }
    # PROD
    location ~ ^/app\.php(/|$) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    # deny access to .htaccess files, if Apache's document root
    # concurs with nginx's one
    #
    location ~ /\.ht {
        deny all;
    }

    ### phpMyAdmin ###
    location /phpmyadmin {
      root /usr/share/;
      index index.php index.html index.htm;
      location ~ ^/phpmyadmin/(.+\.php)$ {
        client_max_body_size 4M;
        client_body_buffer_size 128k;
        try_files $uri =404;
        root /usr/share/;
        # Point it to the fpm socket;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include /etc/nginx/fastcgi_params;
      }
      location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt)) {
        root /usr/share/;
      }
    }
    location /phpMyAdmin {
      rewrite ^/* /phpmyadmin last;
    }
}
EOF

echo -e "\n--- Configure PHP-FPM default pool ---\n"
sudo php5dismod opcache
sudo rm /etc/php5/fpm/pool.d/www.conf;
sudo touch /etc/php5/fpm/pool.d/www.conf;
sudo cat >> /etc/php5/fpm/pool.d/www.conf <<'EOF'
[www]
user = www-data
group = www-data
listen = /var/run/php5-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = ondemand
pm.max_children = 4
php_value[max_execution_time] = 120
php_value[post_max_size] = 256M
php_value[upload_max_filesize] = 256M
php_value[display_errors] = On
php_value[error_reporting] = E_ALL
EOF

echo -e "\n--- Install Composer ---\n"
sudo curl -sS https://getcomposer.org/installer | php > /dev/null 2>&1;
sudo mv composer.phar /usr/local/bin/composer  > /dev/null 2>&1;

echo -e "\n--- Install symfony installer ---\n"
sudo curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony > /dev/null 2>&1;
sudo chmod a+x /usr/local/bin/symfony > /dev/null 2>&1;

echo -e "\n--- Restarting Nginx and PHP servers ---\n"
sudo service nginx restart > /dev/null 2>&1;
sudo service php5-fpm restart > /dev/null 2>&1;

##### CLEAN UP #####
sudo dpkg --configure -a  > /dev/null 2>&1; # when upgrade or install doesnt run well (e.g. loss of connection) this may resolve quite a few issues
sudo apt-get autoremove -y  > /dev/null 2>&1; # remove obsolete packages

# Set envvars
export DB_HOST=$DBHOST
export DB_NAME=$DBNAME
export DB_USER=$DBUSER
export DB_PASS=$DBPASSWD

echo -e "\n-----------------------------------------------------------------"
echo -e "\n----------- Your Vagrant is ready in /var/www ------------"
echo -e "\n-----------------------------------------------------------------"
echo -e "\n* Type http://localhost:8080/phpmyadmin for your MySQL db admin."
echo -e "\n* MySQL User: $DBUSER"
echo -e "\n* MySQL Password: $DBPASSWD"
echo -e "\n* MySQL Database: $DBNAME"
echo -e "\n-----------------------------------------------------------------"
