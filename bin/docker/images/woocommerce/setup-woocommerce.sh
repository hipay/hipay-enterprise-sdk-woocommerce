#!/usr/bin/env bash
sleep 10
/bin/bash /tmp/docker-entrypoint.sh "apache2-foreground"

sleep 20
wp core install --url=http://localhost:8000 --title="Test Wordpress" --admin_user=$ADMIN_USERNAME --admin_password=$ADMIN_PASSWORD --admin_email=$ADMIN_EMAIL --allow-root --path="/var/www/html"
wp plugin install woocommerce --version=$WOOCOMMERCE_VERSION --allow-root --activate --path="/var/www/html"
wp theme install storefront --allow-root --activate --path="/var/www/html"
wp plugin install wordpress-importer --activate --allow-root --path="/var/www/html"
php -f /tmp/setup-wizard-woocommerce.php

cp -R /tmp/woocommerce_hipayenterprise /var/www/html/wp-content/plugins

printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
printf "\n${COLOR_SUCCESS}            INSTALLATION SDK PHP         ${NC}\n"
printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

cd /var/www/html/wp-content/plugins/woocommerce_hipayenterprise/ \
&& composer install --no-dev

wp import /var/www/html/wp-content/plugins/woocommerce/sample-data/smaple_product.csv --authors=create --path="/var/www/html"

#==========================================
# Install XDebug
#==========================================
if [ "$XDEBUG_ENABLED" = "1" ]; then
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLATION XDEBUG $ENVIRONMENT    ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    echo '' && pecl install xdebug-2.6.0
    echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
    echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini
    echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
fi

exec apache2 -DFOREGROUND
