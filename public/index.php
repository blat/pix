<?php

require_once __DIR__ . '/../vendor/autoload.php';

//---------------------------------------------------------------------------
// Init application

$options = parse_ini_file(__DIR__ . '/../config.ini', true);
$options['sessions']  = true;
$options['templates'] = __DIR__ . '/../templates';

$app = new \Phencil\App($options);

//---------------------------------------------------------------------------
// Search

$app->get('/', function() {
    return $this->render('tags', [
        'tags'   => App\Tag::getPopular(),
    ]);
});

//---------------------------------------------------------------------------
// Sign-in

$app->get('/login', function() {
    return $this->render("form_auth");
});

$app->post('/login', function() {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        $this->flashMessage("danger", "Veuillez saisir un pseudo et un mot de passe.");
        $this->redirect("/login");
    }

    if (!$user = App\User::getByUsernameAndPassword($_POST["username"], $_POST["password"])) {
        $this->flashMessage("danger", "Ces identifiants sont invalides.");
        $this->redirect("/login");
    }

    $_SESSION["user"] = $user;

    $this->flashMessage("success", "Vous êtes maintenant connecté en tant que {$user->username} !");
    $this->redirect("/");
});

//---------------------------------------------------------------------------
// Sign-out

$app->get('/logout', function() {
    unset($_SESSION["user"]);

    $this->flashMessage("success", "Vous êtes maintenant déconnecté !");
    $this->redirect("/");
});

//---------------------------------------------------------------------------
// Sign-up

$app->get('/register', function() {
    return $this->render("form_auth");
});

$app->post('/register', function() {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        $this->flashMessage("danger", "Veuillez saisir un pseudo et un mot de passe.");
        $this->redirect("/register");
    }

    $user = App\User::getByUsername($_POST["username"]);
    if ($user) {
        $this->flashMessage("danger", "Ce pseudo est déjà pris. Veuillez en saisir un autre.");
        $this->redirect("/register");
    }

    $user = new App\User();
    $user->username = $_POST["username"];
    $user->password = App\User::hashPassword($_POST["password"]);
    $user->save();

    $_SESSION["user"] = $user;

    $this->flashMessage("success", "Vous êtes maintenant inscrit !");
    $this->redirect("/");
});

//---------------------------------------------------------------------------
// Upload

$app->get('/upload', function() {
    return $this->render('form_image');
});

$app->post('/upload', function() {
    try {
        $image = App\Image::upload($_FILES['image']);
        $image->private = !empty($_POST['private']);
        $image->setTags($_POST['tags']);
        if (!empty($_SESSION['user'])) {
            $image->setOwner($_SESSION['user']);
        }
        $image->save();

        $this->redirect('/image/' . $image->slug);
    } catch (Exception $e) {
        $this->flashMessage('danger', $e->getMessage());
        $this->redirect('/upload');
    }
});

//---------------------------------------------------------------------------
// App\Image view

$app->get('/image/{slug}', function($slug) {
    $image = App\Image::getBySlug($slug);
    if (!$image) $this->error(404, 'Not found');

    $image->popularity++;
    $image->save();

    return $this->render('image', [
        'image' => $image,
    ]);
});

$app->get('/image/{slug}/{size}.jpg', function($slug, $size) {
    $image = App\Image::getBySlug($slug);
    if (!$image) $this->error(404, 'Not found');

    header('Content-type: image/jpeg');
    header('Cache-Control: public,max-age=31536000,immutable');
    header("Expires: " . gmdate("D, d M Y H:i:s", strtotime('+1 day')) . " GMT");
    header('Pragma: cache');
    echo $image->resize($size);
    exit;
});

//---------------------------------------------------------------------------
// Edit

$app->get('/edit/{slug}', function($slug) {
    $image = App\Image::getBySlug($slug);
    if (!$image) $this->error(404, 'Not found');
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isAdmin() && !$_SESSION['user']->isOwner($image))) $this->error(403, 'Forbidden');

    return $this->render('form_image', [
        'image' => $image,
    ]);
});

$app->post('/edit/{slug}', function($slug) {
    $image = App\Image::getBySlug($slug);
    if (!$image) $this->error(404, 'Not found');
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isAdmin() && !$_SESSION['user']->isOwner($image))) $this->error(403, 'Forbidden');

    $image->setTags($_POST['tags']);
    $image->private = !empty($_POST['private']);
    $image->save();

    $this->flashMessage('success', 'Les modifications ont été enregistrées !');
    $this->redirect($image->getUrl());
});

//---------------------------------------------------------------------------
// Delete

$app->get('/delete/{slug}', function($slug) {
    $image = App\Image::getBySlug($slug);
    if (!$image) $this->error(404, 'Not found');
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isAdmin() && !$_SESSION['user']->isOwner($image))) $this->error(403, 'Forbidden');

    $image->delete();

    $this->flashMessage('success', 'L\'image a été supprimée !');
    $this->redirect('/');
});

//---------------------------------------------------------------------------
// Search

$app->get('/explore', function() {
    return $this->render('images', [
        'images' => App\Image::getPopular(),
        'stats'  => [
            'image_count' => App\Image::count(),
            'image_size'  => App\Image::sum('size'),
            'user_count'  => App\User::count(),
        ],
    ]);
});

$app->get('/tag/{label}', function($label) {
    $label = urldecode($label);
    $tag = App\Tag::getByLabel($label);
    if (!$tag) $this->error(404, 'Not found');

    return $this->render('images', [
        'images' => $tag->getPublicImages(),
        'title'  => 'Images taggées « ' . $label . ' »',
    ]);
});

$app->get('/user/{username}', function($username) {
    $username = urldecode($username);
    $user = App\User::getByUsername($username);
    if (!$user) $this->error(404, 'Not found');

    return $this->render('images', [
        'images' => !empty($_SESSION['user']) && $_SESSION['user']->id == $user->id ? $user->getAllImages() : $user->getPublicImages(),
        'title'  => 'Images envoyées par ' . $username,
    ]);
});

//---------------------------------------------------------------------------
// Let's go!

$app->run();
