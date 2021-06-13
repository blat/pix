<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $timestamps = false;

    /**
     * Tag has many images
     */
    public function images()
    {
        return $this->belongsToMany('App\Image');
    }

    /**
     * Filter only public images
     * Newest first
     *
     * @return array
     */
    public function getPublicImages()
    {
        return $this->images()
            ->where('private', '<>', 1)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Calcul tag popularity
     *
     * @return int
     */
    public function getPopularity()
    {
        return Image::join('image_tag', 'images.id', '=', 'image_id')
            ->where('private', '<>', 1)
            ->where('tag_id', '=', $this->id)
            ->count();
    }

    /**
     * Get tag by label
     *
     * @return Tag
     */
    public static function getByLabel($label)
    {
        return self::where('label', '=', $label)->first();
    }

    /**
     * Get all popular tags
     *
     * @return array
     */
    public static function getPopular()
    {
        return self::select('tags.*')
            ->join('image_tag', 'tags.id', '=', 'tag_id')
            ->join('images', 'images.id', '=', 'image_id')
            ->where('private', '<>', 1)
            ->groupBy('tag_id')
            ->orderByRaw('COUNT(image_id)*SUM(popularity) DESC')
            ->limit(100)
            ->get();
    }

}
