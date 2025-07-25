# Source: https://wiki.debian.org/LaMp

## URL: https://wiki.debian.org/LaMp

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#Installation

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#MariaDB

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#apache2

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#The_.22P.22_part

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#Configuration

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#Test_PHP

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#phpMyAdmin

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


## URL: https://wiki.debian.org/LaMp#See_also

Title: LaMp - Debian Wiki

URL Source: https://wiki.debian.org/LaMp

Markdown Content:
[Translation(s)](https://wiki.debian.org/DebianWiki/EditorGuide#traduction) : [English](https://wiki.debian.org/LaMp) - [Français](https://wiki.debian.org/fr/Lamp) - [Italiano](https://wiki.debian.org/it/LaMp) - [Português (Brasil)](https://wiki.debian.org/pt_BR/LaMp) - [Русский](https://wiki.debian.org/ru/LaMp) - [简体中文](https://wiki.debian.org/zh_CN/LAMP)

* * *

LAMP, Linux Apache MySQL PHP
----------------------------

*   Some people argue that P HP can be replaced with P ython or P erl.

*   ... and Apache can be replaced by lighttpd! 
*   ... and M ySQL have been replaced by M ariaDB!

Contents

1.   [LAMP, Linux Apache MySQL PHP](https://wiki.debian.org/LaMp#LAMP.2C_Linux_Apache_MySQL_PHP)
    1.   [Installation](https://wiki.debian.org/LaMp#Installation)
        1.   [MariaDB](https://wiki.debian.org/LaMp#MariaDB)
        2.   [apache2](https://wiki.debian.org/LaMp#apache2)
        3.   [The "P" part](https://wiki.debian.org/LaMp#The_.22P.22_part)

    2.   [Configuration](https://wiki.debian.org/LaMp#Configuration)
        1.   [Apache2 configuration file: /etc/apache2/apache2.conf](https://wiki.debian.org/LaMp#Apache2_configuration_file:_.2Fetc.2Fapache2.2Fapache2.conf)
        2.   [Test PHP](https://wiki.debian.org/LaMp#Test_PHP)
        3.   [phpMyAdmin](https://wiki.debian.org/LaMp#phpMyAdmin)
        4.   [PHP: /etc/php5/apache2/php.ini](https://wiki.debian.org/LaMp#PHP:_.2Fetc.2Fphp5.2Fapache2.2Fphp.ini)
        5.   [MySQL : /etc/mysql/my.cnf](https://wiki.debian.org/LaMp#MySQL_:_.2Fetc.2Fmysql.2Fmy.cnf)

    3.   [See also](https://wiki.debian.org/LaMp#See_also)

Installation
------------

Before starting the installation, make sure your distribution is up to date (the '#' indicates that you should do this as root):

 # apt update && apt upgrade
### MariaDB

Next install **MariaDB** using the following command:

 # apt install mariadb-server mariadb-client
Immediately after you have installed the MariaDB server, you should run the next command to secure your installation.

 # mysql_secure_installation 
The previous script change root password and make some other security improvements.

You must never use your root account and password when running databases. The root account is a privileged account which should only be used for admin procedures. You will need to create a separate user account to connect to your MariaDB databases from a PHP script. You can add users to a MariaDB database by using a control panel like phpMyAdmin to easily create or assign database permissions for users.

### apache2

The web server can be installed as follows:

 # apt install apache2 apache2-doc
#### Configuring user directories for Apache Web Server

Enable module

# a2enmod userdir
Configure Apache module userdir in /etc/apache2/mods-enabled/userdir.conf as follows:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Order allow,deny
                        Allow from all
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Order deny,allow
                        Deny from all
                </LimitExcept>
        </Directory>
</IfModule>
From apache 2.4 and later use instead:

<IfModule mod_userdir.c>
        UserDir public_html
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes SymLinksIfOwnerMatch
                <Limit GET POST OPTIONS>
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        Require all denied
                </LimitExcept>
        </Directory>
</IfModule>
Create directory as user (not as root):

$mkdir /home/$USER/public_html
Change group as root (substitute your username) and restart web server:

# chgrp www-data /home/<username>/public_html
# service apache2 restart
If you get a _Forbidden_ error when accessing home folder through Apache check /home/username has permissions drwxr-xr-x. If the permissions are wrong correct them as such:

# chmod 755 /home/<username>
To be able to serve PHP (PHP needs to be installed as per instructions) check that /etc/apache2/mods-available/php5.conf is correct:

<IfModule mod_php5.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
        Require all granted
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
        Require all denied
    </FilesMatch>
    # To re-enable php in user directories comment the following lines
    # (from <IfModule ...> to </IfModule>.) Do NOT set it to On as it
    # prevents .htaccess files from disabling it.
    #<IfModule mod_userdir.c>
    #    <Directory /home/*/public_html>
    #        php_admin_value engine Off
    #    </Directory>
    #</IfModule>
</IfModule>
Place some web content in ~/public_html and see the results at http://localhost/~username

### The "P" part

Installing the **PHP** subset of LAMP in Debian is quite simple, you just type this as root in an console (the # is the root prompt symbol):

 # apt install php php-mysql
If you prefer **Perl**, then you might consider:

 # apt install perl libapache2-mod-perl2
If you prefer **Python**, then you might consider:

 # apt install python3 libapache2-mod-python
Configuration
-------------

### Apache2 configuration file: /etc/apache2/apache2.conf

You can edit this file when needed, but for most simple applications, this should not be necessary as most stuff is now done using conf.d.

### Test PHP

To test the PHP interface, edit the file /var/www/html/test.php:

 # nano /var/www/html/test.php
and insert the following code.

<?php phpinfo(); ?>
Afterwards, point your browser to http://<SERVERIP>/test.php to start using it.

### phpMyAdmin

Probably you also want to install phpMyAdmin for easy configuration:

 # apt install phpmyadmin
To have access to phpMyAdmin on your website (i.e. http://example.com/phpmyadmin/ ) all you need to do is include the following line in /etc/apache2/apache2.conf (needed only before Squeeze, since 6.0 it will be linked by the package install script to /etc/apache2/conf.d/phpmyadmin.conf->../../phpmyadmin/apache.conf automatically):

Include /etc/phpmyadmin/apache.conf
Restart Apache:

 # /etc/init.d/apache2 restart
Go to http://<SERVERIP>/phpmyadmin/ to start using it. (Use the IP or name of your PC/server instead of <SERVERIP> (The localhost IP is always 127.0.0.1).)

### PHP: /etc/php5/apache2/php.ini

A usual issue with PHP configuration is to enable MySQL. Just edit the file and uncomment the following line (tip: search for mysql)

extension=mysql.so
Note that this should not be needed anymore as conf.d is now used.

### MySQL : /etc/mysql/my.cnf

You can find configuration examples in /usr/share/doc/mysql-server/examples

See also
--------

*   [WordPress](https://wiki.debian.org/WordPress)

* * *

[CategorySystemAdministration](https://wiki.debian.org/CategorySystemAdministration)[CategoryNetwork](https://wiki.debian.org/CategoryNetwork)[CategorySoftware](https://wiki.debian.org/CategorySoftware)

---


# Crawl Statistics

- **Source:** https://wiki.debian.org/LaMp
- **Depth:** 4
- **Pages processed:** 13
- **Crawl method:** api
- **Duration:** 31.94 seconds
- **Crawl completed:** 20.07.2025, 11:46:05

