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


class Image {

    private $_name;
    private $_extension;
    private $_width;
    private $_height;
    private $_size;
    private $_tags;
    private $_description;
    private $_public;
    private $_user;
    public $error;

    public function upload($file, $tags, $description, $private) { 
        global $config, $sql;

        // check upload
        if ($file["error"]) {
            switch ($file["error"]){
                case 1: // UPLOAD_ERR_INI_SIZE
                    $this->error = "Le fichier d&eacute;passe la limite autoris&eacute;e par le serveur !";
                    return;
                    break;
                case 2: // UPLOAD_ERR_FORM_SIZE
                    $this->error = "Le fichier d&eacute;passe la limite autoris&eacute;e : " . $config['file_size_max'] . "ko !";
                    return;
                    break;
                case 3: // UPLOAD_ERR_PARTIAL
                    $this->error = "L'envoi du fichier a &eacute;t&eacute; interrompu pendant le transfert !";
                    return;
                    break;
                case 4: // UPLOAD_ERR_NO_FILE
                    $this->error = "Le fichier que vous avez envoy&eacute; a une taille nulle !";
                    return;
                    break;
            }
        }

        // check file size
        $this->_size = Image::_getSize($file["tmp_name"]);
        if (!$this->_checkSize()) {
            $this->error = "Le fichier est trop gros...";
            return false;
        }

        // get extension
        switch($file['type']) {
            case 'image/png':
                $this->_extension = '.png';
                break;
            case 'image/jpeg': case 'image/pjpeg':
                $this->_extension = '.jpg';
                break;
            case 'image/gif':
                $this->_extension = '.gif';
                break;
        }

        // check extension
        if (!$this->_checkExtension()) {
            $this->error = "Vous devez uploader un fichier de type ";
            for ($i = 0; $i < count($config['allowed_extensions']); $i++) {
                if ($i != 0 && $i != count($config['allowed_extensions']) - 1) $this->error .= ', ';
                if ($i == count($config['allowed_extensions']) - 1) $this->error .= ' ou ';
                $this->error .= $config['allowed_extensions'][$i];
            }
            return false;
        }

        // build new name based on timestamp
        $this->_name = time() . $this->_extension;

        // complete upload: move file to target dir
        if (!move_uploaded_file($file["tmp_name"], ORIGINAL . $this->_name)) {
            $this->error = "Echec de l'upload !";
            return false;
        }

        // build resized image
        $this->_resize(RESIZE, $config['width'], $config['height']);
        $this->_resize(THUMB, $config['width_thumb'], $config['height_thumb']);


        // format tags
        $this->_tags = $tags;
        $this->_formatTags();

        // description
        $this->_description = htmlspecialchars($description);

        // access
        $this->_public = !$private + 0;

        // author
        $this->_user = $_SESSION['pseudo'];

        // save into database
        $query = "INSERT INTO uploads (user, description, tags, public, name) VALUES ('" . $sql->escape($this->_user) . "', '" . $sql->escape($this->_description) . "', '" . $sql->escape($this->_tags) . "', " . $this->_public . ", '" . $this->_name . "')";
        return $sql->execute($query) && $this->saveTags();
    }

    public function edit($tags, $description, $private) {
        global $sql;

        $this->_tags = $tags;
        $this->_formatTags();

        $this->_description = htmlspecialchars($description);

        $this->_public = !$private + 0;

        // save into database
        $query = "UPDATE uploads SET description = '" . $sql->escape($this->_description) . "', tags = '" . $sql->escape($this->_tags) . "', public = ". $this->_public . " WHERE name = '" . $this->_name . "'";
        return $sql->execute($query);
    }

    public function getName() {
        return $this->_name;
    }

    public function getUser() {
        return $this->_user;
    }

    public function getTags() {
        return explode(',', $this->_tags);
    }

    public function getDescription() {
        return $this->_description;
    }

    public function isPrivate() {
        return !$this->_public;
    }

    public function saveTags() {
        global $sql;

        if (!$this->_public || !$this->_tags) return true;

        $pounds = array(10, 10, 8, 6, 4, 2);

        $tags = explode(',', $this->_tags);

        $sql->begin_transaction();
        $index = 0;
        foreach ($tags as $tag) {
            if ($index >= count($pounds)) {
                $index = count($pounds) - 1;
            }
            $hits = Image::_getTagHits($tag) + $pounds[$index];

            $query = "INSERT INTO tags VALUES ('', '" . $sql->escape($tag) . "', " . $hits . ") ON DUPLICATE KEY UPDATE hits = " . $hits;
            if (!$sql->execute($query)) {
                // on error
                $sql->rollback();
                return false;
            }

            $index++;
        }

        $sql->commit();
        return true;
    }

