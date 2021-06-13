<x-layout>
    <div id="tagcloud">
        @foreach ($tags as $tag)
            <a data-weight="{{ $tag->getPopularity() }}" href="/tag/{{ $tag->label }}">
                {{ $tag->label }}
            </a>
        @endforeach
    </div>
</x-layout>
