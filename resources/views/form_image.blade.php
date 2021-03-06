<x-layout>
    <form method="post" enctype="multipart/form-data">
        @if (empty($image))
        <div class="form-group row">
            <label for="image" class="col-sm-2 col-form-label">Image :</label>
            <div class="col-sm-10">
                <input id="image" class="form-control-file" type="file" name="image" />
            </div>
        </div>
        @endif

        <div class="form-group row">
            <label for="tags" class="col-sm-2 col-form-label">Tags :</label>
            <div class="col-sm-10">
                <input id="tags" class="form-control" type="text" name="tags" value="@if (!empty($image)) {{ implode(', ', $image->getLabels()) }} @endif" />
                <small class="form-text text-muted">Séparés par des virgules.</small>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
                <label for="private" class="form-check-label">
                    <input id="private" class="form-check-input" type="checkbox" name="private" @if (!empty($image) && $image->private) checked="checked" @endif /> Cette image est privée
                </label>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
                <input name="MAX_FILE_SIZE" value="2048000" type="hidden" />
                <button type="submit" class="btn btn-primary">Valider</button>
            </div>
        </div>
    </form>
</x-layout>