    private static function _getTagHits($tag) {
        global $sql;
        $query = "SELECT hits FROM tags WHERE tag = '" . $sql->escape($tag) . "'";
        $sql->execute($query);
        if ($row = $sql->next()) {
            return $row['hits'];
        }
        return 0;
    }

    private function _formatTags() {
        $this->_tags = preg_replace('/\s*,\s*/', ',', $this->_tags);
        $this->_tags = strtolower($this->_tags);
        $this->_tags = htmlspecialchars($this->_tags);
        $this->_tags = stripcslashes($this->_tags);
    }

    private function _resize($target, $width_max, $height_max) {

        // load image
        $img = $this->_load();

        // get current dimension
        if (!$this->_width) $this->_width = $this->getWidth();
        if (!$this->_height) $this->_height = $this->getHeight();

        // if current image is too small, save it
        if ($this->_width < $width_max && $this->_height <= $height_max) {
            $this->_saveTo($img, $target);
        } else {

            // calculate new dimensions
            $ratio = $this->_width / $this->_height;
            if ($ratio == $width_max / $height_max) {
                $width_resized = $width_max;
                $height_resized = $height_max;
            } elseif ($ratio > $width_max / $height_max) {
                $height_resized = $this->_height * $width_max / $this->_width;
                $width_resized = $width_max;
            } else {
                $width_resized = $this->_width * $height_max / $this->_height;
                $height_resized = $height_max;
            }

            // create resized image
            $img_resized = ImageCreateTrueColor($width_resized, $height_resized);

            // save alphas
            ImageAlphaBlending($img_resized, false);
            ImageSaveAlpha($img_resized, true);

            // copy and resize from source
            ImageCopyResampled($img_resized, $img, 0, 0, 0, 0, $width_resized, $height_resized, $this->_width, $this->_height);

            // save new image
            $this->_saveTo($img_resized, $target);

            // flush
            ImageDestroy($img_resized);
        }

        ImageDestroy($img);
    }

    private function _load() {
        $filename = ORIGINAL . $this->_name;
        switch ($this->_extension) {
            case ".jpg":
            case ".jpeg":
                return ImageCreateFromJpeg($filename);
            case ".gif":
                return ImageCreateFromGif($filename);
            case ".png":
                return ImageCreateFromPng($filename);
            case ".bmp":
                return ImageCreateFromBmp($filename);
        }
    }

    private function _saveTo($img, $dir) {
        $filename = $dir . $this->_name;
        switch ($this->_extension) {
            case ".jpg":
            case ".jpeg":
                ImageJpeg($img, $filename);
                break;
            case ".gif":
                ImageGif($img, $filename);
                break;
            case ".png":
                ImagePng($img, $filename);
                break;
            case ".bmp":
                ImageBmp($img, $filename);
                break;
        }
    }

    public function getSize() { 
        $filename = ORIGINAL . $this->_name;
        return Image::_getSize($filename); 
    }

    private static function _getSize($filename) { 
        return filesize($filename); 
    }

    public function getWidth() { 
        $filename = ORIGINAL . $this->_name;
        $size = GetImageSize($filename); 
        return $size[0]; 
    }

    public function getHeight() { 
        $filename = ORIGINAL . $this->_name;
        $size = GetImageSize($filename); 
        return $size[1]; 
    }

    private static function _getExtension($filename) {
        return strtolower(strrchr($filename, '.')); 
    }

    private function _checkSize() {
        global $config;
        return $this->_size < ($config['file_size_max']*1024);
    }

    private function _checkExtension() {
        global $config;
        return in_array($this->_extension, $config['allowed_extensions']);
    }


