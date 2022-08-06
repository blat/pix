<div class="row">
    <?php foreach ($images as $image): ?>
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 p-3">
        <a class="col-12 p-0" href="<?= $this->urlFor('image', ['slug' => $image->slug]) ?>">
            <img class="img-fluid col-12 p-0 border border-light" src="<?= $this->urlFor('fullImage', ['slug' => $image->slug, 'size' => 'square']) ?>" />
        </a>
    </div>
    <?php endforeach ?>
</div>
