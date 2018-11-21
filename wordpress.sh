#!/usr/bin/env bash

if [ "$1" = 'init' ] && [ "$2" = '' ];then
     docker-compose -f docker-compose.dev.yml stop
     docker-compose -f docker-compose.dev.yml rm -fv
     rm -Rf wordpress/
     rm -Rf data/
     docker-compose -f docker-compose.dev.yml build --no-cache
     docker-compose -f docker-compose.dev.yml up -d
fi

if [ "$1" = 'restart' ];then
     docker-compose -f docker-compose.dev.yml  stop
     docker-compose -f docker-compose.dev.yml  up -d
fi

if [ "$1" = 'l' ];then
    docker logs -f hipayenterprisesdkwoocommerce_wordpress_1
fi
