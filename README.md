Kassa
=====

[![Build Status](https://travis-ci.com/zebraf1/Kassa.svg?branch=master)](https://travis-ci.com/zebraf1/Kassa)

Inventory system

Clone Repository
----------------

```bash
git clone https://github.com/zebraf1/Kassa.git
```

Installation
------------

Vendors are installed via composer. See https://getcomposer.org/download/ for updated installation guide.

Install vendors for project:
```bash
php composer.phar install
```

Note: if you install composer to a runnable directory (ie --install-dir=/usr/bin) and set --filename=composer:
```bash
composer install
```

At the end of the installation fill in parameters (saved to app/config/parameters.yml)

Install nodejs packages
```bash
npm install -g bower polymer-cli
```

App is started from public/index.php. Ensure this is the entry point configured by web server.

Building
--------

Build database if needed or run sql manually
```bash
TODO: run doctrine migrations
```

Run migrations
```bash
php bin/console TODO for doctrine
```

Build frontend
```bash
cd src/Rotalia/FrontendBundle/Resources/source/
bower prune
bower install
bower update
polymer build
```

Deploy new version: composer install, propel build, assets install, bower install, polymer build
```bash
sh build.sh # TODO: Doctrine
```

Testing
-------

Run unit and functional tests with PHPUnit

```bash
php bin/phpunit tests
```

See coverage:
```bash
cd tests/coverage/Rotalia/API
open index.html
```