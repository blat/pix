<?= $this->fetch('shared/images.php', ['images' => $images]) ?>

<?php if (isset($stats)): ?>
<div class="meta text-muted text-right">
    <?= $stats['image_count'] ?> images &mdash; <?= $stats['user_count'] ?> utilisateurs &mdash; <?= round($stats['image_size']/1024/1024/1024, 2) ?> Go
</div>
<?php endif ?>
