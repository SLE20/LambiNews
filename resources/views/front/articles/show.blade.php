@extends('front.layouts.app')

@section(
    'title',
    ($article->seo_title ?: $article->title).' — Lambi News'
)

@section(
    'meta_description',
    $article->seo_description
        ?: $article->excerpt
        ?: 'Lire cet article sur Lambi News'
)

@php
    $articleUrl = url()->current();
    $shareTitle = $article->title;

    /*
     * Nettoyage du contenu HTML.
     */
    $decodedContent = html_entity_decode(
        $article->content,
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );

    $cleanArticleContent = preg_replace(
        '/<p\b[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/iu',
        '',
        $decodedContent
    );

    $cleanArticleContent = preg_replace(
        '/(?:<br\s*\/?>\s*){3,}/iu',
        '<br><br>',
        $cleanArticleContent
    );

    /*
     * Recherche de la photo de l’auteur.
     * Le code accepte profile_photo, avatar ou photo.
     */
    $authorPhoto = null;
    $authorPhotoUrl = null;

    if ($article->author) {
        $authorPhoto = $article->author->profile_photo
            ?? $article->author->avatar
            ?? $article->author->photo
            ?? null;

        if ($authorPhoto) {
            $authorPhotoUrl = \Illuminate\Support\Str::startsWith(
                $authorPhoto,
                ['http://', 'https://']
            )
                ? $authorPhoto
                : asset(
                    'storage/'.ltrim($authorPhoto, '/')
                );
        }
    }
@endphp