    public static function getTagCloud() {
        global $sql, $config;

        $query = "SELECT SUM(hits) AS total FROM tags WHERE tag <> ''";
        $sql->execute($query);
        $row = $sql->next();
        $total = $row['total'];

        $query = "SELECT tag, hits FROM tags WHERE tag <> '' ORDER BY RAND()"; 
        $sql->execute($query);
        $count = $sql->count();
        while($row = $sql->next()) {

            $rate = ( $row['hits'] / $count ) * 100;
            if($rate >= 40) {
                $index = 8;
            } elseif($rate >= 30 && $rate < 40) {
                $index = 7;
            } elseif($rate >= 13 && $rate < 30) {
                $index = 6;
            } elseif($rate >= 10 && $rate < 13) {
                $index = 5;
            } elseif($rate >= 8 && $rate < 10) {
                $index = 4;
            } elseif($rate >= 6 && $rate < 8) {
                $index = 3;
            } elseif($rate >= 3 && $rate < 6) {
                $index = 2;
            } elseif($rate >= 1 && $rate < 3) {
                $index = 1;
            } else {
                $index = 0;
            }

            $data = array(
                'color' => $config['tagcloud'][$index]['color'],
                'size' => $config['tagcloud'][$index]['size'],
                'hits' => $row['hits']
            );

            $tagcloud[$row['tag']] = $data;
        }
        return $tagcloud;
    }

    public static function getFromTag($tag) {
        global $sql;

        $tag = htmlspecialchars($tag);
        if (!$tag) return array();

        $query = "SELECT * FROM uploads WHERE (public = 1";
        if ($_SESSION['pseudo']) {
            $query .= " OR user = '" . $_SESSION['pseudo'] . "'";
        }
        $query .= ") AND tags LIKE '%" . $sql->escape($tag) . "%' ORDER BY name DESC";

        $images = Image::_getFromQuery($query);

        foreach ($images as $index => $image) {
            $valid = false;
            foreach ($image->getTags() as $t) {
                if ($tag == $t) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                unset($images[$index]);
            }
        }

        return $images;
    }

    public static function getFromAuthor($author) {
        global $sql;

        $author = htmlspecialchars($author);
        if (!$author) return array();

        $query = "SELECT * FROM uploads WHERE user = '" . $sql->escape($author) . "'";

        if ($author != $_SESSION['pseudo']) {
            $query .= " AND public=1";
        }

        $query .= " ORDER BY name DESC";

        return Image::_getFromQuery($query);
    }

    public static function getAll() {
        $query = "SELECT * FROM uploads WHERE public=1 ORDER BY name DESC";

        return Image::_getFromQuery($query);
    }

    public function getRelated() {
        $result = array(); //Image::getFromAuthor($this->_user);
        $taglist = $this->getTags();
        if (is_array($taglist)) {
            foreach ($taglist as $tag) {
                $result = array_merge($result, Image::getFromTag($tag));
            }
        }
        unset($result[$this->_name]);
        return $result;
    }

    private static function _getFromQuery($query) {
        global $sql;

        $sql->execute($query);

        $result = array();
        while ($row = $sql->next()) {
            $image = new Image();
            $image->_name = $row['name'];
            $image->_user = $row['user'];
            $image->_tags = $row['tags'];
            $image->_public = $row['public'];
            $image->_description = $row['description'];

            $result[$row['name']] = $image;
        }
        return $result;

    }

    public static function getFromName($name) {
        global $sql;

        $query = "SELECT * FROM uploads WHERE name = '" . $sql->escape($name) . "'";
        $result = Image::_getFromQuery($query);
        if (count($result) == 1) {
            $image = array_shift($result);
            return $image;
        }
        return null;

    }

    public static function getRandom($limit = 1) {
        global $sql;

        $query = "SELECT * FROM uploads WHERE public=1 ORDER BY RAND() LIMIT " . $limit;
        $result = Image::_getFromQuery($query); 
        return $result;
        /*if (count($result) == 1) {
            return $result[0];
        }
        return null;*/

    }

    public static function getCount() {
        return count(scandir(ORIGINAL));
        /*global $sql;

        $query = "SELECT COUNT(id) AS count FROM uploads";
        $sql->execute($query);
        $row = $sql->next();

        return $row['count'];*/
    }

    public static function getTotalSize() {
        $total = 0;
        foreach (scandir(ORIGINAL) as $file) {
            $total += Image::_getSize(ORIGINAL . $file);
        }
        return $total;
    }

    public static function getTagsCount() {
        global $sql;

        $query = "SELECT COUNT(id) AS count FROM tags";
        $sql->execute($query);
        $row = $sql->next();

        return $row['count'];
    }

    public static function getHumanSize($size) {
        if ($size < 1024) {
            return round($size, 2) . ' o';
        } else if ($size / 1024 < 1024) {
            return round($size / 1024, 2) . ' ko';
        } else if ($size / 1024 / 1024 < 1024) {
            return round($size / 1024 / 1024, 2) . ' Mo';
        } else {
            return round($size / 1024 / 1024 / 1024, 2) . ' Go';
        }
    }

