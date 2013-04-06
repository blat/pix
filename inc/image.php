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
?>

    <div class="image">
        <span class="thumbnail">
            <a href="<?= $original ?>" rel="milkbox"><img src="<?= $resized ?>"/></a>
        </span>
        <?php if ($image && ( in_array($_SESSION['pseudo'], $config['admins']) || ($image->getUser() && $image->getUser() == $_SESSION['pseudo']))) { ?>
            <span class="actions">
                <a class="edit" href="?action=edit&img=<?= $img ?>"><img src="images/edit.png" /></a>
                <a class="delete" href="?action=delete&img=<?= $img ?>"><img src="images/delete.png" /></a>
            </span>
        <? } ?>
        <? if ($image) { ?>
            <span class="author">Image envoy&eacute;e le <? echo $image->getDate(); ?>
            <? if ($image->getUser()) { ?>
                par <a href="?action=search&method=author&author=<?echo $image->getUser(); ?>"><?echo $image->getUser(); ?></a>
            <? } ?>
            </span>
            <span class="metadata"><? echo $image->getWidth() . ' x ' . $image->getHeight(); ?> - <? echo Image::getHumanSize($image->getSize()); ?></span>
            <span class="description"><?echo $image->getDescription(); ?></span>
            <span class="tags">
            <? foreach ($image->getTags() as $tag) { ?>
                <a href="?action=search&method=tag&tag=<?echo $tag; ?>"><?echo $tag; ?></a>
            <? } ?>
            </span>
        <? } ?>
    </div>

    <table class="info">
        <tr>
            <th></th>
            <th>Copiez/collez les textes suivants pour...</th>
        </tr>
        <tr>
            <td>Afficher l'image : </td>
            <td>
                <textarea><? echo $config['url']; ?>?img=<? echo $img; ?></textarea>
            </td>
        </tr>
        <tr>
            <td>Acc&eacute;der &agrave; l'image : </td>
            <td>
                <textarea><? echo $config['url'] . $original; ?></textarea>
            </td>
        </tr>
        <tr>
            <td>Ins&eacute;rer la miniature dans un forum : </td>
            <td>
                <textarea>[url=<? echo $config['url']; ?>?img=<? echo $img; ?>][img]<? echo $config['url'] . $thumb; ?>[/img][/url]</textarea>
            </td>
        </tr>
        <tr>
            <td>Ins&eacute;rer l'image dans un forum : </td>
            <td>
                <textarea>[url=<? echo $config['url']; ?>?img=<? echo $img; ?>][img]<? echo $config['url'] . $resized; ?>[/img][/url]</textarea>
            </td>
        </tr>
        <tr>
            <td>Ins&eacute;rer la miniature &agrave; votre site : </td>
            <td>
                <textarea><a href='<? echo $config['url']; ?>?img=<? echo $img; ?>'><img src='<? echo $config['url'] . $thumb; ?>' /></a></textarea>
            </td>
        </tr>
        <tr>
            <td>Ins&eacute;rer l'image &agrave; votre site : </td>
            <td>
                <textarea><a href='<? echo $config['url']; ?>?img=<? echo $img; ?>'><img src='<? echo $config['url'] . $resized; ?>' /></a></textarea>
            </td>
        </tr>
    </table>

    <div class="related">
        <?php if ($image) $images = $image->getRelated();
        if (!is_array($images) || count($images) == 0) {
            $images = Image::getRandom(3);
            echo "<h4>Images al&eacute;atoires</h4>";
        } else {
            echo "<h4>Images li&eacute;es</h4>";
        }
        foreach ($images as $image) {
            echo '<p>';
            echo '  <a href="?img=' . $image->getName() . '">';
            echo '      <img src="' . $config['dir_thumb'] .$image->getName() . '"/>';
            echo '  </a>';
            echo '<p>';
        } ?>
    </div>
<?php
} else {
    $error = "Cette image n'existe pas !";
    include_once INC . '_error.php';
}

