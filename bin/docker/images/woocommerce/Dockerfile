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
    && apt-get install -y python default-mysql-client \
    && curl -sS https://getcomposer.org/installer | php -- --filename=composer -- --install-dir=/usr/local/bin \
    &&  rm -rf /var/lib/apt/lists/*

##########################################
####            ENTRYPOINT      ##########
##########################################
COPY bin/docker/images/woocommerce/setup-woocommerce.sh /tmp/setup-woocommerce.sh
COPY bin/docker/images/woocommerce/setup-wizard-woocommerce.php /tmp/setup-wizard-woocommerce.php
COPY bin/docker/images/woocommerce/docker-entrypoint.sh /tmp/docker-entrypoint.sh

##########################################
####              WP CLI        ##########
##########################################
WORKDIR /home
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN php wp-cli.phar --info
RUN chmod +x wp-cli.phar
RUN mv wp-cli.phar /usr/local/bin/wp
RUN wp --info

RUN chmod u+x /tmp/setup-woocommerce.sh

COPY src/woocommerce_hipayenterprise /tmp/woocommerce_hipayenterprise

WORKDIR /var/www/html

RUN read -p "$*"

ENTRYPOINT ["/tmp/setup-woocommerce.sh"]
