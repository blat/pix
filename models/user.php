<?php

class Model_User extends RedBean_SimpleModel {

    public function getImages($allowPrivate = false) {
        $images = $this->bean;
        if ($allowPrivate) {
            $images->with('ORDER BY id DESC');
        } else {
            $images->withCondition('private <> 1 ORDER BY id DESC');
        }
        return $images->ownImage;
    }

    public function canUpdate($image) {
        return $image && (($image->user && $image->user->id == $this->id) || in_array($this->username, option('admin')));
    }

    public static function password($password) {
        return crypt($_POST["password"], $_POST["password"]);
    }

    public static function get($username) {
        return RedBean_Facade::findOne('user', 'username = ?', array($username));
    }

}
