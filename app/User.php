<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;

    /**
     * User has many images
     */
    public function images()
    {
        return $this->hasMany('App\Image');
    }

    /**
     * Get all images
     * Newest first
     *
     * @return array
     */
    public function getAllImages()
    {
        return $this->images()
            ->orderBy('id', 'desc')
            ->get();
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
     * Check if user is administrator
     *
     * @return bool
     */
    public function isAdmin()
    {
        $admins = array_map(function($admin) {
            return trim($admin);
        }, explode(',', env('PIX_ADMINS')));

        return in_array($this->username, $admins);
    }

    /**
     * Check if user owns this image
     *
     * @param Image
     * @return bool
     */
    public function isOwner($image)
    {
        return $image->user && $image->user->id == $this->id;
    }

    /**
     * Get user by username
     *
     * @return User
     */
    public static function getByUsername($username)
    {
        return self::where('username', '=', $username)->first();
    }

    /**
     * Get user by username and password
     *
     * @return User
     */
    public static function getByUsernameAndPassword($username, $password)
    {
        $user = self::getByUsername($username);
        if ($user && !hash_equals($user->password, self::hashPassword($password))) {
            $user = null;
        }
        return $user;
    }

    /**
     * Hash password
     *
     * @param string
     * @return string
     */
    public static function hashPassword($password)
    {
        return crypt($password, $password);
    }

}
