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
require_once CLASSES . 'Image.php';

?>

<h2>Statistiques</h2>

<ul>
    <li><?php echo User::getCount(); ?> utilisateurs enregistr&eacute;s</li>
    <li><?php echo Image::getCount(); ?> images soit <?php echo Image::getHumanSize(Image::getTotalSize()); ?></li>
    <li><?php echo Image::getTagsCount(); ?> tags</li>
</ul>
