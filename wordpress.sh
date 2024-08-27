#!/bin/bash -e

CONTAINER=hipay-enterprise-sdk-woocommerce-wordpress-1

if [ "$1" = 'init' ] && [ "$2" = '' ]; then
     docker compose -f docker-compose.dev.yml rm -sfv
     docker compose -f docker-compose.dev.yml rm -sfv database
     sudo rm -Rf wordpress/core/ data/ src/woocommerce_hipayenterprise/vendor/ src/woocommerce_hipayenterprise/composer.lock
     docker compose -f docker-compose.dev.yml up -d --build
fi

if [ "$1" = 'restart' ]; then
     docker compose -f docker-compose.dev.yml stop
     docker compose -f docker-compose.dev.yml up -d
fi

if [ "$1" = 'kill' ]; then
     docker compose -f docker-compose.dev.yml rm -sfv
     docker compose -f docker-compose.dev.yml rm -sfv database
     sudo rm -Rf wordpress/core/ data/ src/woocommerce_hipayenterprise/vendor/ src/woocommerce_hipayenterprise/composer.lock
fi

if [ "$1" = 'l' ]; then
     docker logs $CONTAINER -f
fi
