#!/bin/bash

# Enable production mode
if [ -n $1 ]
then
    export _ENV=$1
else
    export _ENV=prod
fi

export SYMFONY_ENV=${_ENV}

dir=$( cd "$(dirname "${BASH_SOURCE}")" ; pwd -P )

echo "$(tput bold)$(tput setaf 4)Setting up file permissions...$(tput sgr0)"

setfacl -R  -m u:www-data:rwX -m u:`whoami`:rwX var/cache var/logs var/sessions || exit 1
setfacl -dR -m u:www-data:rwX -m u:`whoami`:rwX var/cache var/logs var/sessions || exit 1

echo "$(tput bold)$(tput setaf 4)Cleaning up development remnants..."

if [ -d "web/uploads" ];
then
    rm -r web/uploads  || exit 1
fi

echo "$(tput bold)$(tput setaf 4)Installing configurations..."
cp ~/parameters.yml app/config/parameters.yml

echo "$(tput bold)$(tput setaf 4)Installing dependencies..."

if [ "$_ENV" == "prod" ]
then
    composer install --optimize-autoloader --no-dev || exit 1
    # npm install --production || echo "$(tput dim)$(tput setaf 3)Warning : Cannot install nodejs dependencies assetic:dump may fail";
else
    composer install --optimize-autoloader || exit 1
    # npm install --production || exit 1
fi

    # bower install || exit 1

# echo "$(tput bold)$(tput setaf 4)Compiling TypeScript to ECMAScript5..."
# ./node_modules/.bin/tsc || exit 1

echo "$(tput bold)$(tput setaf 4)Warming up cache..."

if [ ${_ENV} == "prod" ]
then
    bin/console cache:warmup   --env=prod --no-debug || exit 1
    bin/console assets:install --env=prod --no-debug || exit 1
    bin/console assetic:dump   --env=prod --no-debug || exit 1
else
    bin/console cache:warmup   || exit 1
    bin/console assets:install || exit 1
    bin/console assetic:dump   || exit 1
fi


# support auto migrations

#if [ -d "migrations" ];
#then
#   echo "Migrations pending"
#   php migrations/migrate.php
#   mv migrations/migrate.php migrations/migrate_done.php
#fi

echo "$(tput bold)$(tput setaf 4)Linking..."

ln -sf  ~/content            -T web/uploads  || exit 1

# echo "$(tput bold)$(tput setaf 4)Deploying to staging server..."
# ln -sf `pwd`/web /srv/www/perception-staging.cetb.in || exit 1

# echo "$(tput bold)$(tput setaf 4)Testing if staging is up..."
# curl -sf -o /dev/null "http://127.0.0.1:8000"

if [ $? ] && [ ${_ENV} == "prod" ];
then
    echo "$(tput bold)$(tput setaf 4)Running pre-deployment tests..."
    # check last minute errors in configuration
    # whoa nothing here

    echo "$(tput bold)$(tput setaf 4)Deploying to the world..."
    ln -sf `pwd`/web /srv/www/perception.cetb.in || exit 1

    echo "$(tput bold)$(tput setaf 2)Succesfully deployed"
else
    echo "$(tput bold)$(tput setaf 1)Deploy failed or not on production"
fi
echo -e "\033[0m"
