@php
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Support\Str;

    /*
    |--------------------------------------------------------------------------
    | Référencement et aperçus de partage
    |--------------------------------------------------------------------------
    |
    | Ce partiel est la source unique des métadonnées de toutes les pages
    | publiques : titre, description, canonique, Open Graph, X (Twitter)
    | et données structurées schema.org.
    |
    | Il reprend les @section('title') et @section('meta_description') déjà
    | définies par chaque vue, afin que la balise <title>, og:title et le
    | JSON-LD racontent toujours la même chose.
    |
    */

    /*
     * Type de page, déterminé par la route et non par la présence d’une
     * variable : @extends transmet au gabarit toutes les variables de la
     * vue enfant, y compris la variable de boucle $article laissée par un
     * @foreach. S’y fier ferait passer l’accueil pour un article.
     */
    $isArticlePage  = request()->routeIs('articles.show') && isset($article);
    $isCategoryPage = request()->routeIs('categories.show') && isset($category);
    $isAuthorPage   = request()->routeIs('authors.show') && isset($author);
    $isStaticPage   = request()->routeIs('pages.show') && isset($page);

    $siteName = 'Lambi News';
    $tagline  = 'Le citoyen au cœur de l’information';

    $defaultTitle = $siteName.' — '.$tagline;

    $defaultDescription = 'Lambi News vous informe sur l’actualité '
        .'nationale et internationale.';

    /*
     * Titre et description réellement affichés par la page.
     */
    $pageTitle = trim($__env->yieldContent('title')) ?: $defaultTitle;

    $pageDescription = Str::limit(
        trim(preg_replace(
            '/\s+/u',
            ' ',
            strip_tags($__env->yieldContent('meta_description'))
        )) ?: $defaultDescription,
        200
    );

    /*
     * Pagination : présente sur les rubriques, les auteurs et la recherche.
     */
    $paginator = (isset($articles) && $articles instanceof LengthAwarePaginator)
        ? $articles
        : null;

    $currentPage = $paginator?->currentPage() ?? 1;

    /*
     * Canonique auto-référente. Sur une page 2, 3… elle pointe sur cette
     * page précise : Google veut une canonique par page paginée, pas un
     * renvoi systématique vers la première.
     */
    $canonicalUrl = $currentPage > 1
        ? url()->current().'?page='.$currentPage
        : url()->current();

    /*
     * Les résultats de recherche sont volontairement désindexés : contenu
     * mince et duplicable à l’infini par un paramètre d’URL.
     */
    $isSearch = request()->routeIs('search');

    // Le rapport de campagne est une page privée remise à un annonceur :
    // elle ne doit jamais atterrir dans un index.
    $isPrivate = $isSearch || request()->routeIs('ads.report');

    $robots = $isPrivate
        ? 'noindex, follow'
        : 'index, follow, max-image-preview:large, max-snippet:-1, '
            .'max-video-preview:-1';

    /*
     * Image de partage : 1200×630 dans tous les cas.
     *
     * Pour un article, elle est composée à la volée (photo + logo + titre)
     * par OgImageGenerator ; ailleurs, c’est la carte par défaut du site.
     */
    $ogImage = $isArticlePage
        ? $article->og_image_url
        : asset('images/og-default.jpg');

    $ogType = $isArticlePage ? 'article' : 'website';

    /*
     * Fil d’Ariane, repris à l’identique en JSON-LD.
     */
    $breadcrumbs = [['name' => 'Accueil', 'url' => route('home')]];

    if ($isArticlePage) {
        if ($article->category) {
            if ($article->category->parent) {
                $breadcrumbs[] = [
                    'name' => $article->category->parent->name,
                    'url'  => route('categories.show', $article->category->parent->slug),
                ];
            }

            $breadcrumbs[] = [
                'name' => $article->category->name,
                'url'  => route('categories.show', $article->category->slug),
            ];
        }

        $breadcrumbs[] = ['name' => $article->title, 'url' => $canonicalUrl];
    } elseif ($isCategoryPage) {
        if ($category->parent) {
            $breadcrumbs[] = [
                'name' => $category->parent->name,
                'url'  => route('categories.show', $category->parent->slug),
            ];
        }

        $breadcrumbs[] = ['name' => $category->name, 'url' => $canonicalUrl];
    } elseif ($isAuthorPage) {
        $breadcrumbs[] = ['name' => $author->name, 'url' => $canonicalUrl];
    } elseif ($isStaticPage) {
        $breadcrumbs[] = ['name' => $page->title, 'url' => $canonicalUrl];
    }

    /*
     * Données structurées.
     *
     * Un seul @graph plutôt que plusieurs blocs séparés : les entités s’y
     * référencent entre elles (@id), ce que Google exploite mieux.
     */
    $organizationId = route('home').'#organization';
    $websiteId      = route('home').'#website';

    $graph = [];

    $graph[] = [
        '@type'  => 'NewsMediaOrganization',
        '@id'    => $organizationId,
        'name'   => $siteName,
        'url'    => route('home'),
        'slogan' => $tagline,
        'email'  => 'info.lambinews@gmail.com',
        'logo'   => [
            '@type'  => 'ImageObject',
            'url'    => asset('images/lambinews-embleme.jpg'),
            'width'  => 477,
            'height' => 419,
        ],
        'sameAs' => [
            'https://www.facebook.com/lambinews/',
            'https://www.tiktok.com/@lambinews',
            'https://www.instagram.com/info.lambinews/',
        ],
    ];

    /*
     * WebSite + SearchAction : c’est ce qui permet à Google d’afficher un
     * champ de recherche directement sous le site dans ses résultats.
     */
    $graph[] = [
        '@type'      => 'WebSite',
        '@id'        => $websiteId,
        'url'        => route('home'),
        'name'       => $siteName,
        'description' => $defaultDescription,
        'inLanguage' => 'fr',
        'publisher'  => ['@id' => $organizationId],
        'potentialAction' => [
            '@type'  => 'SearchAction',
            'target' => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => route('search').'?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    if (count($breadcrumbs) > 1) {
        $graph[] = [
            '@type'           => 'BreadcrumbList',
            '@id'             => $canonicalUrl.'#breadcrumb',
            'itemListElement' => collect($breadcrumbs)
                ->map(fn ($crumb, $i) => [
                    '@type'    => 'ListItem',
                    'position' => $i + 1,
                    'name'     => $crumb['name'],
                    'item'     => $crumb['url'],
                ])
                ->values()
                ->all(),
        ];
    }

    if ($isArticlePage) {
        $plainBody = trim(preg_replace(
            '/\s+/u',
            ' ',
            strip_tags($article->content ?? '')
        ));

        /*
         * Un publireportage n’est pas un article de presse : le déclarer
         * NewsArticle tromperait Google Actualités. On le publie en
         * « Article » avec son sponsor déclaré.
         */
        $graph[] = [
            '@type'            => $article->is_sponsored ? 'Article' : 'NewsArticle',
            '@id'              => $canonicalUrl.'#article',
            'headline'         => Str::limit($article->title, 110, ''),
            'description'      => $pageDescription,
            'image'            => [$ogImage],
            'datePublished'    => $article->published_at?->toIso8601String(),
            'dateModified'     => $article->updated_at?->toIso8601String(),
            'inLanguage'       => 'fr',
            'isAccessibleForFree' => true,
            'wordCount'        => Str::of($plainBody)->explode(' ')->filter()->count(),
            'articleSection'   => $article->category?->name,
            'keywords'         => $article->tags->pluck('name')->implode(', '),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
            'isPartOf'         => ['@id' => $websiteId],
            'publisher'        => ['@id' => $organizationId],
            'sponsor'          => $article->is_sponsored && $article->sponsor_name
                ? array_filter([
                    '@type' => 'Organization',
                    'name'  => $article->sponsor_name,
                    'url'   => $article->sponsor_url ?: null,
                ])
                : null,
            'author'           => $article->author ? [
                '@type' => 'Person',
                'name'  => $article->author->name,
                'url'   => route('authors.show', $article->author->slug),
            ] : ['@id' => $organizationId],
        ];
    } elseif ($isAuthorPage) {
        $graph[] = [
            '@type'      => 'ProfilePage',
            '@id'        => $canonicalUrl.'#profile',
            'url'        => $canonicalUrl,
            'name'       => $author->name,
            'isPartOf'   => ['@id' => $websiteId],
            'inLanguage' => 'fr',
            'mainEntity' => array_filter([
                '@type'       => 'Person',
                'name'        => $author->name,
                'url'         => $canonicalUrl,
                'jobTitle'    => $author->job_title ?: null,
                'description' => $author->bio ? Str::limit(strip_tags($author->bio), 300) : null,
                'image'       => $author->photo ? asset('storage/'.$author->photo) : null,
                'worksFor'    => ['@id' => $organizationId],
                'sameAs'      => array_values(array_filter([
                    $author->facebook_url,
                    $author->instagram_url,
                    $author->tiktok_url,
                ])),
            ], fn ($value) => $value !== null && $value !== []),
        ];
    } elseif ($isCategoryPage) {
        $graph[] = [
            '@type'       => 'CollectionPage',
            '@id'         => $canonicalUrl.'#collection',
            'url'         => $canonicalUrl,
            'name'        => $category->name,
            'description' => $pageDescription,
            'isPartOf'    => ['@id' => $websiteId],
            'inLanguage'  => 'fr',
        ];
    } elseif ($isStaticPage) {
        $graph[] = [
            '@type'      => 'WebPage',
            '@id'        => $canonicalUrl.'#webpage',
            'url'        => $canonicalUrl,
            'name'       => $page->title,
            'description' => $pageDescription,
            'isPartOf'   => ['@id' => $websiteId],
            'inLanguage' => 'fr',
        ];
    }

    // Retire les clés nulles : un « sponsor: null » dans le JSON-LD est
    // signalé comme une erreur par les validateurs.
    $graph = array_map(
        fn (array $node) => array_filter($node, fn ($value) => $value !== null),
        $graph
    );

    $structuredData = ['@context' => 'https://schema.org', '@graph' => $graph];
@endphp

<link rel="canonical" href="{{ $canonicalUrl }}">

<meta name="robots" content="{{ $robots }}">

{{-- Pagination : aide les moteurs à relier les pages d’une même liste. --}}
@if($paginator)
    @if($paginator->currentPage() > 1)
        <link rel="prev" href="{{ $paginator->previousPageUrl() }}">
    @endif

    @if($paginator->hasMorePages())
        <link rel="next" href="{{ $paginator->nextPageUrl() }}">
    @endif
@endif

<link
    rel="alternate"
    type="application/rss+xml"
    title="{{ $siteName }} — Flux RSS"
    href="{{ route('feed') }}"
>

<meta name="theme-color" content="#080808">


{{-- ============================================================= --}}
{{-- OPEN GRAPH (Facebook, WhatsApp, LinkedIn, Messenger) --}}
{{-- ============================================================= --}}

<meta property="og:locale" content="fr_FR">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">

{{--
    Les dimensions sont déclarées explicitement : sans elles, Facebook
    doit télécharger et mesurer l’image avant d’afficher l’aperçu, ce qui
    fait souvent échouer le tout premier partage d’un lien.
--}}
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:secure_url" content="{{ $ogImage }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $pageTitle }}">

@if($isArticlePage)
    <meta
        property="article:published_time"
        content="{{ $article->published_at?->toIso8601String() }}"
    >

    <meta
        property="article:modified_time"
        content="{{ $article->updated_at?->toIso8601String() }}"
    >

    @if($article->category)
        <meta property="article:section" content="{{ $article->category->name }}">
    @endif

    @if($article->author)
        <meta
            property="article:author"
            content="{{ route('authors.show', $article->author->slug) }}"
        >
    @endif

    @foreach($article->tags as $tag)
        <meta property="article:tag" content="{{ $tag->name }}">
    @endforeach
@endif


{{-- ============================================================= --}}
{{-- X (TWITTER) --}}
{{-- ============================================================= --}}

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ $pageTitle }}">


{{-- ============================================================= --}}
{{-- DONNÉES STRUCTURÉES --}}
{{-- ============================================================= --}}

<script type="application/ld+json">
{!! json_encode(
    $structuredData,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRETTY_PRINT
) !!}
</script>
