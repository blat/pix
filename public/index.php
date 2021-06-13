<?php

require_once __DIR__ . '/../vendor/autoload.php';

//---------------------------------------------------------------------------
// Init application

session_start();

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();

$app = new Laravel\Lumen\Application(dirname(__DIR__));
$app->withEloquent();


//---------------------------------------------------------------------------
// Search

$app->router->get('/', function() {
    return view('tags', [
        'tags'   => App\Tag::getPopular(),
    ]);
});

//---------------------------------------------------------------------------
// Sign-in

$app->router->get('/login', function() {
    return view("form_auth");
});

$app->router->post('/login', function() {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        $_SESSION['flash'] = [
            'level'   => 'danger',
            'message' => "Veuillez saisir un pseudo et un mot de passe."
        ];
        return redirect("/login");
    }

    if (!$user = App\User::getByUsernameAndPassword($_POST["username"], $_POST["password"])) {
        $_SESSION['flash'] = [
            'level'   => 'danger',
            'message' => "Ces identifiants sont invalides."
        ];
        return redirect("/login");
    }

    $_SESSION["user"] = $user;

    $_SESSION['flash'] = [
        'level'   => 'success',
        'message' => "Vous êtes maintenant connecté en tant que {$user->username} !"
    ];
    return redirect("/");
});

//---------------------------------------------------------------------------
// Sign-out

$app->router->get('/logout', function() {
    unset($_SESSION["user"]);

    $_SESSION['flash'] = [
        'level'   => 'success',
        'message' => "Vous êtes maintenant déconnecté !"
    ];
    return redirect("/");
});

//---------------------------------------------------------------------------
// Sign-up

$app->router->get('/register', function() {
    return view("form_auth");
});

$app->router->post('/register', function() {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        $_SESSION['flash'] = [
            'level'   => 'danger',
            'message' => "Veuillez saisir un pseudo et un mot de passe."
        ];
        return redirect("/register");
    }

    $user = App\User::getByUsername($_POST["username"]);
    if ($user) {
        $_SESSION['flash'] = [
            'level'   => 'danger',
            'message' => "Ce pseudo est déjà pris. Veuillez en saisir un autre."
        ];
        return redirect("/register");
    }

    $user = new App\User();
    $user->username = $_POST["username"];
    $user->password = App\User::hashPassword($_POST["password"]);
    $user->save();

    $_SESSION["user"] = $user;

    $_SESSION['flash'] = [
        'level'   => 'success',
        'message' => "Vous êtes maintenant inscrit !"
    ];
    return redirect("/");
});

//---------------------------------------------------------------------------
// Upload

$app->router->get('/upload', function() {
    return view('form_image');
});

$app->router->post('/upload', function() {
    try {
        $image = App\Image::upload($_FILES['image']);
        $image->private = !empty($_POST['private']);
        $image->setTags($_POST['tags']);
        if (!empty($_SESSION['user'])) {
            $image->setOwner($_SESSION['user']);
        }
        $image->save();

        return redirect('/image/' . $image->slug);
    } catch (Exception $e) {
        $_SESSION['flash'] = [
            'level'   => 'danger',
            'message' => $e->getMessage()
        ];
        return redirect('/upload');
    }
});

//---------------------------------------------------------------------------
// App\Image view

$app->router->get('/image/{slug}', function($slug) {
    $image = App\Image::getBySlug($slug);
    if (!$image) return response('Not found', 404);

    $image->popularity++;
    $image->save();

    return view('image', [
        'image' => $image,
    ]);
});

$app->router->get('/image/{slug}/{size}.jpg', function($slug, $size) {
    $image = App\Image::getBySlug($slug);
    if (!$image) return response('Not found', 404);

    return response($image->resize($size))
        ->withHeaders([
            'Content-type'  => 'image/jpeg',
            'Cache-Control' => 'public,max-age=31536000,immutable',
            'Expires'       => gmdate("D, d M Y H:i:s", strtotime('+1 day')) . " GMT",
            'Pragma'        => 'cache',
        ]);
});

//---------------------------------------------------------------------------
// Edit

$app->router->get('/edit/{slug}', function($slug) {
    $image = App\Image::getBySlug($slug);
    if (!$image) return response('Not found', 404);
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isAdmin() && !$_SESSION['user']->isOwner($image))) return response('Forbidden', 403);

    return view('form_image', [
        'image' => $image,
    ]);
});

$app->router->post('/edit/{slug}', function($slug) {
    $image = App\Image::getBySlug($slug);
    if (!$image) return response('Not found', 404);
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isAdmin() && !$_SESSION['user']->isOwner($image))) return response('Forbidden', 403);

    $image->setTags($_POST['tags']);
    $image->private = !empty($_POST['private']);
    $image->save();

    $_SESSION['flash'] = [
        'level'   => 'success',
        'message' => "Les modifications ont été enregistrées !"
    ];
    return redirect($image->getUrl());
});

//---------------------------------------------------------------------------
// Delete

$app->router->get('/delete/{slug}', function($slug) {
    $image = App\Image::getBySlug($slug);
    if (!$image) return response('Not found', 404);
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isAdmin() && !$_SESSION['user']->isOwner($image))) return response('Forbidden', 403);

    $image->delete();

    $_SESSION['flash'] = [
        'level'   => 'success',
        'message' => "L'image a été supprimée !"
    ];
    return redirect('/');
});

//---------------------------------------------------------------------------
// Search

$app->router->get('/explore', function() {
    return view('images', [
        'images' => App\Image::getPopular(),
        'stats'  => [
            'image_count' => App\Image::count(),
            'image_size'  => App\Image::sum('size'),
            'user_count'  => App\User::count(),
        ],
    ]);
});

$app->router->get('/tag/{label}', function($label) {
    $label = urldecode($label);
    $tag = App\Tag::getByLabel($label);
    if (!$tag) return response('Not found', 404);

    return view('images', [
        'images' => $tag->getPublicImages(),
        'title'  => 'Images taggées « ' . $label . ' »',
    ]);
});

$app->router->get('/user/{username}', function($username) {
    $username = urldecode($username);
    $user = App\User::getByUsername($username);
    if (!$user) return response('Not found', 404);

    return view('images', [
        'images' => !empty($_SESSION['user']) && $_SESSION['user']->id == $user->id ? $user->getAllImages() : $user->getPublicImages(),
        'title'  => 'Images envoyées par ' . $username,
    ]);
});

//---------------------------------------------------------------------------
// Let's go!

$app->run();
