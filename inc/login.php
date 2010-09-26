<?php

/******************************************************************************/
/*                                                                            */
/* Pix : Hébergement d'images                                                 */
/*                                                                            */
/******************************************************************************/
/*                                                                            */
/* Auteur:                                                                    */
/*     - Mickael BLATIERE (mickael@saezlive.net)                              */
/*                                                                            */
/* Contributeurs :                                                            */
/*     - ZeR0^ (zero@toile-libre.org)                                         */
/*     - NiZoX (nizox@alterinet.org)                                          */
/*                                                                            */
/* Licence : GPL                                                              */
/*                                                                            */
/******************************************************************************/


require_once CLASSES . 'User.php';

if ($_POST) {

    $user = new User();
    $success = $user->login($_POST['pseudo'], $_POST['password']);
    if (!$success) {
        $error = $user->error;
    }

}

if ($success) {
    header('Location: ' . $config['url']);
} else {

?>

    <h2>Ouvrir une session</h2>
    <p>Veuillez entrer ci-dessous votre pseudo et votre mot de passe :-)</p>
    <p>Si vous ne disposez pas encore d'un compte, vous pouvez en enregistrer un gratuitement sur <a href="?action=register">cette page</a>.</p>

    <?php include_once INC . '_error.php'; ?>

    <form action="?action=login" method="post">
        <label for="pseudo">Pseudo : </label>
        <input type="text" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo']; ?>" />
        <label for="password">Mot de passe : </label>
        <input type="password" id="password" name="password" />
        <input type="submit" value="S'identifier" />
    </form>

<?php 
}

