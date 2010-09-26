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

$method = $_GET['method'];
switch ($method) {

    case 'tag':
        $searchFor = "Images tagg&eacute;es \"". $_GET['tag'] . "\"";
        $images = Image::getFromTag($_GET['tag']);
        break;

    case 'author':
        $searchFor = "Images envoy&eacute;es par " . $_GET['author'];
        $images = Image::getFromAuthor($_GET['author']);
        break;

    case 'random':
        $searchFor = "Image al&eacute;atoire...";
        $images = Image::getRandom();
        break;

    case 'browse':
        $searchFor = "Toutes les images";
        $images = Image::getAll();
        break;
}

echo '<h2>' . $searchFor . '</h2>';
if (count($images) == 0) {
    echo "Aucune image.";
} else if ($method == 'random') {
    $image = array_shift($images);
    ob_clean();
    header('Location: ' . $config['url'] . '?img=' . $image->getName());
} else {
    foreach ($images as $image) {
        echo '<p class="result">';
        echo '  <a href="?img=' . $image->getName() . '">';
        echo '      <img src="' . $config['dir_thumb'] .$image->getName() . '"/>';
        echo '  </a>';
        echo '</p>';
    } 
}?>

<div class="clear"></div>
