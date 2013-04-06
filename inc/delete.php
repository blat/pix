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


require_once CLASSES . 'Image.php';

if (isset($img) && file_exists(ORIGINAL . $img)) {
    $original = $config['dir_original'] . $img;
    $resized = $config['dir_resize'] . $img;
    $thumb = $config['dir_thumb'] . $img;

    $image = Image::getFromName($img);

    $user = User::get();
    if (!$user || ($image->getUser() != $user->getPseudo() && !in_array($user->getPseudo(), $config['admins']))) {
        $error = 'Acc&egrave;s interdit !';
        include_once INC . '_error.php';

    } else {

        if ($_POST) {

            if ($_POST['cancel']) {
                header('Location: ' . $config['url'] . '?img=' . $img);

            } else {
                $success = $image->delete();
                if (!$success) {
                    $error = $image->error;
                }
            }
        }

        ?>

        <h2>Supprimer une image</h2>

        <?php include_once INC . '_error.php';
        if ($success) {
            echo '<span class="success">Image supprim&eacute;e !</span>';
        } else { ?>

            <span class="preview">
                <a href="?img=<?= $img ?>" rel="milkbox"><img src="<?= $thumb ?>"/></a>
            </span>

            <p>&Ecirc;tes vous sur de vouloir supprimer cette image ?</p>

            <form action="?action=delete&img=<?= $img; ?>" method="post">
                <input type="submit" name="delete" value="Oui" />
                <input type="submit" name="cancel" value="Non" />
            </form>

    <?php   }
    }
}