@push('styles')
    <style>
        .article {
            max-width: 920px;
            margin: 0 auto;
        }

        .article-header {
            margin-bottom: 1.7rem;
        }

        .article-header h1 {
            margin: 0.8rem 0 1rem;
            color: var(--black);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(2.3rem, 6vw, 4.8rem);
            letter-spacing: -0.035em;
            line-height: 1.04;
        }

        .article-excerpt {
            max-width: 800px;
            margin: 0;
            color: var(--muted);
            font-size: clamp(1.05rem, 2.5vw, 1.3rem);
            line-height: 1.55;
        }

        .article-information {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem 1.2rem;
            margin-top: 1.3rem;
            padding-bottom: 1.3rem;
            border-bottom: 1px solid var(--border);
        }

        .author-information {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        .author-avatar {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid var(--primary);
            border-radius: 50%;
            color: var(--black);
            background: var(--primary);
            font-family: Arial, sans-serif;
            font-weight: 800;
        }

        .author-avatar-image {
            display: block;
            padding: 0;
            background: #eeeae1;
            object-fit: cover;
            object-position: center;
        }

        .author-details {
            min-width: 0;
        }

        .author-details strong,
        .author-details .meta {
            display: block;
        }

        .author-details a {
            color: var(--black);
            text-decoration: none;
        }

        .author-details a:hover {
            color: #9d7306;
        }

        .article-figure {
            margin: 2rem 0 1.5rem;
        }

        .article-image {
            width: 100%;
            height: auto;
            max-height: 620px;
            display: block;
            border-radius: 16px;
            background: #ece8de;
            object-fit: cover;
        }

        .article-caption {
            display: block;
            margin-top: 0.55rem;
            color: var(--muted);
            font-size: 0.8rem;
            font-style: italic;
            line-height: 1.5;
        }

        .share-section {
            margin: 1.7rem 0;
            padding: 1.15rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: #fff;
        }

        .share-title {
            margin: 0 0 0.8rem;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .share-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .share-button {
            min-height: 40px;
            padding: 0.55rem 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            font-size: 0.84rem;
            font-weight: 700;
            text-decoration: none;
            transition:
                opacity 0.2s ease,
                transform 0.2s ease;
        }

        .share-button:hover {
            color: #fff;
            opacity: 0.88;
            transform: translateY(-2px);
        }

        .share-facebook {
            background: #1877f2;
        }

        .share-whatsapp {
            background: #168b46;
        }

        .share-email {
            background: #4b5563;
        }

        .share-copy {
            background: var(--black);
        }

        .copy-message {
            display: none;
            margin-top: 0.75rem;
            color: #168b46;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .copy-message.visible {
            display: block;
        }

        /*
         * Contenu principal.
         */
        .article-body {
            max-width: 780px;
            margin: 2rem auto 0;
            color: #302e29;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 1.12rem;
            line-height: 1.72;
            white-space: normal !important;
            overflow-wrap: break-word;
        }

        .article-body > *:first-child {
            margin-top: 0 !important;
        }

        .article-body > *:last-child {
            margin-bottom: 0 !important;
        }

        .article-body p {
            margin: 0 0 1rem !important;
            padding: 0;
            line-height: 1.72;
            white-space: normal !important;
        }

        .article-body p:empty {
            display: none;
        }

        .article-body h2 {
            position: relative;
            margin: 2.2rem 0 0.8rem !important;
            padding-bottom: 0.65rem;
            color: var(--black);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(1.6rem, 4vw, 2.25rem);
            line-height: 1.25;
        }

        .article-body h2::after {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 46px;
            height: 3px;
            border-radius: 10px;
            background: var(--primary);
            content: "";
        }

        .article-body h3 {
            margin: 1.7rem 0 0.65rem !important;
            color: var(--black);
            font-size: 1.35rem;
            line-height: 1.3;
        }

        .article-body h4 {
            margin: 1.5rem 0 0.6rem !important;
            color: var(--black);
            font-size: 1.15rem;
            line-height: 1.35;
        }

        .article-body ul,
        .article-body ol {
            margin: 0.8rem 0 1.2rem !important;
            padding-left: 1.6rem;
        }

        .article-body li {
            margin-bottom: 0.4rem;
            line-height: 1.65;
        }

        .article-body li::marker {
            color: #a77b08;
            font-weight: 700;
        }

        .article-body blockquote {
            margin: 1.5rem 0 !important;
            padding: 1rem 1.25rem;
            border-left: 4px solid var(--primary);
            border-radius: 0 10px 10px 0;
            color: #49443b;
            background: #faf6e9;
            font-size: 1.08rem;
            font-style: italic;
            line-height: 1.65;
        }

        .article-body blockquote p {
            margin-bottom: 0.7rem !important;
        }

        .article-body blockquote p:last-child {
            margin-bottom: 0 !important;
        }

        .article-body a {
            color: #936c06;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: rgba(147, 108, 6, 0.4);
            text-underline-offset: 3px;
        }

        .article-body a:hover {
            color: var(--black);
            text-decoration-color: var(--black);
        }

        .article-body img {
            max-width: 100%;
            height: auto;
            margin: 1.2rem auto;
            display: block;
            border-radius: 12px;
        }

        .article-body figure {
            margin: 1.5rem 0;
        }

        .article-body figcaption {
            margin-top: 0.45rem;
            color: var(--muted);
            font-family: Arial, sans-serif;
            font-size: 0.8rem;
            line-height: 1.5;
            text-align: center;
        }

        .article-body iframe,
        .article-body video {
            max-width: 100%;
        }

        .article-body table {
            width: 100%;
            margin: 1.4rem 0;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 0.95rem;
        }

        .article-body th,
        .article-body td {
            padding: 0.75rem;
            border: 1px solid var(--border);
            text-align: left;
        }

        .article-body th {
            color: var(--black);
            background: #f6f2e8;
        }

        .article-tags {
            max-width: 780px;
            margin: 2rem auto 0;
            padding-top: 1.3rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            border-top: 1px solid var(--border);
        }

        .article-tag {
            padding: 0.4rem 0.75rem;
            display: inline-flex;
            border: 1px solid #e6cb7b;
            border-radius: 999px;
            color: #51400d;
            background: #fff8df;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .related-section {
            margin-top: 4rem;
            padding-top: 2.2rem;
            border-top: 1px solid var(--border);
        }

        .related-section .article-card img,
        .related-section .image-placeholder {
            width: 100%;
            aspect-ratio: 16 / 10;
            display: block;
            object-fit: cover;
        }

        @media (max-width: 767px) {
            .article-header h1 {
                font-size: clamp(2.1rem, 11vw, 3.3rem);
            }

            .article-information {
                align-items: flex-start;
                flex-direction: column;
            }

            .article-figure {
                margin-top: 1.4rem;
            }

            .article-image {
                border-radius: 11px;
            }

            .article-body {
                margin-top: 1.5rem;
                font-size: 1.02rem;
                line-height: 1.65;
            }

            .article-body p {
                margin-bottom: 0.9rem !important;
                line-height: 1.65;
            }

            .article-body h2 {
                margin-top: 1.8rem !important;
            }

            .share-buttons {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .share-button {
                width: 100%;
            }

            .related-section {
                margin-top: 3rem;
            }
        }

        @media (max-width: 420px) {
            .share-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <article class="article">
        <header class="article-header">
            @if($article->category)
                <a
                    href="{{ route(
                        'categories.show',
                        $article->category->slug
                    ) }}"
                    class="category"
                >
                    {{ $article->category->name }}
                </a>
            @endif

            <h1>{{ $article->title }}</h1>

            @if($article->excerpt)
                <p class="article-excerpt">
                    {{ $article->excerpt }}
                </p>
            @endif

            <div class="article-information">
                @if($article->author)
                    <div class="author-information">
                        @if($authorPhotoUrl)
                            <img
                                src="{{ $authorPhotoUrl }}"
                                alt="Photo de {{ $article->author->name }}"
                                class="author-avatar author-avatar-image"
                                width="46"
                                height="46"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <span class="author-avatar">
                                {{ mb_strtoupper(
                                    mb_substr(
                                        $article->author->name,
                                        0,
                                        1
                                    )
                                ) }}
                            </span>
                        @endif

                        <div class="author-details">
                            <strong>
                                <a
                                    href="{{ route(
                                        'authors.show',
                                        $article->author->slug
                                    ) }}"
                                >
                                    {{ $article->author->name }}
                                </a>
                            </strong>

                            <span class="meta">
                                Auteur
                            </span>
                        </div>
                    </div>
                @endif

                @if($article->published_at)
                    <div class="meta">
                        Publié le
                        {{ $article->published_at->translatedFormat(
                            'd F Y à H:i'
                        ) }}
                    </div>
                @endif

                <div class="meta">
                    {{ number_format(
                        $article->views_count ?? 0,
                        0,
                        ',',
                        ' '
                    ) }}
                    vue(s)
                </div>
            </div>
        </header>

        @if($article->featured_image)
            <figure class="article-figure">
                <img
                    class="article-image"
                    src="{{ asset(
                        'storage/'.$article->featured_image
                    ) }}"
                    alt="{{ $article->title }}"
                    width="1200"
                    height="720"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >

                @if($article->image_caption)
                    <figcaption class="article-caption">
                        {{ $article->image_caption }}
                    </figcaption>
                @endif
            </figure>
        @endif

        <div class="share-section">
            <p class="share-title">
                Partager cet article
            </p>

            <div class="share-buttons">
                <a
                    href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($articleUrl) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="share-button share-facebook"
                >
                    Facebook
                </a>

                <a
                    href="https://wa.me/?text={{ urlencode(
                        $shareTitle.' '.$articleUrl
                    ) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="share-button share-whatsapp"
                >
                    WhatsApp
                </a>

                <a
                    href="mailto:?subject={{ urlencode($shareTitle) }}&body={{ urlencode($articleUrl) }}"
                    class="share-button share-email"
                >
                    Courriel
                </a>

                <button
                    type="button"
                    class="share-button share-copy"
                    id="copy-article-link"
                    data-url="{{ $articleUrl }}"
                >
                    Copier le lien
                </button>
            </div>

            <div
                class="copy-message"
                id="copy-message"
                role="status"
                aria-live="polite"
            >
                Le lien a été copié.
            </div>
        </div>

        <div class="article-body">
            {!! $cleanArticleContent !!}
        </div>

        @if($article->tags->isNotEmpty())
            <div class="article-tags">
                @foreach($article->tags as $tag)
                    <span class="article-tag">
                        #{{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </article>

    @if($relatedArticles->isNotEmpty())
        <section class="related-section">
            <div class="section-heading">
                <h2 class="section-title">
                    À lire également
                </h2>
            </div>

            <div class="articles-grid">
                @foreach($relatedArticles as $related)
                    <article class="article-card">
                        <a href="{{ route('articles.show', $related) }}">
                            @if($related->featured_image)
                                <img
                                    src="{{ asset(
                                        'storage/'.$related->featured_image
                                    ) }}"
                                    alt="{{ $related->title }}"
                                    width="640"
                                    height="400"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div
                                    class="image-placeholder"
                                    role="img"
                                    aria-label="{{ $related->title }}"
                                ></div>
                            @endif
                        </a>

                        <div class="article-card-content">
                            @if($related->category)
                                <a
                                    href="{{ route(
                                        'categories.show',
                                        $related->category->slug
                                    ) }}"
                                    class="category"
                                >
                                    {{ $related->category->name }}
                                </a>
                            @endif

                            <h3>
                                <a href="{{ route('articles.show', $related) }}">
                                    {{ $related->title }}
                                </a>
                            </h3>

                            @if($related->published_at)
                                <div class="meta">
                                    {{ $related->published_at
                                        ->translatedFormat('d F Y') }}
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @include('front.articles.comments')
@endsection

@push('scripts')
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const copyButton = document.getElementById(
                    'copy-article-link'
                );

                const copyMessage = document.getElementById(
                    'copy-message'
                );

                if (!copyButton) {
                    return;
                }

                copyButton.addEventListener(
                    'click',
                    async function () {
                        const articleUrl = copyButton.dataset.url;

                        try {
                            await navigator.clipboard.writeText(
                                articleUrl
                            );

                            if (copyMessage) {
                                copyMessage.classList.add('visible');
                            }

                            copyButton.textContent = 'Lien copié';

                            setTimeout(function () {
                                if (copyMessage) {
                                    copyMessage.classList.remove(
                                        'visible'
                                    );
                                }

                                copyButton.textContent =
                                    'Copier le lien';
                            }, 2500);
                        } catch (error) {
                            window.prompt(
                                'Copiez le lien de l’article :',
                                articleUrl
                            );
                        }
                    }
                );
            }
        );
    </script>
@endpush