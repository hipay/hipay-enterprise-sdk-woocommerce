FROM wordpress:latest

LABEL maintainer="PI-Ecommerce"
LABEL maintainer_email="pi-ecommerce@hipay.com"

ENV APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_PID_FILE=/var/run/apache2/apache2.pid \
    APACHE_RUN_DIR=/var/run/apache2 \
    APACHE_LOCK_DIR=/var/lock/apache2 \
    APACHE_LOG_DIR=/var/log/apache2

RUN apt-get update \
<<<<<<< HEAD
    && apt-get install -y default-mysql-client less python3 \
    && curl -sS https://getcomposer.org/installer | php -- --filename=composer -- --install-dir=/usr/local/bin \
    &&  rm -rf /var/lib/apt/lists/* \
    && pecl install xdebug
=======
    && apt-get install -y python3 default-mysql-client msmtp \
    && curl -sS https://getcomposer.org/installer | php -- --filename=composer -- --install-dir=/usr/local/bin \
    &&  rm -rf /var/lib/apt/lists/* \
    && echo "sendmail_path = /usr/bin/msmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini \
    && echo "host smtp\nport 1025\ntls off\nfrom pi-ecommerce@hipay.com" > /etc/msmtprc \

RUN pecl install xdebug
>>>>>>> origin/feature/UPDATE-WOOCOMMERCE

##########################################
####            ENTRYPOINT      ##########
##########################################
COPY bin/docker/images/woocommerce/setup-woocommerce.sh /tmp/setup-woocommerce.sh
COPY bin/docker/images/woocommerce/setup-wizard-woocommerce.php /tmp/setup-wizard-woocommerce.php
COPY bin/docker/images/woocommerce/docker-entrypoint.sh /tmp/docker-entrypoint.sh

##########################################
####              WP CLI        ##########
##########################################
ADD https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar /usr/local/bin/wp
WORKDIR /home
RUN chmod +x /usr/local/bin/wp \
    && wp --info \
    && chmod u+x /tmp/setup-woocommerce.sh

COPY src/woocommerce_hipayenterprise /tmp/woocommerce_hipayenterprise

WORKDIR /var/www/html

ENTRYPOINT ["/tmp/setup-woocommerce.sh"]
