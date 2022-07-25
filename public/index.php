<?php

require_once __DIR__ . '/../vendor/autoload.php';

//---------------------------------------------------------------------------
// Init application

session_start();

use DI\Container;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Flash\Messages as FlashMessages;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$container->set('flash', function () {
    return new FlashMessages;
});

$container->set('renderer', function ($container) {
    $renderer = new PhpRenderer(__DIR__ . '/../templates', [
        'user' => isset($_SESSION['user']) ? $_SESSION['user'] : false,
        'flash' => $container->get('flash'),
    ]);
    $renderer->setLayout('layout.php');
    return $renderer;
});

$capsule = new CapsuleManager();
$capsule->addConnection([
    'driver'   => env('DB_DRIVER'),
    'host'     => env('DB_HOST'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

//---------------------------------------------------------------------------
// Search

$app->get('/', function (Request $request, Response $response, $args) {
    return $this->get('renderer')->render($response, 'tags.php', [
        'tags'   => App\Tag::getPopular(),
    ]);
});

//---------------------------------------------------------------------------
// Sign-in

$app->get('/login', function (Request $request, Response $response, $args) {
    return $this->get('renderer')->render($response, 'form_auth.php');
});

$app->post('/login', function (Request $request, Response $response, $args) {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        $this->get('flash')->addMessage('danger', "Veuillez saisir un pseudo et un mot de passe.");
        return $response->withStatus(302)->withHeader('Location', '/login');
    }

    if (!$user = App\User::getByUsernameAndPassword($_POST["username"], $_POST["password"])) {
        $this->get('flash')->addMessage('danger', "Ces identifiants sont invalides.");
        return $response->withStatus(302)->withHeader('Location', '/login');
    }

    $_SESSION["user"] = $user;

    $this->get('flash')->addMessage('success', "Vous êtes maintenant connecté en tant que {$user->username} !");
    return $response->withStatus(302)->withHeader('Location', '/');
});

//---------------------------------------------------------------------------
// Sign-out

$app->get('/logout', function (Request $request, Response $response, $args) {
    unset($_SESSION["user"]);

    $this->get('flash')->addMessage('success', "Vous êtes maintenant déconnecté !");
    return $response->withStatus(302)->withHeader('Location', '/');
});

//---------------------------------------------------------------------------
// Sign-up

$app->get('/register', function (Request $request, Response $response, $args) {
    return $this->get('renderer')->render($response, 'form_auth.php');
});

$app->post('/register', function (Request $request, Response $response, $args) {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        $this->get('flash')->addMessage('danger', "Veuillez saisir un pseudo et un mot de passe.");
        return $response->withStatus(302)->withHeader('Location', '/register');
    }

    $user = App\User::getByUsername($_POST["username"]);
    if ($user) {
        $this->get('flash')->addMessage('danger', "Ce pseudo est déjà pris. Veuillez en saisir un autre.");
        return $response->withStatus(302)->withHeader('Location', '/register');
    }

    $user = new App\User();
    $user->username = $_POST["username"];
    $user->password = App\User::hashPassword($_POST["password"]);
    $user->save();

    $_SESSION["user"] = $user;

    $this->get('flash')->addMessage('success', "Vous êtes maintenant inscrit !");
    return $response->withStatus(302)->withHeader('Location', '/');
});

//---------------------------------------------------------------------------
// Upload

$app->get('/upload', function (Request $request, Response $response, $args) {
    return $this->get('renderer')->render($response, 'form_image.php');
});

$app->post('/upload', function (Request $request, Response $response, $args) {
    try {
        $image = App\Image::upload($_FILES['image']);
        $image->private = !empty($_POST['private']);
        $image->setTags($_POST['tags']);
        if (!empty($_SESSION['user'])) {
            $image->setOwner($_SESSION['user']);
        }
        $image->save();

        return $response->withStatus(302)->withHeader('Location', '/image/' . $image->slug);
    } catch (Exception $e) {
        $this->get('flash')->addMessage('danger', $e->getMessage());
        return $response->withStatus(302)->withHeader('Location', '/upload');
    }
});

//---------------------------------------------------------------------------
// Image view

$app->get('/image/{slug}', function (Request $request, Response $response, $args) {
    $image = App\Image::getBySlug($args['slug']);
    if (!$image) return $response->withStatus(404);

    $image->popularity++;
    $image->save();

    return $this->get('renderer')->render($response, 'image.php', [
        'image' => $image,
    ]);
});

$app->get('/image/{slug}/{size}.jpg', function (Request $request, Response $response, $args) {
    $image = App\Image::getBySlug($args['slug']);
    if (!$image) return $response->withStatus(404);

    $response->getBody()->write($image->resize($args['size']));
    return $response
        ->withHeader('Content-Type', 'image/jpeg')
        ->withHeader('Cache-Control', 'public,max-age=31536000,immutable')
        ->withHeader('Expires', gmdate("D, d M Y H:i:s", strtotime('+1 day')) . " GMT")
        ->withHeader('Pragma', 'cache');
});

//---------------------------------------------------------------------------
// Edit

$app->get('/edit/{slug}', function (Request $request, Response $response, $args) {
    $image = App\Image::getBySlug($args['slug']);
    if (!$image) return $response->withStatus(404);
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isOwner($image)) && !$_SESSION['user']->isAdmin()) return $response->withStatus(403);

    return $this->get('renderer')->render($response, 'form_image.php', [
        'image' => $image,
    ]);
});

