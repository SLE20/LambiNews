@props([
    'article',
    'class' => '',
    'eager' => false,
    'width' => 900,
    'height' => 560,
])

@if($article->featured_image)
    <img
        src="{{ asset('storage/'.$article->featured_image) }}"
        alt="{{ $article->title }}"
        class="{{ $class }}"
        width="{{ $width }}"
        height="{{ $height }}"
        loading="{{ $eager ? 'eager' : 'lazy' }}"
        decoding="async"
        fetchpriority="{{ $eager ? 'high' : 'auto' }}"
    >
@else
    <div
        class="image-placeholder {{ $class }}"
        role="img"
        aria-label="{{ $article->title }}"
    ></div>
@endif