<div id="tagcloud">
    <?php foreach ($tags as $tag): ?>
        <a data-weight="<?= $tag->getPopularity() ?>" href="/tag/<?= htmlspecialchars($tag->label) ?>">
            <?= htmlspecialchars($tag->label) ?>
        </a>
    <?php endforeach ?>
</div>
