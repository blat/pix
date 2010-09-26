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

$cloud = Image::getTagCloud();

//echo '<h2>Nuage de tags</h2>';

if (is_array($cloud)) {
    foreach ($cloud as $tag => $data) {
        echo "<a href=\"?action=search&method=tag&tag=" . urlencode($tag) . "\">";
        echo "  <span style='color: " . $data['color']."; font-size: " . $data['size'] . "px;'>" . $tag . "</span>";
        echo "</a>";
    }
}
