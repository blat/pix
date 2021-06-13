<x-layout>
    @if (isset($title))
    <x-slot name="title">{{ $title }}</x-slot>
    @endif

    @include('shared.images', ['images' => $images])

    @if (isset($stats))
    <div class="meta text-muted text-right">
        {{ $stats['image_count'] }} images &mdash; {{ $stats['user_count'] }} utilisateurs &mdash; {{ round($stats['image_size']/1024/1024/1024, 2) }} Go
    </div>
    @endif
</x-layout>
