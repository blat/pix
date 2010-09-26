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
    $success = $user->register($_POST['pseudo'], $_POST['password'], $_POST['confirm']);
    if (!$success) {
        $error = $user->error;
    }

}

if ($success) {
    header('Location:' . $config['url']);
} else {

?>

    <h2>Enregistrer un compte</h2>
    <p>Veuillez entrer ci-dessous le pseudo et le mot de passe d&eacute;sir&eacute;.</p>
    <p><em>Votre mot de passe est crypt&eacute; via une methode a sens unique (hash).</em></p>

    <?php include_once INC . '_error.php'; ?>

    <form action="?action=register" method="post">
        <label for="pseudo">Pseudo : </label>
        <input type="text" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo']; ?>" />
        <label for="password">Mot de passe : </label>
        <input type="password" id="password" name="password" />
        <label for="confirm">Confirmer votre mot de passe : </label>
        <input type="password" id="confirm" name="confirm" />
        <input type="submit" value="S'enregistrer" />
    </form>

<?php 
}
