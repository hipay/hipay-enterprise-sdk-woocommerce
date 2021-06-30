#!/bin/bash -e

CONTAINER=hipay-enterprise-sdk-woocommerce_wordpress_1

manageComposerForData() {
     COMPOSER_JSON_FILE="src/woocommerce_hipayenterprise/composer.json"

     echo "Setting up git pre-commit hook..."

     echo "#!/bin/bash" >.git/hooks/pre-commit
     echo "COMPOSER_JSON_FILE='"$COMPOSER_JSON_FILE"'" >>.git/hooks/pre-commit
     echo "git status --porcelain -uno | grep \$COMPOSER_JSON_FILE" >>.git/hooks/pre-commit
     echo "if [ $? -eq 0 ]" >>.git/hooks/pre-commit
     echo "then" >>.git/hooks/pre-commit
     echo "    cp \$COMPOSER_JSON_FILE \$COMPOSER_JSON_FILE.bak" >>.git/hooks/pre-commit
     echo "    cat \$COMPOSER_JSON_FILE.bak | python -c \"import sys, json; composerObj=json.load(sys.stdin); composerObj['scripts'] = None; del composerObj['scripts']; print( json.dumps(composerObj, sort_keys=True, indent=4));\" > \$COMPOSER_JSON_FILE" >>.git/hooks/pre-commit
     echo "    git add \$COMPOSER_JSON_FILE" >>.git/hooks/pre-commit
     echo "fi" >>.git/hooks/pre-commit
     echo "exit 0" >>.git/hooks/pre-commit

     chmod 775 .git/hooks/pre-commit

     echo "Setting up git post-commit hook..."

     echo "#!/bin/bash" >.git/hooks/post-commit
     echo "COMPOSER_JSON_FILE='"$COMPOSER_JSON_FILE"'" >>.git/hooks/post-commit
     echo "if [ -f \$COMPOSER_JSON_FILE.bak ]" >>.git/hooks/post-commit
     echo "then" >>.git/hooks/post-commit
     echo "    cp \$COMPOSER_JSON_FILE.bak \$COMPOSER_JSON_FILE" >>.git/hooks/post-commit
     echo "    rm \$COMPOSER_JSON_FILE.bak" >>.git/hooks/post-commit
     echo "fi" >>.git/hooks/post-commit
     echo "exit 0" >>.git/hooks/post-commit

     chmod 775 .git/hooks/post-commit
}

manageComposerForData

if [ "$1" = 'init' ] && [ "$2" = '' ]; then
     if docker inspect $CONTAINER >/dev/null 2>&1; then
          if [ "$(docker inspect -f '{{.State.Running}}' $CONTAINER)" = 'true' ]; then
               docker exec $CONTAINER bash -c 'chmod -R 777 /var/www/html'
          fi
     fi
     docker-compose -f docker-compose.dev.yml rm -sfv
     rm -Rf wordpress/ data/ src/woocommerce_hipayenterprise/vendor/ src/woocommerce_hipayenterprise/composer.lock
     docker-compose -f docker-compose.dev.yml build
     docker-compose -f docker-compose.dev.yml up -d
fi

if [ "$1" = 'restart' ]; then
     docker-compose -f docker-compose.dev.yml stop
     docker-compose -f docker-compose.dev.yml up -d
fi

if [ "$1" = 'kill' ]; then
     docker-compose -f docker-compose.dev.yml rm -sfv
     rm -Rf wordpress/ data/ src/woocommerce_hipayenterprise/vendor/ src/woocommerce_hipayenterprise/composer.lock
fi

if [ "$1" = 'l' ]; then
     docker-compose -f docker-compose.dev.yml logs -f
fi
