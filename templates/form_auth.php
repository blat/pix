<?php $this->layout('layout') ?>

<form method="post">
    <div class="form-group row">
        <label for="name" class="col-sm-2 col-form-label">Pseudo :</label>
        <div class="col-sm-10">
            <input id="name" class="form-control" type="text" name="username" />
        </div>
    </div>

    <div class="form-group row">
        <label for="password" class="col-sm-2 col-form-label">Mot de passe :</label>
        <div class="col-sm-10">
            <input id="password" class="form-control" type="password" name="password" />
        </div>
    </div>

    <div class="form-group row">
        <div class="offset-sm-2 col-sm-10">
            <button type="submit" class="btn btn-primary">Valider</button>
        </div>
    </div>
</form>
