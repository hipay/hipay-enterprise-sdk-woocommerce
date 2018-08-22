#!/usr/bin/env bash
sleep 10
/bin/bash /tmp/docker-entrypoint.sh "apache2-foreground"

sleep 20
wp core install --url=http://localhost:8000 --title="Test Wordpress" --admin_user=$ADMIN_USERNAME --admin_password=$ADMIN_PASSWORD --admin_email=$ADMIN_EMAIL --allow-root --path="/var/www/html"
wp plugin install woocommerce --version=$WOOCOMMERCE_VERSION --allow-root --activate --path="/var/www/html"
wp theme install storefront --allow-root --activate --path="/var/www/html"
wp plugin install wordpress-importer --activate --allow-root --path="/var/www/html"
php -f /tmp/setup-wizard-woocommerce.php

cp -R /tmp/woocommerce_hipayenterprise /var/www/html/wp-content/plugins/woocommerce_hipayenterprise

wp import /var/www/html/wp-content/plugins/woocommerce/sample-data/smaple_product.csv --authors=create --path="/var/www/html"

exec apache2 -DFOREGROUND