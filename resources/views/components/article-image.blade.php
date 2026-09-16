@props([
    'article',
    'class' => '',
    'eager' => false,
    'width' => 900,
    'height' => 560,
    /*
     * Largeur réelle occupée par l'image dans la page. Elle indique au
     * navigateur quelle taille choisir dans le srcset : sans elle, il
     * suppose la pleine largeur et télécharge toujours la plus grande.
     */
    'sizes' => '(max-width: 680px) 100vw, 400px',
])

@if($article->featured_image)
    {{--
        On ne sert jamais l'original : la rédaction téléverse des PNG de
        deux à trois mégaoctets. Les vignettes JPEG sont générées à la
        demande puis mises en cache.
    --}}
    <img
        src="{{ $article->thumbUrl($eager ? 1200 : 800) }}"
        srcset="{{ $article->thumbUrl(400) }} 400w,
                {{ $article->thumbUrl(800) }} 800w,
                {{ $article->thumbUrl(1200) }} 1200w"
        sizes="{{ $sizes }}"
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
