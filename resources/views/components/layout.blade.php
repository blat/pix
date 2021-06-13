<html>
    <head>
        <title>Pix | {{ $title ?? "Hébergement d'images" }}</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Grand+Hotel" />
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="/style.css" />
        <link href="/favicon.png" rel="icon" />
        @if (!empty($image))
        <meta name="twitter:card" content="photo" />
        <meta name="twitter:image:src" content="{{ $image }}" />
        @endif
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="/"><img src="/logo.png" /> Pix</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="btn btn-primary" href="/upload"><i class="fa fa-cloud-upload"></i> Upload</a></li>
                        <li class="nav-item"><a class="nav-link" href="/explore">Explorer</a></li>
                        @if (!empty($_SESSION['user']))
                        <li class="nav-item"><a class="nav-link" href="/user/{{ $_SESSION['user']->username }}">Mes images</a></li>
                        <li class="nav-item"><a class="nav-link" href="/logout">Déconnexion</a></li>
                        @else
                        <li class="nav-item"><a class="nav-link" href="/login">Connexion</a></li>
                        <li class="nav-item"><a class="nav-link" href="/register">Inscription</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="content p-4">
                @if (!empty($_SESSION['flash']))
                    <div class="alert alert-{{ $_SESSION['flash']['level'] }} alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        {{ $_SESSION['flash']['message'] }}
                    </div>
                    @php
                    unset($_SESSION['flash']);
                    @endphp
                @endif

                @if (!empty($title))
                    <h2 class="title">{{ $title }}</h2>
                @endif

                {{ $slot }}
            </div>

            <div class="footer text-right pt-2 pb-2">
                <small class="text-muted"><a target="_blank" href="https://github.com/blat/pix">Pix v3.0</a> &mdash; Icon made by <a target="_blank" href="http://www.flaticon.com/free-icon/image-quadrate_3949" title="Adam Whitcroft">Adam Whitcroft</a>.</small>
            </div>
        </div>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.12.0/d3.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/d3-cloud/1.2.4/d3.layout.cloud.min.js"></script>
        <script type="text/javascript" src="/app.js"></script>
        @if (env('PIWIK_TRACKER_HOST') && env('PIWIK_SITE_ID'))
        <script type="text/javascript">
            var _paq = _paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function(){
                var u=(("https:" == document.location.protocol) ? "https" : "http") + "://{{ env('PIWIK_TRACKER_HOST') }}/";
                _paq.push(["setTrackerUrl", u+"js/"]);
                _paq.push(["setSiteId", {{ env('PIWIK_SITE_ID') }}]);

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
        @endif
    </body>
</html>
