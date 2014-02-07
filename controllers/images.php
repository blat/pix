<?php

use PHPImageWorkshop\ImageWorkshop;


//---------------------------------------------------------------------------
// Image view

function image_view($slug) {
    $image = Model_Image::get($slug);
    if (!$image) halt(NOT_FOUND);

    $image->popularity++;
    RedBean_Facade::store($image);

    set('image', $image);
    return render('image.phtml');
}

function image_download($slug, $size) {
    ini_set('memory_limit', '512M');

    $image = Model_Image::get($slug);
    $config = option('size');

    if (!$image || !isset($config[$size])) halt(NOT_FOUND);
    $pixel = $config[$size];

    $layer = ImageWorkshop::initFromPath($image->getFile());

    if ($size == 'square') {
        $layer->cropMaximumInPixel(0, 0, 'MM');
        $layer->resizeInPixel($pixel, $pixel);
        $pixel = null;
    }

    if ($pixel) $layer->resizeByLargestSideInPixel($pixel, true);

    $layer->save(option('cache_dir') . '/' . $size, $slug . '.jpg');

    header('Content-type: image/jpeg');
    return imagejpeg($layer->getResult(), null, 95);
}


//---------------------------------------------------------------------------
// Upload

function image_upload() {
    return render('form_image.phtml');
}

function image_upload_post() {
    $file = $_FILES['image'];
    try {

        if (!is_uploaded_file($file['tmp_name'])) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error = 'Le fichier excède la taille autorisée !';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error = 'Le fichier n\'a été que partiellement envoyé !';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error = 'Aucun fichier n\'a été envoyé !';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = 'Le dossier de réception est manquant !';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error = 'Échec de l\'écriture du fichier sur le disque !';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error = 'Une extension PHP a arrêté l\'envoi du fichier !';
                    break;
                default:
                    $error = 'Une erreur est survenue. Merci de r&eacute;&eacute;ssayer plus tard !';
            }
            throw new Exception($error);
        }

        switch ($file['type']) {
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/jpeg':
            case 'image/pjpeg':
                $extension = 'jpg';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
            default:
                throw new Exception('Ce type de fichier n\'est pas autorisé !');
        }

        $image = RedBean_Facade::dispense('image');
        $image->slug = time();
        $image->type = $extension;

        if (!move_uploaded_file($file['tmp_name'],  $image->getFile())) {
            throw new Exception('Échec de l\'envoi !');
        }

        $image->private = !empty($_POST['private']);
        $image->size = $image->getFileSize();
        $image->date = date('Y-m-d H:i:s');

        $image->setTags($_POST['tags']);

        if (!empty($_SESSION['user'])) {
            $image->user = $_SESSION['user'];
        }

        RedBean_Facade::store($image);

        redirect('/image/' . $image->slug);
    } catch (Exception $e) {
        flash('error', $e->getMessage());
        redirect('/upload');
    }
}


//---------------------------------------------------------------------------
// Edit

function image_edit($slug) {
    $image = Model_Image::get($slug);
    if (empty($_SESSION['user']) || !$_SESSION['user']->canUpdate($image)) halt(NOT_FOUND);

    set('image', $image);
    return render('form_image.phtml');
}

function image_edit_post($slug) {
    $image = Model_Image::get($slug);
    if (empty($_SESSION['user']) || !$_SESSION['user']->canUpdate($image)) halt(NOT_FOUND);

    $image->setTags($_POST['tags']);
    $image->private = !empty($_POST['private']);
    RedBean_Facade::store($image);

    flash('success', 'Les modifications ont été enregistrées !');
    redirect('/image/' . $image->slug);
}


//---------------------------------------------------------------------------
// Delete

function image_delete($slug) {
    $image = Model_Image::get($slug);
    if (empty($_SESSION['user']) || !$_SESSION['user']->canUpdate($image)) halt(NOT_FOUND);

    RedBean_Facade::trash($image);

    flash('success', 'L\'image a été supprimée !');
    redirect('/');
}


//---------------------------------------------------------------------------
// Search

function image_search_by_popularity() {
    $images = RedBean_Facade::find('image', 'private <> 1 ORDER BY popularity/DATEDIFF(NOW(), date)*RAND() DESC LIMIT 50');

    $stats = RedBean_Facade::getRow('SELECT COUNT(*) AS image_count, SUM(size) AS image_size FROM image');
    $stats['user_count'] = RedBean_Facade::getCell('SELECT COUNT(*) FROM user');

    set('images', $images);
    set('stats', $stats);
    return render('images.phtml');
}

function image_search_by_tag($name) {
    $tag = Model_Tag::get($name);
    if (!$tag) halt(NOT_FOUND);

    $images = $tag->getImages();
    if (!$images) halt(NOT_FOUND);

    set('images', $images);
    set('title', 'Images taggées « ' . $name . ' »');
    return render('images.phtml');
}

function image_search_by_user($username) {
    $user = Model_User::get($username);
    if (!$user) halt(NOT_FOUND);

    $images = $user->getImages(!empty($_SESSION['user']) && $_SESSION['user']->id == $user->id);
    if (!$images) halt(NOT_FOUND);

    set('images', $images);
    set('title', 'Images envoyées par ' . $username);
    return render('images.phtml');
}
