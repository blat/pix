Pix
===============

Pix is an image hosting service.

This tools was originaly build for [Toile-Libre](http://www.toile-libre.org) with the help of:
* [ZeR0^](zero@toile-libre.org)
* [NiZoX](nizox@alterinet.org) 


Demo
------------------

* [pix.blizzart.net](http://pix.blizzart.net)


Setup
------------------

* Install composer:

        curl -sS https://getcomposer.org/installer | php

* Run composer to fetch dependencies:

        php composer.phar install

* Create a MySQL database and import schema:

        CREATE TABLE `image` (
            `id` INT(11) AUTO_INCREMENT,
            `type` VARCHAR(255),
            `slug` INT(11),
            `date` DATETIME,
            `private` TINYINT(3) DEFAULT 0,
            `size` INT(11),
            `user_id` INT(11),
            `popularity` INT(11) DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY (`slug`),
            KEY (`user_id`)
        );

        CREATE TABLE `image_tag` (
            `id` INT(11) AUTO_INCREMENT,
            `tag_id` INT(11),
            `image_id` INT(11),
            PRIMARY KEY (`id`),
            UNIQUE KEY (`image_id`,`tag_id`),
            KEY (`tag_id`)
        );

        CREATE TABLE `tag` (
            `id` INT(11) AUTO_INCREMENT,
            `label` VARCHAR(255),
            PRIMARY KEY (`id`),
            UNIQUE KEY (`label`)
        );

        CREATE TABLE `user` (
            `id` INT(11) AUTO_INCREMENT,
            `username` VARCHAR(255),
            `password` VARCHAR(255),
            PRIMARY KEY (`id`),
            UNIQUE KEY (`username`)
        );

*  Rename `config.ini-dist` in `config.ini`, then edit it (in particular, informations related to MySQL connection):

        host = localhost
        dbname = pix
        user = root
        password = 

* Change permissions for `data` and `cache` directory (HTTPd needs a write access):

        mkdir data/ cache/
        chown -R you:httpd data/ cache/
        chmod -R 775 data/ cache/


* Edit HTTPd config to add rewrite rules.

  * Nginx:
  
                server {
                        listen                  80;
                        server_name             pix.mydomain.tld;
                        root                    /path/to/pix/;
                        index                   index.php;
                        try_files               $uri $uri/ /index.php$is_args$args;
                        rewrite                 ^/image/(\d+)/(\w+)\.jpg$ /cache/$2/$1.jpg break;
                        rewrite                 ^/(.*\.(css|js|png))$ /public/$1 break;
                }
  
  * Lighttpd:

                $HTTP["host"] == "pix.mydomain.tld" {
                    server.document-root = "/path/to/pix/"
                    url.rewrite-once = (
                        "^/image/(\d+)/(\w+)\.jpg$" => "/cache/$2/$1.jpg",
                        "^.+\..+$" => "/public/$0",
                        "^([^\?]*)(\?(.*))?$" => "/index.php?uri=$1&$3"
                    )
                    server.error-handler-404 = "/index.php"
                }


Upgrade
------------------

If you want to upgrade from an old version of Pix:

* Edit `upgrade.php` to change `$DB_*` and `$DIR_DATA`

* Run:

        php upgrade.php

* Add theses rules in HTTPd config to conserve existing URLs:

  * Nginx:

                if ($args ~ "^img=(\d+).(\w+)$") {
                        set             $img $1;
                        rewrite         ^/$ /image/$img permanent;
                }
                rewrite                 ^/upload/original/(\d+)\.(\w+)$ /image/$1/original.jpg permanent;
                rewrite                 ^/upload/img/(\d+)\.(\w+)$ /image/$1/medium.jpg permanent;
                rewrite                 ^/upload/thumb/(\d+)\.(\w+)$ /image/$1/small.jpg permanent;
        
  * Lighttpd:

                url.redirect = (
                    "\?img=(\d+)\.(\w+)" => "/image/$1",
                    "upload/original/(\d+)\.(\w+)" => "/image/$1/original.jpg",
                    "upload/img/(\d+)\.(\w+)" => "/image/$1/medium.jpg",
                    "upload/thumb/(\d+)\.(\w+)" => "/image/$1/small.jpg",
                )
