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

    public function getRelatedImages($count = 5) {
        $result = array();

        $where = array();
        if ($this->user_id) {
            $where[] = 'user_id = ' . $this->user_id;
        }

        if ($this->sharedTag) {
            $tagIds = array();
            foreach ($this->sharedTag as $tag) {
                $tagIds[] = $tag->id;
            }
            if ($tagIds) {
                $where[] = 'tag_id IN (' . implode(', ', $tagIds) . ')';
            }
        }

        if ($where) {
            $result = RedBean_Facade::convertToBeans('image', RedBean_Facade::getAll('SELECT * FROM (SELECT image.* FROM image LEFT JOIN image_tag ON image.id = image_id WHERE image.id <> ' . $this->id . ' AND private <> 1 AND (' . implode(' OR ', $where) . '))as similar GROUP BY id ORDER BY RAND() LIMIT ' . $count));
            $count -= count($result);
        }

        if ($count) {
            $result = array_merge($result, RedBean_Facade::find('image', 'image.id <> ' . $this->id . ' AND private <> 1 ORDER BY RAND() LIMIT ' . $count));
        }

        return $result;
    }

    public static function get($slug) {
        return RedBean_Facade::findOne('image', 'slug = ?', array($slug));
    }

}
