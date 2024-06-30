#!/bin/bash

if which composer; then
    composer install
else
    php83-cli composer.phar install
fi

# php app/console propel:build

# shellcheck disable=SC2164
cd src/Rotalia/FrontendBundle/Resources/source/
../../../../../node_modules/bower/bin/bower prune
../../../../../node_modules/bower/bin/bower install
../../../../../node_modules/bower/bin/bower update
../../../../../node_modules/polymer-cli/bin/polymer.js build
