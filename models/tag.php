<?php

class Model_Tag extends RedBean_SimpleModel {

    public function getImages() {
        return $this->bean->withCondition('private <> 1 ORDER BY id DESC')->sharedImage;
    }

    public function getPopularity() {
        return RedBean_Facade::getCell('SELECT COUNT(image_id) FROM image JOIN image_tag ON image.id = image_id WHERE private <> 1 AND tag_id = ' . $this->id);
    }

    public static function get($label) {
        return RedBean_Facade::findOne('tag', 'label = ?', array($label));
    }

}
