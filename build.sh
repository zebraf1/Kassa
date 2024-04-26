#!/bin/bash

if which composer; then
    composer install
else
    composer.phar install
fi

# php app/console propel:build

# shellcheck disable=SC2164
cd src/Rotalia/FrontendBundle/Resources/source/
bower prune
bower install
bower update
polymer build