$app->post('/edit/{slug}', function (Request $request, Response $response, $args) {
    $image = App\Image::getBySlug($args['slug']);
    if (!$image) return $response->withStatus(404);
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isOwner($image)) && !$_SESSION['user']->isAdmin()) return $response->withStatus(403);

    $image->setTags($_POST['tags']);
    $image->private = !empty($_POST['private']);
    $image->save();

    $this->get('flash')->addMessage('success', "Les modifications ont été enregistrées !");
    return $response->withStatus(302)->withHeader('Location', $image->getUrl());
});

//---------------------------------------------------------------------------
// Delete

$app->get('/delete/{slug}', function (Request $request, Response $response, $args) {
    $image = App\Image::getBySlug($args['slug']);
    if (!$image) return $response->withStatus(404);
    if (empty($_SESSION['user']) || (!$_SESSION['user']->isOwner($image)) && !$_SESSION['user']->isAdmin()) return $response->withStatus(403);

    $image->delete();

    $this->get('flash')->addMessage('success', "L'image a été supprimée !");
    return $response->withStatus(302)->withHeader('Location', '/');
});

//---------------------------------------------------------------------------
// Search

$app->get('/explore', function (Request $request, Response $response, $args) {
    return $this->get('renderer')->render($response, 'images.php', [
        'images' => App\Image::getPopular(),
        'stats'  => [
            'image_count' => App\Image::count(),
            'image_size'  => App\Image::sum('size'),
            'user_count'  => App\User::count(),
        ],
    ]);
});

$app->get('/tag/{label}', function (Request $request, Response $response, $args) {
    $label = $args['label'];
    $tag = App\Tag::getByLabel($label);
    if (!$tag) return $response->withStatus(404);

    return $this->get('renderer')->render($response, 'images.php', [
        'images' => $tag->getPublicImages(),
        'title'  => 'Images taggées « ' . $label . ' »',
    ]);
});

$app->get('/user/{username}', function (Request $request, Response $response, $args) {
    $username = $args['username'];
    $user = App\User::getByUsername($username);
    if (!$user) return $response->withStatus(404);

    return $this->get('renderer')->render($response, 'images.php', [
        'images' => !empty($_SESSION['user']) && $_SESSION['user']->id == $user->id ? $user->getAllImages() : $user->getPublicImages(),
        'title'  => 'Images envoyées par ' . $username,
    ]);
});


//---------------------------------------------------------------------------
// Let's go!

$app->addErrorMiddleware(false, true, true);
$app->run();
