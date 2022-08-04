<?php $this->layout('layout') ?>

<div id="tagcloud">
    <?php foreach ($tags as $tag): ?>
        <a data-weight="<?= $tag->getPopularity() ?>" href="/tag/<?= $this->e($tag->label) ?>">
            <?= $this->e($tag->label) ?>
        </a>
    <?php endforeach ?>
</div>
