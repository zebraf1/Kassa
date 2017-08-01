#!/bin/bash

cd src/Rotalia/FrontendBundle/Resources/source/
bower prune
bower install
bower update
polymer build