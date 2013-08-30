<?php

class Model_Image extends RedBean_SimpleModel {

    public function getFile() {
        return option('data_dir') . $this->slug . '.' . $this->type;
    }

    public function getFileSize() {
        return filesize($this->getFile());
    }

    public function delete() {
        unlink($this->getFile());
    }

    public function setTags($labels) {
        $this->bean->sharedTag = array();
        $labels = trim($labels);
        if (!empty($labels)) {
            foreach (explode(',', $labels) as $label) {
                $label = trim($label);
                $tag = Model_Tag::get($label);
                if (!$tag) {
                    $tag = RedBean_Facade::dispense('tag');
                    $tag->label = $label;
                    RedBean_Facade::store($tag);
                }
                $this->bean->sharedTag[] = $tag;
            }
        }
    }

    public function getLabels() {
        $labels = array();
        foreach ($this->sharedTag as $tag) {
            $labels[] = $tag->label;
        }
        return $labels;
    }

    public static function get($slug) {
        return RedBean_Facade::findOne('image', 'slug = ?', array($slug));
    }

}
