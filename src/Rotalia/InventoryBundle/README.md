Rotalia Kassa inventar
======================

Antud programm on Rotalia inventari abivahend.

1) Installeerimine
------------------

Installi serverisse XAMPP või WAMP (või muu PHP+MySql veebiserver).

Paiguta tarkvara serverisse selliselt, et juurkataloog ei oleks veebist ligipääsetav.
Web root peab olema /web kaust ning sealt seest .htaccess failis on kirjas, mis faili kasutatakse (app.php).

Vajadusel veendu, et mysql.sock oleks õiges kohas:
(Mac OS X) sudo ln -s /Applications/XAMPP/xamppfiles/var/mysql/mysql.sock /var/mysql/mysql.sock

Seadista andmebaas vastavalt oma environment seadele (dev, prod, ...):
app/config_env.yml failist propel sektsioon

Käivita andmebaasi initsialiseerimine projekti kaustast käsurealt:
$ php app/console propel:database:create
$ php app/console propel:sql:insert --force
$ php app/console propel:fixtures:load @RotaliaInventoryBundle
