<?php

class Model_Tag extends RedBean_SimpleModel {

    public function getImages() {
        return $this->bean->withCondition('private <> 1 ORDER BY id DESC')->sharedImage;
    }

    public static function get($label) {
        return RedBean_Facade::findOne('tag', 'label = ?', array($label));
    }

}
