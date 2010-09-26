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


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
    <head>
        <title><?php echo $config['title']; ?> | H&eacute;bergement d'images</title>
        <link rel="shortcut icon" href="images/<?php echo $config['favicon']; ?>.ico" type="image/x-icon" />
        <link rel="stylesheet" type="text/css" href="stylesheets/<?php echo $config['style']; ?>.css" media="all"  />
        <link rel="stylesheet" href="stylesheets/milkbox.css" type="text/css" media="screen" />
        <script src="javascripts/mootools-1.2.1-core-yc.js" type="text/javascript"></script>
        <script src="javascripts/mootools-1.2-more.js" type="text/javascript"></script>
        <script src="javascripts/milkbox.js" type="text/javascript"></script>
        <script type="text/javascript" language="javascript">
            // <![CDATA[
            function slideOut() {
                new Fx.Slide('upload-options').slideOut();
                $('upload-options-link').innerHTML = "<a href='#' onclick='slideIn();return false;'>Plus d'options &gt;</a>";
            }
            function slideIn() {
                new Fx.Slide('upload-options').slideIn();
                $('upload-options-link').innerHTML = "<a href='#' onclick='slideOut();return false;'>&lt; Masquer les options</a>";
            }
            // ]]>
        </script>
    </head>
    <body>

        <div id="prelude">

            <div class="actions">
                <?php if($_SESSION['pseudo']) { 
                    echo("Vous &ecirc;tes identifi&eacute; en tant que ".$_SESSION['pseudo'] . " - <a href=\"?action=account\">Mon compte</a>");
                    echo(" - <a href=\"?action=search&method=author&author=".$_SESSION['pseudo']."\">Mes images</a> - <a href=\"?action=logout\">Fermer la session</a>");
                } else { 
                    echo("<a href=\"?action=register\">Enregistrer un compte (facultatif)</a>");
                    echo(" - <a href=\"?action=login\">Ouvrir une session</a>");
                } ?>
            </div>

        </div>

        <div id="header">

            <a class="logo"href="<?echo $config['url'];?>">
                <h1><?php echo $config['title']; ?></h1>
                <h2>H&eacute;bergement d'images</h2>
            </a>

            <form class="upload" enctype="multipart/form-data" action="?action=upload" method="post">
                <h3>Envoyez votre image !</h3>
                <a class="help" href="?action=help">Aide ?</a>
                <input name="MAX_FILE_SIZE" value="<?= $config['file_size_max'] * 1024 ?>" type="hidden" /> 
                <input name="img" size="30" type="file" />
                <input value="Envoyer" type="submit" />
                <div class="options" id="upload-options">
                    <label for"tags">Tags (facultatif), &agrave; s&eacute;parer par des virgules :</label>
                    <input id="tags" name="tags" type="text"/>
                    <label for="description">Description (facultative) :</label>
                    <textarea name="description"></textarea>
                    <label for"private">Image priv&eacute;e ? par defaut, <b>votre image est publique !</b></label>
                    <input id="private" type="checkbox" name="private" value="1" />
                    Je ne veux pas que mon image apparaisse dans le moteur de recherche
                </div>
                <div id="upload-options-link" class="more">
                    <a href="#" onclick="slideIn();return false;">Plus d'options &gt;</a>
                </div>
            </form>
            <script type="text/javascript">new Fx.Slide('upload-options').hide();</script>
 
        </div>

        <div class="content" id="<?php echo $action; ?>">