    public function getDate() {
        $timestamp = strtolower(substr($this->_name, 0, strrpos($this->_name, '.')));
        return date('d/m/Y', $timestamp);
    }

    public static function resetTags() {
        global $sql;

        $query = "DELETE FROM tags";
        return $sql->execute($query);
    }

    public static function rebuildTagCloud() {
        if (Image::resetTags()) {
            $images = Image::getAll();
            foreach ($images as $image) {
                $image->saveTags();
            }
        }
    }

    public function delete() {
        global $sql;

        unlink(THUMB . $this->_name);
        unlink(RESIZE . $this->_name);
        unlink(ORIGINAL . $this->_name);

        $query = "DELETE FROM uploads WHERE name = '" . $this->_name . "'";
        return $sql->execute($query);
    }

}


/******************************************************************************/
/*                                                                            */
/*                       __        ____                                       */
/*                 ___  / /  ___  / __/__  __ _____________ ___               */
/*                / _ \/ _ \/ _ \_\ \/ _ \/ // / __/ __/ -_|_-<               */
/*               / .__/_//_/ .__/___/\___/\_,_/_/  \__/\__/___/               */
/*              /_/       /_/                                                 */
/*                                                                            */
/*                                                                            */
/******************************************************************************/
/*                                                                            */
/* Titre          : Fonctions imagecreatefrombmp et imagebmp                  */
/*                                                                            */
/* URL            : http://www.phpsources.org/scripts120-PHP.htm              */
/* Auteur         : kurt67                                                    */
/* Date édition   : 14 Avril 2005                                             */
/* Website auteur : http://www.phpsources.org                                 */
/*                                                                            */
/******************************************************************************/

function ImageCreateFromBmp($dir) {
    $bmp = "";
    if (file_exists($dir)) {
        $file = fopen($dir,"r");
        while(!feof($file)) $bmp .= fgets($file,filesize($dir));
        if (substr($bmp,0,2) == "BM") {
            // Lecture du header
            $header = unpack("vtype/Vlength/v2reserved/Vbegin/Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", $bmp);
            extract($header);
            // Lecture de l'image
            $im = imagecreatetruecolor($width,$height);
            $i = 0;
            $diff = floor(($imagesize - ($width*$height*($bits/8)))/$height);
            for($y=$height-1;$y>=0;$y--) {
                for($x=0;$x<$width;$x++) {
                    if ($bits == 32) {
                        $b = ord(substr($bmp,$begin+$i,1));
                        $v = ord(substr($bmp,$begin+$i+1,1));
                        $r = ord(substr($bmp,$begin+$i+2,1));
                        $i += 4;
                    } else if ($bits == 24) {
                        $b = ord(substr($bmp,$begin+$i,1));
                        $v = ord(substr($bmp,$begin+$i+1,1));
                        $r = ord(substr($bmp,$begin+$i+2,1));
                        $i += 3;
                    } else if ($bits == 16) {
                        $tot1 = decbin(ord(substr($bmp,$begin+$i,1)));
                        while(strlen($tot1)<8) $tot1 = "0".$tot1;
                        $tot2 = decbin(ord(substr($bmp,$begin+$i+1,1)));
                        while(strlen($tot2)<8) $tot2 = "0".$tot2;
                        $tot = $tot2.$tot1;
                        $r = bindec(substr($tot,1,5))*8;
                        $v = bindec(substr($tot,6,5))*8;
                        $b = bindec(substr($tot,11,5))*8;
                        $i += 2;
                    }
                    $col = imagecolorexact($im,$r,$v,$b);
                    if ($col == -1) $col = imagecolorallocate($im,$r,$v,$b);
                    imagesetpixel($im,$x,$y,$col);
                }
                $i += $diff;
            }
            // retourne l'image
            return $im;
            imagedestroy($im);
        } else return false;
    } else return false;
}

function ImageBmp($im,$dir="") {
    $pix = "";
    for($y=imagesy($im)-1;$y>=0;$y--) {
        for($x=0;$x<imagesx($im);$x++) {
            $rgb = ImageColorAt($im, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $pix .= pack("C3",$b,$g,$r);
        }
    }
    $header = pack("Vv2VVVVvvVVVVVV",strlen($pix)+54,0,0,54,40,imagesx($im),imagesy($im),1,24,0,strlen($pix),0,0,0,0);
    if ($dir != "") {
        $inF = fopen($dir,"w");
        fwrite($inF,"BM".$header.$pix);
        fclose($inF);
    } else echo "BM".$header.$pix; 
}

