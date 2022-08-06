<html>
    <head>
        <title>Pix | <?php if (empty($title)): ?>Hébergement d'images<?php else: ?><?= $this->e($title) ?><?php endif ?></title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.0/css/bootstrap.min.css" integrity="sha512-XWTTruHZEYJsxV3W/lSXG1n3Q39YIWOstqvmFsdNEEQfHoZ6vm6E9GK2OrF6DSJSpIbRbi+Nn0WDPID9O7xB2Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Grand+Hotel&display=swap" rel="stylesheet"> 
        <link rel="stylesheet" type="text/css" href="<?= $this->basePath() ?>/style.css" />
        <link href="<?= $this->basePath() ?>/favicon.png" rel="icon" />
        <?php if (!empty($image)): ?>
        <meta name="twitter:card" content="photo" />
        <meta name="twitter:image:src" content="<?= $this->fullUrlFor('fullImage', ['slug' => $image->slug, 'size' => 'medium'] ) ?>" />
        <?php endif ?>
        <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="<?= $this->urlFor('home') ?>"><img src="<?= $this->basePath() ?>/logo.png" /> Pix</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="btn btn-primary" href="<?= $this->urlFor('upload') ?>"><i class="fa fa-cloud-upload"></i> Upload</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $this->urlFor('explore') ?>">Explorer</a></li>
                        <?php if (!empty($user)): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $this->urlFor('user', ['username' => $user->username]) ?>">Mes images</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $this->urlFor('logout') ?>>Déconnexion</a></li>
                        <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $this->urlFor('login') ?>">Connexion</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $this->urlFor('register') ?>">Inscription</a></li>
                        <?php endif ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="content p-4">
                <?php foreach ($flash->getMessages() as $level => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                    <div class="alert alert-<?= $level ?> alert-dismissible" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?= $this->e($message) ?>
                    </div>
                    <?php endforeach ?>
                <?php endforeach ?>

                <?php if (!empty($title)): ?>
                    <h2 class="title"><?= $this->e($title) ?></h2>
                <?php endif ?>

                <?= $this->section('content') ?>
            </div>

            <div class="footer text-end pt-2 pb-2">
                <small class="text-muted"><a target="_blank" href="https://github.com/blat/pix">Pix v3.0</a> &mdash; Icon made by <a target="_blank" href="http://www.flaticon.com/free-icon/image-quadrate_3949" title="Adam Whitcroft">Adam Whitcroft</a>.</small>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.0/js/bootstrap.min.js" integrity="sha512-8Y8eGK92dzouwpROIppwr+0kPauu0qqtnzZZNEF8Pat5tuRNJxJXCkbQfJ0HlUG3y1HB3z18CSKmUo7i2zcPpg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js" integrity="sha512-7O5pXpc0oCRrxk8RUfDYFgn0nO1t+jLuIOQdOMRp4APB7uZ4vSjspzp5y6YDtDs4VzUSTbWzBFZ/LKJhnyFOKw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.6.1/d3.min.js" integrity="sha512-MefNfAGJ/pEy89xLOFs3V6pYPs6AmUhXJrRlydI/9wZuGrqxmrdQ80zKHUcyadAcpH67teDZcBeS6oMJLPtTqw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/d3-cloud/1.2.5/d3.layout.cloud.min.js" integrity="sha512-HjKxWye8lJGPu5q1u/ZYkHlJrJdm6KGr89E6tOrXeKm1mItb1xusPU8QPcKVhP8F9LjpZT7vsu1Fa+dQywP4eg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript" src="/app.js"></script>
        <?php if (env('PIWIK_TRACKER_HOST') && env('PIWIK_SITE_ID')): ?>
        <script type="text/javascript">
            var _paq = _paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function(){
                var u=(("https:" == document.location.protocol) ? "https" : "http") + "://<?= env('PIWIK_TRACKER_HOST') ?>/";
                _paq.push(["setTrackerUrl", u+"js/"]);
                _paq.push(["setSiteId", <?= env('PIWIK_SITE_ID') ?>]);

                _paq.push([function() {
                    var now = new Date(), nowTs = Math.round(now.getTime() / 1000), visitorInfo = this.getVisitorInfo();
                    var createTs = parseInt(visitorInfo[2]);
                    var cookieTimeout = 33696000; // 13 months
                    this.setVisitorCookieTimeout(createTs + cookieTimeout - nowTs);
                }]);

                var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
                g.defer=true; g.async=true; g.src=u+"js/"; s.parentNode.insertBefore(g,s);
            })();
        </script>
        <?php endif ?>
    </body>
</html>
