@php
    $defaultTitle = 'Lambi News — Le citoyen au cœur de l’information';

    $defaultDescription = 'Lambi News vous informe sur l’actualité nationale et internationale.';

    $seoTitle = isset($article)
        ? ($article->seo_title ?: $article->title).' — Lambi News'
        : (isset($category)
            ? $category->name.' — Lambi News'
            : $defaultTitle);

    $seoDescription = isset($article)
        ? ($article->seo_description
            ?: $article->excerpt
            ?: \Illuminate\Support\Str::limit(
                strip_tags($article->content),
                160
            ))
        : (isset($category)
            ? ($category->description
                ?: 'Découvrez les dernières nouvelles de la rubrique '.$category->name.'.')
            : $defaultDescription);

    $seoImage = isset($article) && $article->featured_image
        ? asset('storage/'.$article->featured_image)
        : asset('images/lambinews-embleme.jpg');

    $canonicalUrl = url()->current();

    $schema = isset($article)
        ? [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article->title,
            'description' => $seoDescription,
            'image' => [$seoImage],
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl,
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $article->author->name,
            ],
            'publisher' => [
                '@type' => 'NewsMediaOrganization',
                'name' => 'Lambi News',
                'url' => route('home'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/lambinews-embleme.jpg'),
                ],
            ],
            'articleSection' => $article->category->name,
        ]
        : [
            '@context' => 'https://schema.org',
            '@type' => 'NewsMediaOrganization',
            'name' => 'Lambi News',
            'url' => route('home'),
            'description' => $defaultDescription,
            'email' => 'info.lambinews@gmail.com',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/lambinews-embleme.jpg'),
            ],
            'sameAs' => [
                'https://www.facebook.com/lambinews/',
                'https://www.tiktok.com/@lambinews',
                'https://www.instagram.com/info.lambinews/',
            ],
        ];
@endphp

<link rel="canonical" href="{{ $canonicalUrl }}">

<meta
    name="robots"
    content="index, follow, max-image-preview:large"
>

<meta property="og:locale" content="fr_FR">
<meta property="og:site_name" content="Lambi News">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:alt" content="{{ $seoTitle }}">

<meta
    property="og:type"
    content="{{ isset($article) ? 'article' : 'website' }}"
>

@if(isset($article))
    <meta
        property="article:published_time"
        content="{{ $article->published_at?->toIso8601String() }}"
    >

    <meta
        property="article:modified_time"
        content="{{ $article->updated_at?->toIso8601String() }}"
    >

    <meta
        property="article:section"
        content="{{ $article->category->name }}"
    >

    @foreach($article->tags as $tag)
        <meta
            property="article:tag"
            content="{{ $tag->name }}"
        >
    @endforeach
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">

<script type="application/ld+json">
{!! json_encode(
    $schema,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRETTY_PRINT
) !!}
</script>