pix
===============

setup
------------------

1.  create a MySQL database and import **schema.sql**
2.  edit **config.php**
3.  change write permissions for **uploads** directory and **cron.last** file
        chown -R you:httpd uploads/ cron.last
        chmod -R 775 uploads/ cron.last
4.  enjoy
