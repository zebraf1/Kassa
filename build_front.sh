#!/bin/bash

cd src/Rotalia/FrontendBundle/Resources/public/
bower prune
bower install
bower update
polymer build