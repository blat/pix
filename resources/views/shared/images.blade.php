<div class="row">
    @foreach ($images as $image)
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 p-3">
        <a class="col-12 p-0" href="{{ $image->getUrl() }}">
            <img class="img-fluid col-12 p-0 border border-light" src="{{ $image->getUrl('square') }}" />
        </a>
    </div>
    @endforeach
</div>
