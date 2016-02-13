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

```bash
> php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php

> php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '781c98992e23d4a5ce559daf0170f8a9b3b91331ddc4a3fa9f7d42b6d981513cdc1411730112495fbf9d59cffbf20fb2') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); }"
```

Installer verified

```bash
> php composer-setup.php
```

Install vendors for project:
```bash
> php composer.phar install
```

At the end of the installation fill in parameters (saved to app/config/parameters.yml)


Building
--------

Build base model files

```bash
> php app/console propel:model:build
```

Build database if needed (use --force if needed) or run sql manually from app/propel/sql
```bash
> php app/console propel:sql:insert
```

Run migrations
```bash
> php app/console propel:mig:mig
```
