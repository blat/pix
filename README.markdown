Pix
===============

Pix is an image hosting service.

This tools was originaly build for [Toile-Libre](http://www.toile-libre.org) with the help of:
* [ZeR0^](zero@toile-libre.org)
* [NiZoX](nizox@alterinet.org) 


Demo
------------------

* [pix.toile-libre.org](http://pix.toile-libre.org)


Setup
------------------

*  Create a MySQL database and import `schema.sql`

*  Edit `config.php` (in particular, informations related to MySQL connection):

        'sql_host'     => 'localhost',
        'sql_user'     => 'root',
        'sql_password' => '',
        'sql_database' => 'toile-pix',

* Change permissions for `uploads` directory and `cron.last` file (HTTPd needs a write access):

        chown -R you:httpd uploads/ cron.last
        chmod -R 775 uploads/ cron.last
