<?php

//---------------------------------------------------------------------------
// Load dependencies

require_once 'vendor/autoload.php';
require_once 'models/image.php';
require_once 'models/tag.php';
require_once 'models/user.php';


//---------------------------------------------------------------------------
// Init application

function configure() {
    $config = parse_ini_file(__DIR__ . '/config.ini', true);

    // Init database
    RedBean_Facade::setup('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['dbname'], $config['db']['user'], $config['db']['password']);
    unset($config['db']);

    // Freeze schema in production
    if ($config['env'] == 'production') {
        RedBean_Facade::freeze();
    }
    unset($config['env']);

    // Pass config
    foreach ($config as $key => $value) {
        option($key, $value);
    }

    // Define layout
    layout('layout.phtml');

    // Enable sessions
    option('session', true);

    option('host', 'http://' . $_SERVER['HTTP_HOST']);
    option('base_uri', '/');

    option('data_dir', __DIR__ . '/data/');
    option('cache_dir', __DIR__ . '/cache/');
}


//---------------------------------------------------------------------------
// Routes

dispatch('/login', 'auth_login');
dispatch_post('/login', 'auth_login_post');

dispatch('/logout', 'auth_logout');

dispatch('/register', 'auth_register');
dispatch_post('/register', 'auth_register_post');

dispatch('/upload', 'image_upload');
dispatch_post('/upload', 'image_upload_post');

dispatch('/image/:slug', 'image_view');
dispatch('^/image/(\d+)/(\w+)\.jpg', 'image_download');

dispatch('/edit/:slug', 'image_edit');
dispatch_post('/edit/:slug', 'image_edit_post');

dispatch('/delete/:slug', 'image_delete');

dispatch('/', 'image_search_by_popularity');
dispatch('/tag/:label', 'image_search_by_tag');
dispatch('/user/:username', 'image_search_by_user');


//---------------------------------------------------------------------------
// Always load stats

function before($route) {
    $stats = RedBean_Facade::getRow('SELECT COUNT(*) AS image_count, SUM(size) AS image_size FROM image');
    $stats['user_count'] = RedBean_Facade::getCell('SELECT COUNT(*) FROM user');
    $stats['labels'] = RedBean_Facade::getCol('SELECT tag.label FROM tag JOIN image_tag ON tag.id = tag_id JOIN image ON image.id = image_id WHERE private <> 1 GROUP BY tag_id ORDER BY COUNT(image_id) DESC LIMIT 5');
    set('stats', $stats);
}


//---------------------------------------------------------------------------
// Let's go!

run();
