<?php

namespace App;

use Phencil\Model;

class Image extends Model
{
    /**
     * Define image sizes
     */
    const SIZE_SQUARE   = 140;
    const SIZE_SMALL    = 150;
    const SIZE_MEDIUM   = 360;
    const SIZE_LARGE    = 1000;
    const SIZE_ORIGINAL = 0;

    /**
     * Define storage path
     */
    const DATA_DIR = __DIR__ . '/../data/';

    /**
     * Image belong to one user
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Image has many tags
     */
    public function tags()
    {
        return $this->belongsToMany('App\Tag');
    }

    /**
     * Get image URL
     *
     * @return string
     */
    public function getUrl($size = null)
    {
        $url = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . "/image/{$this->slug}";
        if ($size) {
            $url .= "/{$size}.jpg";
        }
        return $url;
    }

    /**
     * Get tag's labels
     *
     * @return array
     */
    public function getLabels()
    {
        $labels = [];
        foreach ($this->tags as $tag) {
            $labels[] = $tag->label;
        }
        return $labels;
    }

    /**
     * Get random images related to this one (same owner or same tags)
     *
     * @return array
     */
    public function getRelatedImages($count = 6)
    {
        $result = [];
        $baseQuery = self::select('images.*')
            ->where('images.id', '<>', $this->id)
            ->where('private', '<>', 1)
            ->orderByRaw('RAND()');

        if ($this->tags || $this->user_id) {
            $query = clone($baseQuery);
            if ($this->user_id) {
                $query->where('user_id', '=', $this->user_id);
            }

            $tagIds = [];
            foreach ($this->tags as $tag) {
                $tagIds[] = $tag->id;
            }

            if ($tagIds) {
                $query->join('image_tag', 'images.id', '=', 'image_id')->where('tag_id', 'IN', $tagIds);
            }

            foreach ($query->limit($count)->get() as $image) {
                $result[$image->id] = $image;
            }
        }

        if (count($result) != $count) {
            if ($result) {
                $baseQuery->where('images.id', 'NOT IN', array_keys($result));
            }
            foreach ($baseQuery->limit($count - count($result))->get() as $image) {
                $result[] = $image;
            }
        }

        return $result;
    }

    /**
     * Set image owner
     *
     * @param User
     */
    public function setOwner($user)
    {
        $this->user()->associate($user);
    }

    /**
     * Set image tags
     *
     * @param string
     */
    public function setTags($labels)
    {
        $labels = trim($labels);
        if (!empty($labels)) {
            foreach (explode(',', $labels) as $label) {
                $label = trim($label);
                $tag = Tag::getByLabel($label);
                if (!$tag) {
                    $tag = new Tag();
                    $tag->label = $label;
                    $tag->save();
                }
                $this->tags()->attach($tag->id);
            }
        }
    }

    /**
     * Delete an image and its file
     */
    public function delete()
    {
        unlink($this->_getFile());
        return parent::delete();
    }

    /**
     * Resize an image
     *
     * @return string
     */
    public function resize($size)
    {
        $const = "self::SIZE_" . strtoupper($size);
        if (!defined($const)) return;

        $pixel = constant($const);

        return \Intervention\Image\ImageManagerStatic::cache(function($imageManager) use ($pixel) {
            ini_set('memory_limit', '512M');

            $image = $imageManager->make($this->_getFile())->orientate();

            if ($pixel) {
                if ($pixel == self::SIZE_SQUARE) {
                    $image = $image->fit($pixel, $pixel);
                } else {
                    $image = $image->heighten($pixel, function ($constraint) {
                        $constraint->upsize();
                    });
                    $image = $image->widen($pixel, function ($constraint) {
                        $constraint->upsize();
                    });
                }
            }
        }, 24*3600); // cache for 24 hours
    }

    /**
     * Create an image
     *
     * @param $file
     * @return Image
     */
    public static function upload($file)
    {
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
            throw new \Exception($error);
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
                throw new \Exception('Ce type de fichier n\'est pas autorisé !');
        }

        $image = new self();
        $image->slug = time();
        $image->type = $extension;

        if (!move_uploaded_file($file['tmp_name'], $image->_getFile())) {
            throw new \Exception('Échec de l\'envoi !');
        }

        $image->size = filesize($image->_getFile());
        $image->date = date('Y-m-d H:i:s');
        $image->save();

        return $image;
    }

    /**
     * Get path of file
     *
     * @return string
     */
    private function _getFile()
    {
        return self::DATA_DIR . $this->slug . '.' . $this->type;
    }

    /**
     * Get image by slug
     *
     * @return Image
     */
    public static function getBySlug($slug)
    {
        return self::where('slug', '=', $slug)->first();
    }

    /**
     * Get all popular images
     *
     * @return array
     */
    public static function getPopular()
    {
        return self::where('private', '<>', 1)
            ->orderByRaw('popularity/DATEDIFF(NOW(), date)*RAND()')
            ->limit(60)
            ->get();
    }

}
