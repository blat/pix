<?php $this->layout('layout', ['image' => $image]) ?>

<div class="image pb-3">
    <a target="_blank" href="<?= $this->urlFor('fullImage', ['slug' => $image->slug, 'size' => 'original']) ?>">
        <img class="img-fluid mx-auto d-block border border-light" src="<?= $this->urlFor('fullImage', ['slug' => $image->slug, 'size' => 'large']) ?>" />
    </a>
    <?php if (!empty($user) && ($user->isOwner($image) || $user->isAdmin())): ?>
    <div class="actions p-3">
        <a class="btn btn-secondary" href="<?= $this->urlFor('edit', ['slug' => $image->slug]) ?>">Modifier</a>
        <a class="btn btn-secondary delete" href="<?= $this->urlFor('delete', ['slug' => $image->slug]) ?>">Supprimer</a>
    </div>
    <?php endif ?>
    <div class="text-end text-muted pt-3">
        <?php if ($image->private): ?><i class="fa fa-eye-slash"></i><?php endif ?> <?= $image->popularity ?> vues
        <?php if ($image->user): ?>
        &mdash;
        <i class="fa fa-user"></i> <a href="<?= $this->urlFor('user', ['username' => $image->user->username]) ?>"><?= $this->e($image->user->username) ?></a>
        <?php endif ?>
        <?php if ($labels = $image->getLabels()): ?>
        &mdash;
        <i class="fa fa-tags"></i>
            <?php foreach ($labels as $label): ?>
            <a href="<?= $this->urlFor('tag', ['label' => $label]) ?>">#<?= $this->e($label) ?></a>
            <?php endforeach ?>
        <?php endif ?>
        &mdash;
        <i class="fa fa-calendar"></i>
        <?= date('d/m/Y', strtotime($image->date)) ?>
    </div>
</div>

<div class="border border-light border-left-0 border-right-0 py-3">
    <div class="row">
        <label class="col-12 font-weight-bold text-muted form-label">Afficher l'image :</label>
        <div class="col-7 col-sm-8 col-md-9 col-lg-10 pe-0">
            <input class="form-control rounded-0" id="image" type="text" value="<?= $this->fullUrlFor('image', ['slug' => $image->slug]) ?>" readonly="readonly" />
        </div>
        <button class="btn btn-primary btn-clipboard col-5 col-sm-4 col-md-3 col-lg-2 rounded-0" data-clipboard-target="#image"><i class="fa fa-clipboard"></i> Copier</button>
    </div>
    <div class="row pt-3">
        <label class="col-12 font-weight-bold text-muted form-label">Accéder à l'image :</label>
        <div class="col-7 col-sm-8 col-md-9 col-lg-10 pe-0">
            <input class="form-control rounded-0" id="original" type="text" value="<?= $this->fullUrlFor('fullImage', ['slug' => $image->slug, 'size' => 'original']) ?>" readonly="readonly" />
        </div>
        <button class="btn btn-primary btn-clipboard col-5 col-sm-4 col-md-3 col-lg-2 rounded-0" data-clipboard-target="#original"><i class="fa fa-clipboard"></i> Copier</button>
    </div>
    <div class="row pt-3">
        <label class="col-12 font-weight-bold text-muted form-label">Insérer la miniature dans un forum :</label>
        <div class="col-7 col-sm-8 col-md-9 col-lg-10 pe-0">
            <input class="form-control rounded-0" id="forum_thumbnail" type="text" value="[url=<?= $this->fullUrlFor('image', ['slug' => $image->slug]) ?>][img]<?= $this->fullUrlFor('fullImage', ['slug' => $image->slug, 'size' => 'small']) ?>[/img][/url]" readonly="readonly" />
        </div>
        <button class="btn btn-primary btn-clipboard col-5 col-sm-4 col-md-3 col-lg-2 rounded-0" data-clipboard-target="#forum_thumbnail"><i class="fa fa-clipboard"></i> Copier</button>
    </div>
    <div class="row pt-3">
        <label class="col-12 font-weight-bold text-muted form-label">Insérer l'image dans un forum :</label>
        <div class="col-7 col-sm-8 col-md-9 col-lg-10 pe-0">
            <input class="form-control rounded-0" id="forum_image" type="text" value="[url=<?= $this->fullUrlFor('image', ['slug' => $image->slug]) ?>][img]<?= $this->fullUrlFor('fullImage', ['slug' => $image->slug, 'size' => 'medium']) ?>[/img][/url]" readonly="readonly" />
        </div>
        <button class="btn btn-primary btn-clipboard col-5 col-sm-4 col-md-3 col-lg-2 rounded-0" data-clipboard-target="#forum_image"><i class="fa fa-clipboard"></i> Copier</button>
    </div>
</div>

<?= $this->insert('shared/images', ['images' => $image->getRelatedImages()]) ?>
