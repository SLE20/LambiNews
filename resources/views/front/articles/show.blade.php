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

    $authorPhoto = null;
    $authorPhotoUrl = null;

    if ($article->author) {

        $authorPhoto =
            $article->author->profile_photo
            ?? $article->author->avatar
            ?? $article->author->photo
            ?? null;

        if ($authorPhoto) {

            $authorPhotoUrl =
                \Illuminate\Support\Str::startsWith(
                    $authorPhoto,
                    ['http://', 'https://']
                )
                    ? $authorPhoto
                    : asset(
                        'storage/'.ltrim(
                            $authorPhoto,
                            '/'
                        )
                    );
        }
    }

@endphp


@push('styles')

<style>

    .article-page-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            330px;
        gap: 48px;
        align-items: start;
    }

    .article-main-column {
        min-width: 0;
    }

    .article {
        width: 100%;
        max-width: none;
        margin: 0;
    }

    .article-header {
        margin-bottom: 1.7rem;
    }

    .article-header h1 {
        margin: 0.8rem 0 1rem;
        color: var(--black);
        font-family:
            "Playfair Display",
            Georgia,
            "Times New Roman",
            serif;
        font-size: clamp(2.3rem, 5.5vw, 4.6rem);
        letter-spacing: -0.035em;
        line-height: 1.04;
    }

    .article-excerpt {
        max-width: 850px;
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
        color: var(--primary-dark);
    }

    .article-figure {
        margin: 2rem 0 1.5rem;
    }

    .article-image {
        width: 100%;
        height: auto;
        max-height: 650px;
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

    .share-telegram {
        background: #229ed9;
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

    .article-body {
        max-width: 830px;
        margin: 2rem 0 0;
        color: #302e29;
        font-family:
            Georgia,
            "Times New Roman",
            serif;
        font-size: 1.12rem;
        line-height: 1.78;
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
        line-height: 1.78;
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
        font-family:
            "Playfair Display",
            Georgia,
            serif;
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
        color: var(--primary-dark);
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

    .article-body a {
        color: #936c06;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .article-body img {
        max-width: 100%;
        height: auto;
        margin: 1.5rem auto;
        display: block;
        border-radius: 12px;
    }

    .article-body iframe,
    .article-body video {
        max-width: 100%;
    }

    .article-tags {
        max-width: 830px;
        margin: 2rem 0 0;
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

    /*
    |--------------------------------------------------------------------------
    | SIDEBAR
    |--------------------------------------------------------------------------
    */

    .support-box {
        margin-bottom: 24px;
        padding: 20px;
        border-radius: var(--radius);
        background: var(--black);
        color: #fff;
        text-align: center;
    }
    .support-box__title {
        margin: 0 0 8px;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.1rem;
    }
    .support-box__text {
        margin: 0 0 14px;
        font-size: .86rem;
        color: rgba(255, 255, 255, .75);
        line-height: 1.6;
    }
    .support-box__btn {
        display: inline-block;
        padding: 10px 22px;
        border-radius: 999px;
        background: var(--primary);
        color: var(--black);
        font-weight: 700;
        font-size: .88rem;
    }

    .sponsored-flag {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin: 0 0 14px;
        font-size: .9rem;
    }
    .sponsored-flag__tag {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 4px;
        background: #2f2a1c;
        color: var(--primary);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }
    .sponsored-flag__by { color: var(--muted); }
    .sponsored-flag__by a {
        color: var(--primary-dark);
        text-decoration: underline;
    }
    .sponsored-note {
        margin: 28px 0 0;
        padding: 16px 18px;
        border-left: 3px solid var(--primary);
        background: var(--background);
        border-radius: 8px;
        font-size: .9rem;
        color: var(--muted);
    }

    .article-sidebar {
        position: sticky;
        top: 85px;
        display: grid;
        gap: 26px;
    }

    .sidebar-block {
        padding: 21px;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        box-shadow:
            0 8px 25px rgba(30, 24, 12, 0.05);
    }

    .sidebar-title {
        position: relative;
        margin: 0 0 18px;
        padding-bottom: 12px;
        color: var(--black);
        font-family:
            "Playfair Display",
            Georgia,
            serif;
        font-size: 1.45rem;
        line-height: 1.2;
        border-bottom: 1px solid var(--border);
    }

    .sidebar-title::after {
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 65px;
        height: 3px;
        background: var(--primary);
        content: "";
    }

    /*
    |--------------------------------------------------------------------------
    | À LA UNE
    |--------------------------------------------------------------------------
    */

    .sidebar-story {
        display: grid;
        grid-template-columns:
            96px
            minmax(0, 1fr);
        gap: 13px;
        padding: 14px 0;
        border-bottom: 1px solid var(--border);
    }

    .sidebar-story:first-of-type {
        padding-top: 0;
    }

    .sidebar-story:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .sidebar-story img {
        width: 96px;
        height: 78px;
        display: block;
        border-radius: 9px;
        object-fit: cover;
    }

    .sidebar-image-placeholder {
        width: 96px;
        height: 78px;
        border-radius: 9px;
        background:
            linear-gradient(
                135deg,
                var(--primary),
                #1b180f
            );
    }

    .sidebar-story-content {
        min-width: 0;
    }

    .sidebar-category {
        display: inline-block;
        color: var(--primary-dark);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.07em;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .sidebar-story h4 {
        margin: 5px 0 0;
        font-family:
            "Playfair Display",
            Georgia,
            serif;
        font-size: 0.96rem;
        line-height: 1.35;
    }

    .sidebar-story h4 a {
        color: var(--black);
    }

    .sidebar-story h4 a:hover {
        color: var(--primary-dark);
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLICITÉ
    |--------------------------------------------------------------------------
    */

    .sidebar-ad {
        text-align: center;
    }

    .sidebar-small-label {
        display: inline-block;
        margin-bottom: 10px;
        color: var(--muted);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .ad-placeholder {
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border: 1px dashed #d8cda9;
        border-radius: 10px;
        color: #8b806a;
        background:
            linear-gradient(
                135deg,
                #faf7ee,
                #f3eedf
            );
        font-size: 0.9rem;
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | LES PLUS LUS
    |--------------------------------------------------------------------------
    */

    .popular-story {
        display: grid;
        grid-template-columns:
            42px
            minmax(0, 1fr);
        gap: 12px;
        padding: 15px 0;
        border-bottom: 1px solid var(--border);
    }

    .popular-story:first-of-type {
        padding-top: 0;
    }

    .popular-story:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .popular-story-number {
        color: var(--primary);
        font-family:
            "Playfair Display",
            Georgia,
            serif;
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1;
    }

    .popular-story-content {
        min-width: 0;
    }

    .popular-story h4 {
        margin: 4px 0 7px;
        color: var(--black);
        font-family:
            "Playfair Display",
            Georgia,
            serif;
        font-size: 0.98rem;
        line-height: 1.35;
    }

    .popular-story h4 a {
        color: inherit;
    }

    .popular-story h4 a:hover {
        color: var(--primary-dark);
    }

    .popular-story-meta {
        color: var(--muted);
        font-size: 0.75rem;
    }

    /*
    |--------------------------------------------------------------------------
    | ARTICLES LIÉS
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1050px) {

        .article-page-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .article-sidebar {
            position: static;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .sidebar-block:first-child {
            grid-column: 1 / -1;
        }
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
            line-height: 1.68;
        }

        .share-buttons {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .share-button {
            width: 100%;
        }

        .article-sidebar {
            grid-template-columns: 1fr;
        }

        .sidebar-block:first-child {
            grid-column: auto;
        }

        .related-section {
            margin-top: 3rem;
        }
    }

    @media (max-width: 420px) {

        .share-buttons {
            grid-template-columns: 1fr;
        }

        .sidebar-block {
            padding: 17px;
        }

        .sidebar-story {
            grid-template-columns:
                84px
                minmax(0, 1fr);
        }

        .sidebar-story img,
        .sidebar-image-placeholder {
            width: 84px;
            height: 70px;
        }
    }

</style>

@endpush


@section('content')

<div class="article-page-grid">

    <div class="article-main-column">

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

                @if($article->is_sponsored)
                    {{--
                        Mention obligatoire : elle doit être visible avant
                        le titre, pas enfouie en bas de page.
                    --}}
                    <p class="sponsored-flag">
                        <span class="sponsored-flag__tag">Contenu sponsorisé</span>

                        @if($article->sponsor_name)
                            <span class="sponsored-flag__by">
                                Publié pour
                                @if($article->sponsor_url)
                                    <a
                                        href="{{ $article->sponsor_url }}"
                                        rel="nofollow sponsored noopener"
                                        target="_blank"
                                    >{{ $article->sponsor_name }}</a>
                                @else
                                    {{ $article->sponsor_name }}
                                @endif
                            </span>
                        @endif
                    </p>
                @endif

                <h1>
                    {{ $article->title }}
                </h1>

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

                            {{ $article
                                ->published_at
                                ->timezone('America/Port-au-Prince')
                                ->translatedFormat('d F Y à H:i')
                            }}

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
                        href="https://t.me/share/url?url={{ urlencode($articleUrl) }}&text={{ urlencode($shareTitle) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="share-button share-telegram"
                    >
                        Telegram
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

            @if(
                $article->tags
                && $article->tags->isNotEmpty()
            )

                <div class="article-tags">

                    @foreach($article->tags as $tag)

                        <span class="article-tag">
                            #{{ $tag->name }}
                        </span>

                    @endforeach

                </div>

            @endif

        </article>

        <x-telegram-cta variant="inline" />

        <x-ad-slot position="below_article" />

    </div>


    <aside class="article-sidebar">

        <x-telegram-cta />

        <x-ad-slot position="sidebar_top" />

        <div style="margin-bottom:24px">
            <x-featured-fundraiser />
        </div>

        <div style="margin-bottom:24px">
            <x-latest-poll />
        </div>

        {{-- Appel au soutien, là où le lecteur vient de lire un article. --}}
        <div class="support-box">
            <p class="support-box__title">Soutenez notre travail</p>
            <p class="support-box__text">
                Lambi News est un média indépendant. Un don, même modeste,
                finance le travail de terrain.
            </p>
            <a href="{{ route('donations.create') }}" class="support-box__btn">
                Faire un don
            </a>
        </div>

        {{-- À LA UNE --}}

        <div class="sidebar-block">

            <h3 class="sidebar-title">
                À la une
            </h3>

            @forelse(
                ($relatedArticles ?? collect())->take(4)
                as $related
            )

                <article class="sidebar-story">

                    <a
                        href="{{ route(
                            'articles.show',
                            $related
                        ) }}"
                    >

                        @if($related->featured_image)

                            <img
                                src="{{ asset(
                                    'storage/'.$related->featured_image
                                ) }}"
                                alt="{{ $related->title }}"
                                loading="lazy"
                                decoding="async"
                            >

                        @else

                            <div
                                class="sidebar-image-placeholder"
                            ></div>

                        @endif

                    </a>

                    <div class="sidebar-story-content">

                        @if($related->category)

                            <span class="sidebar-category">
                                {{ $related->category->name }}
                            </span>

                        @endif

                        <h4>

                            <a
                                href="{{ route(
                                    'articles.show',
                                    $related
                                ) }}"
                            >
                                {{ $related->title }}
                            </a>

                        </h4>

                    </div>

                </article>

            @empty

                <p class="meta">
                    Aucun autre article disponible.
                </p>

            @endforelse

        </div>


        {{-- PUBLICITÉ --}}

        <div class="sidebar-block sidebar-ad">

            <span class="sidebar-small-label">
                Publicité
            </span>

            <div class="ad-placeholder">
                Espace publicitaire
            </div>

        </div>


        {{-- LES PLUS LUS --}}

        <div class="sidebar-block">

            <h3 class="sidebar-title">
                Les plus lus
            </h3>

            @forelse(
                ($popularArticles ?? collect())->take(5)
                as $popular
            )

                <article class="popular-story">

                    <div class="popular-story-number">
                        {{ str_pad(
                            $loop->iteration,
                            2,
                            '0',
                            STR_PAD_LEFT
                        ) }}
                    </div>

                    <div class="popular-story-content">

                        @if($popular->category)

                            <span class="sidebar-category">
                                {{ $popular->category->name }}
                            </span>

                        @endif

                        <h4>

                            <a
                                href="{{ route(
                                    'articles.show',
                                    $popular
                                ) }}"
                            >
                                {{ $popular->title }}
                            </a>

                        </h4>

                        <div class="popular-story-meta">

                            {{ number_format(
                                $popular->views_count ?? 0,
                                0,
                                ',',
                                ' '
                            ) }}

                            vues

                        </div>

                    </div>

                </article>

            @empty

                <p class="meta">
                    Aucun article populaire disponible.
                </p>

            @endforelse

        </div>

    </aside>

</div>


@if(
    isset($relatedArticles)
    && $relatedArticles->isNotEmpty()
)

    <section class="related-section">

        <div class="section-heading">

            <h2 class="section-title">
                À lire également
            </h2>

        </div>

        <div class="articles-grid">

            @foreach($relatedArticles as $related)

                <article class="article-card">

                    <a
                        href="{{ route(
                            'articles.show',
                            $related
                        ) }}"
                    >

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

                            <a
                                href="{{ route(
                                    'articles.show',
                                    $related
                                ) }}"
                            >
                                {{ $related->title }}
                            </a>

                        </h3>

                        @if($related->published_at)

                            <div class="meta">

                                {{ $related
                                    ->published_at
                                    ->timezone('America/Port-au-Prince')
                                    ->translatedFormat('d F Y')
                                }}

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

            const copyButton =
                document.getElementById(
                    'copy-article-link'
                );

            const copyMessage =
                document.getElementById(
                    'copy-message'
                );

            if (!copyButton) {
                return;
            }

            copyButton.addEventListener(
                'click',
                async function () {

                    const articleUrl =
                        copyButton.dataset.url;

                    try {

                        await navigator
                            .clipboard
                            .writeText(articleUrl);

                        if (copyMessage) {
                            copyMessage
                                .classList
                                .add('visible');
                        }

                        copyButton.textContent =
                            'Lien copié';

                        setTimeout(
                            function () {

                                if (copyMessage) {
                                    copyMessage
                                        .classList
                                        .remove('visible');
                                }

                                copyButton.textContent =
                                    'Copier le lien';

                            },
                            2500
                        );

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