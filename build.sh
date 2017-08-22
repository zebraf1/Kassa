#!/bin/bash

if which composer; then
    composer install
else
    php composer.phar install
fi

php app/console propel:build

cd src/Rotalia/FrontendBundle/Resources/source/
bower prune
bower install
bower update
polymer build
