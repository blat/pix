Pix
===============

Pix is an image hosting service.

This tools was originaly build for [Toile-Libre](http://www.toile-libre.org) with the help of:
* [ZeR0^](zero@toile-libre.org)
* [NiZoX](nizox@alterinet.org)


Demo
------------------

* [pix.blizzart.net](https://pix.blizzart.net)


Setup
------------------

* Run composer to fetch dependencies:

        composer install

* Create a MySQL database and import schema:

        CREATE TABLE `images` (
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

        CREATE TABLE `tags` (
            `id` INT(11) AUTO_INCREMENT,
            `label` VARCHAR(255),
            PRIMARY KEY (`id`),
            UNIQUE KEY (`label`)
        );

        CREATE TABLE `users` (
            `id` INT(11) AUTO_INCREMENT,
            `username` VARCHAR(255),
            `password` VARCHAR(255),
            PRIMARY KEY (`id`),
            UNIQUE KEY (`username`)
        );

*  Rename `.env.example` in `.env`, then edit it (in particular, informations related to MySQL connection):

        DB_DRIVER=mysql
        DB_HOST=localhost
        DB_DATABASE=pix
        DB_USERNAME=root
        DB_PASSWORD=

* Change permissions for `data` directory (HTTPd needs a write access):

        mkdir data/
        chown -R you:httpd data/
        chmod -R 775 data/

* Here is a workig Nginx config:

        server {
                listen                  80;
                server_name             pix.mydomain.tld;
                root                    /path/to/pix/public;
                index                   index.php;
                try_files               $uri $uri/ /index.php?$query_string;
        }


Upgrade
------------------

If you want to upgrade from v2.x of Pix:

* Update PHP dependencies using composer:

        composer install

* Update MySQL schema:

        RENAME TABLE `image` TO `images`;
        RENAME TABLE `tag` TO `tags`;
        RENAME TABLE `user` TO `users`;
