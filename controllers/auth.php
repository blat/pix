<?php

//---------------------------------------------------------------------------
// Sign-in

function auth_login() {
    return render("form_auth.phtml");
}

function auth_login_post() {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        flash("error", "Veuillez saisir un pseudo et un mot de passe.");
        redirect("/login");
    }

    $user = RedBean_Facade::findOne("user", "username = ? AND password = ?", array($_POST["username"], Model_User::password($_POST["password"])));
    if (!$user) {
        flash("error", "Ces identifiants sont invalides.");
        redirect("/login");
    }

    $_SESSION["user"] = $user;

    flash("success", "Vous êtes maintenant connecté en tant que {$user->username} !");
    redirect("/");
}


//---------------------------------------------------------------------------
// Sign-up

function auth_register() {
    return render("form_auth.phtml");
}

function auth_register_post() {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        flash("error", "Veuillez saisir un pseudo et un mot de passe.");
        redirect("/register");
    }

    $user = Model_User::get($_POST["username"]);
    if ($user) {
        flash("error", "Ce pseudo est déjà prit. Veuillez en saisir un autre.");
        redirect("/register");
    }

    $user = RedBean_Facade::dispense("user");
    $user->username = $_POST["username"];
    $user->password = Model_User::password($_POST["password"]);
    RedBean_Facade::store($user);

    $_SESSION["user"] = $user;

    flash("success", "Vous êtes maintenant inscrit !");
    redirect("/");
}


//---------------------------------------------------------------------------
// Sign-out

function auth_logout() {
    unset($_SESSION["user"]);

    flash("success", "Vous êtes maintenant déconnecté !");
    redirect("/");
}
