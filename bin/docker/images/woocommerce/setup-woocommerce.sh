#!/usr/bin/env bash
COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'

sleep 10
/bin/bash /tmp/docker-entrypoint.sh "apache2-foreground"
sleep 20

 #if [ ! -f /var/www/html/wp-content/plugins/woocommerce/woocommerce.php  ]; then

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}            INSTALL WOOCOMMERCE           ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    wp core install --url=$WORDPRESS_URL --title="Test Wordpress" --admin_user=$ADMIN_USERNAME --admin_password=$ADMIN_PASSWORD --admin_email=$ADMIN_EMAIL --allow-root --path="/var/www/html"
    wp plugin install woocommerce --version=$WOOCOMMERCE_VERSION --allow-root --activate --path="/var/www/html"
    wp theme install storefront --allow-root --activate --path="/var/www/html"
    wp plugin install wordpress-importer --activate --allow-root --path="/var/www/html"
    php -f /tmp/setup-wizard-woocommerce.php
    chmod 777 -R /var/www/html/wp-content/uploads/

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}            COPY  MODULE FILE            ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cp -R /tmp/woocommerce_hipayenterprise /var/www/html/wp-content/plugins

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}            INSTALLATION SDK PHP         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cd /var/www/html/wp-content/plugins/woocommerce_hipayenterprise/ \
    && composer install --no-dev

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}    INSTALL HIPAY WOOCOMMERCE MODULE     ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    wp plugin activate woocommerce_hipayenterprise --allow-root

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}    SET HIPAY WOOCOMMERCE MODULE OPTION  ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    # HIPAY_OPTION=`wp option get hipay_enterprise`
    # wp option update hipay_enterprise HIPAY_OPTION

    #==========================================
    # Import sample data
    #==========================================
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}            IMPORT SAMPLE DATA           ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    wp import /var/www/html/wp-content/plugins/woocommerce/sample-data/sample_products.xml --allow-root --authors=create --path="/var/www/html"
    sleep 10

    #==========================================
    # Set permissions
    #==========================================
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}           SET PERMISSION                ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    chown -R www-data:www-data /var/www/html/

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

#fi

#==========================================
# APACHE RUNNING
#==========================================
exec apache2 -DFOREGROUND
