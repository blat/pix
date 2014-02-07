<?php

//---------------------------------------------------------------------------
// Search

function tag_search_by_popularity() {
    $images = RedBean_Facade::find('image', 'private <> 1 ORDER BY popularity/DATEDIFF(NOW(), date)*RAND() DESC LIMIT 5');

    $tags = RedBean_Facade::convertToBeans('tag', RedBean_Facade::getAll('SELECT tag.* FROM tag JOIN image_tag ON tag.id = tag_id JOIN image ON image.id = image_id WHERE private <> 1 GROUP BY tag_id ORDER BY COUNT(image_id)*SUM(popularity) DESC LIMIT 100'));

    set('images', $images);
    set('tags', $tags);
    return render('tags.phtml');
}

