Kassa
=====

Inventory system

Clone Repository
----------------

```bash
> git clone https://github.com/zebraf1/Kassa.git
```

Installation
------------

Vendors are installed via composer. See https://getcomposer.org/download/ for updated installation guide.

Install vendors for project:
```bash
> php composer.phar install
```

Note: if you install composer to a runnable directory (ie --install-dir=/usr/bin) and set --filename=composer:
```bash
> composer install
```

At the end of the installation fill in parameters (saved to app/config/parameters.yml)


Building
--------

Build base model files
```bash
> php app/console propel:model:build
```

Install web assets
```bash
> php app/console assets:install --relative --symlink
```

Build database if needed (use --force if needed) or run sql manually from app/propel/sql
```bash
> php app/console propel:sql:insert
```

Run migrations
```bash
> php app/console propel:mig:mig
```

Testing
-------

Run unit and functional tests with PHPUnit

```bash
> bin/phpunit -c app/
```
