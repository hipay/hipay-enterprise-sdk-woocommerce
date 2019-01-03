#!/usr/bin/env bash
COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'
ENV_DEVELOPMENT="development"
ENV_STAGE="stage"
ENV_PROD="production"

sleep 10
/bin/bash /tmp/docker-entrypoint.sh "apache2-foreground"
sleep 20

 if [ ! -f /var/www/html/wp-content/plugins/woocommerce/woocommerce.php  ]; then

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

    #==========================================
    # Import sample data
    #==========================================
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}            IMPORT SAMPLE DATA           ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    wp import /var/www/html/wp-content/plugins/woocommerce/sample-data/sample_products.xml --allow-root --authors=create --path="/var/www/html"
    sleep 10

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     CONFIGURE TAX AND SHIPPING          ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    wp option update 'woocommerce_calc_taxes' 'yes' --allow-root
    wp option update 'woocommerce_tax_display_shop' 'incl' --allow-root
    wp option update 'woocommerce_tax_display_cart' 'incl' --allow-root
    wp wc tax create --country="FR" --rate="20" --name="TVA" --allow-root --user=admin-wordpress@hipay.com
    wp wc shipping_zone create --allow-root --name="FR" --user=admin-wordpress@hipay.com

    CONFIG=$(wp option --allow-root get hipay_enterprise --format=json)
    CONFIG=${CONFIG/'"api_username_sandbox":""'/'"api_username_sandbox":"'$HIPAY_API_USER_TEST'"'}
    CONFIG=${CONFIG/'"api_password_sandbox":""'/'"api_password_sandbox":"'$HIPAY_API_PASSWORD_TEST'"'}
    CONFIG=${CONFIG/'"api_tokenjs_username_sandbox":""'/'"api_tokenjs_username_sandbox":"'$HIPAY_TOKENJS_USERNAME_TEST'"'}
    CONFIG=${CONFIG/'"api_tokenjs_password_publickey_sandbox":""'/'"api_tokenjs_password_publickey_sandbox":"'$HIPAY_TOKENJS_PUBLICKEY_TEST'"'}
    CONFIG=${CONFIG/'"api_secret_passphrase_sandbox":""'/'"api_secret_passphrase_sandbox":"'$HIPAY_SECRET_PASSPHRASE_TEST'"'}

    if [ "$ENVIRONMENT" != "$ENV_PROD" ];then
        CONFIG=${CONFIG/'"send_url_notification":1'/'"send_url_notification":0'}
    fi

    echo $CONFIG
    wp option --allow-root update hipay_enterprise "$CONFIG" --format=json
    wp option --allow-root update woocommerce_hipayenterprise_credit_card_settings '{"enabled":"yes"}' --format=json

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALL PLUGIN EXTERNAL             ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    wp plugin install wp-mail-logging --allow-root --activate --path="/var/www/html"


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

    echo "define( 'WP_DEBUG_DISPLAY', true );" >> /var/www/html/wp-config.php
    echo "define( 'WP_DEBUG', true );" >> /var/www/html/wp-config.php
    echo "define( 'WP_DEBUG_LOG', true );" >> /var/www/html/wp-config.php
fi

#==========================================
# APACHE RUNNING
#==========================================
exec apache2 -DFOREGROUND
