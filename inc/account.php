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

$user = User::get();

if (!$user) {
    $error = "Vous devez &ecirc;tre connect&eacute; pour acc&egrave;der &agrave; cette page.";
    include_once INC . '_error.php';
} else {

    $pseudo = ($_POST['edit']) ? $_POST['pseudo'] : $user->getPseudo();

    if ($_POST) {

        if ($_POST['edit']) {
            $success = $user->edit($pseudo);
        } else if ($_POST['changePassword']) {
            $success = $user->changePassword($_POST['password'], $_POST['new'], $_POST['confirm']);
        } else if ($_POST['delete']) {
            $success = $user->delete($_POST['all']);
        }

        if (!$success) {
            $error = $user->error;
        }

    }

    ?>

        <h2>Mon compte</h2>

        <?php include_once INC . '_error.php';

        if ($_POST['delete'] && $success) {
            echo '<span class="success">Compte supprim&eacute; !</span>';
        } else {

            if ($success) {
                echo '<span class="success">Compte mis-&agrave;-jour !</span>';
            } ?>

            <form class="edit" action="?action=account" method="post">
                <label for="pseudo">Pseudo : </label>
                <input type="text" id="pseudo" name="pseudo" value="<?php echo $pseudo; ?>" />
                <input type="submit" name="edit" value="Changer le pseudo" />
            </form>

            <form class="changePassword" action="?action=account" method="post">
                <label for="password">Mot de passe actuel : </label>
                <input type="password" id="password" name="password" />
                <label for="new">Nouveau mot de passe : </label>
                <input type="password" id="new" name="new" />
                <label for="confirm">Confirmer votre mot de passe : </label>
                <input type="password" id="confirm" name="confirm" />
                <input type="submit" name="changePassword" value="Changer le mot de passe" />
            </form>

            <form action="?action=account" method="post">
                <input type="hidden" name="all" value="0" />
                <input type="submit" name="delete" value="Supprimer mon compte" />
            </form>

            <form action="?action=account" method="post">
                <input type="hidden" name="all" value="1" />
                <input type="submit" name="delete" value="Supprimer mon compte, ainsi que toutes les images associ&eacute;es" />
            </form>


    <?php }
}

