<?php

// CONFIG

$DB_HOST = 'localhost';
$DB_NAME = 'pix';
$DB_USER = 'root';
$DB_PASS = '';

$DIR_DATA = '/path/to/pix/upload/original';

// LOAD DEPENDENCIES

require "vendor/autoload.php";
require_once "models/image.php";
require_once "models/tag.php";
require_once "models/user.php";

use RedBean_Facade as R;

$config = parse_ini_file(__DIR__ . '/config.ini', true);

R::setup("mysql:host=" . $config['db']['host'] . ";dbname=" . $config['db']['dbname'], $config['db']['user'], $config['db']['password']);

R::addDatabase("old", "mysql:host=" . $DB_HOST . ";dbname=" . $DB_NAME, $DB_USER, $DB_PASS);

R::debug();

option('data_dir', __DIR__ . '/data/');

// READ OLD DATA

R::selectDatabase("old");
R::freeze();

$images = R::getAll("SELECT * FROM uploads");
$users = R::getAll("SELECT * FROM users");

R::close();

// CONNECT TO NEW DATABASE

R::selectDatabase("default");
R::freeze(false);

R::exec("SET FOREIGN_KEY_CHECKS=0");
R::exec("TRUNCATE image_tag");
R::exec("TRUNCATE tag");
R::exec("TRUNCATE image");
R::exec("TRUNCATE user");
R::exec("SET FOREIGN_KEY_CHECKS=1");

foreach ($users as $data) {
    $user = R::dispense("user");
    $user->username = $data['pseudo'];
    $user->password = $data['password'];
    R::store($user);

    $users[$user->username] = $user;
}

foreach ($images as $data) {
    $file = $DIR_DATA . '/' . $data['name'];

    $image = R::dispense("image");
    list($image->slug, $image->type) = explode('.', $data['name']);

    copy($file, $image->getFile());

    $image->date = date('Y-m-d H:i:s', $image->slug);
    $image->private = empty($data['public'])
    $image->size = $image->getFileSize();

    if (isset($users[$data['user']])) {
        $image->user = $users[$data['user']];
    }

    $image->setTags($data['tags']);

    R::store($image);

}
