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


$file = ROOT . 'cron.last';
$last = file_exists($file) ? file_get_contents($file) : '0';

$now = time();

if ($last + $config['cron'] < $now) {

    require_once CLASSES . 'Image.php';
    Image::rebuildTagCloud();

    file_put_contents($file, $now);
}



