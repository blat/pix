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

$image = new Image();
if ($image->upload($_FILES['img'], $_POST['tags'], $_POST['description'], $_POST['private'])) {
    ob_clean();
    header('Location: ' . $config['url'] . '?img=' . $image->getName());
} else {
    $error = $image->error;
    include_once INC . '_error.php';
}

