@extends('front.layouts.app')

@section(
    'title',
    'Lambi News — Le citoyen au cœur de l’information'
)

@section(
    'meta_description',
    'Retrouvez toute l’actualité nationale et internationale sur Lambi News.'
)

@push('styles')
    <style>
        .popular-section {
            margin: 65px 0;
            padding: clamp(24px, 5vw, 38px);
            color: white;
            border-radius: var(--radius);
            background:
                radial-gradient(
                    circle at 90% 10%,
                    rgba(216, 169, 34, 0.22),
                    transparent 32%
                ),
                var(--black);
        }

        .popular-section .section-title {
            color: white;
        }

        .popular-section .section-link {
            color: var(--primary);
        }

        .popular-layout {
            display: grid;
            grid-template-columns:
                minmax(0, 1.15fr)
                minmax(300px, 0.85fr);
            gap: 32px;
        }

        .popular-main {
            overflow: hidden;
            border-radius: 12px;
            background: var(--black-light);
        }

        .popular-main-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .popular-main-content {
            padding: 24px;
        }

        .popular-main h3 {
            margin: 9px 0 12px;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: clamp(25px, 4vw, 35px);
            line-height: 1.18;
        }

        .popular-main h3 a:hover {
            color: var(--primary);
        }

        .popular-list {
            display: grid;
        }

        .popular-item {
            display: grid;
            grid-template-columns:
                42px minmax(0, 1fr);
            gap: 13px;
            padding: 17px 0;
            border-bottom:
                1px solid rgba(255, 255, 255, 0.12);
        }

        .popular-item:first-child {
            padding-top: 0;
        }

        .popular-item:last-child {
            border-bottom: 0;
        }

        .popular-number {
            color: var(--primary);
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: 32px;
            font-weight: 800;
            line-height: 1;
        }

        .popular-item h3 {
            margin: 0 0 7px;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: 18px;
            line-height: 1.3;
        }

        .popular-item h3 a:hover {
            color: var(--primary);
        }

        .category-section {
            margin-top: 68px;
            padding-top: 34px;
            border-top: 1px solid var(--border);
        }

        .category-feature-grid {
            display: grid;
            grid-template-columns:
                minmax(0, 1.15fr)
                minmax(0, 0.85fr);
            gap: 26px;
        }

        .category-main {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: white;
            box-shadow: var(--shadow);
        }

        .category-main-image {
            width: 100%;
            height: 330px;
            object-fit: cover;
        }

        .category-main-content {
            padding: 26px;
        }

        .category-main h3 {
            margin: 8px 0 12px;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: clamp(25px, 4vw, 35px);
            line-height: 1.2;
        }

        .category-main h3 a:hover {
            color: var(--primary-dark);
        }

        .category-side-list {
            display: grid;
            gap: 16px;
        }

        .category-side-item {
            display: grid;
            grid-template-columns:
                130px minmax(0, 1fr);
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: white;
        }

        .category-side-image,
        .category-side-item .image-placeholder {
            width: 130px;
            height: 125px;
            min-height: 125px;
            object-fit: cover;
        }

        .category-side-content {
            padding: 14px;
        }

        .category-side-content h3 {
            margin: 5px 0 8px;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: 17px;
            line-height: 1.25;
        }

        .category-side-content h3 a:hover {
            color: var(--primary-dark);
        }

        @media (max-width: 850px) {
            .popular-layout,
            .category-feature-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {
            .popular-section {
                margin-inline: -12px;
                border-radius: 0;
            }

            .category-side-item {
                grid-template-columns:
                    105px minmax(0, 1fr);
            }

            .category-side-image,
            .category-side-item .image-placeholder {
                width: 105px;
            }
        }
    </style>
@endpush

@section('content')
    @if($featuredArticles->isNotEmpty())
        @php
            $mainArticle = $featuredArticles->first();

            $secondaryArticles = $featuredArticles
                ->skip(1)
                ->take(4);
        @endphp

        <section>
            <div class="section-heading">
                <h1 class="section-title">
                    À la une
                </h1>

                <span class="meta">
                    Les informations essentielles du moment
                </span>
            </div>

            <div class="hero-grid">
                <article class="hero-card">
                    <x-article-image
                        :article="$mainArticle"
                        class="hero-main-image"
                        :eager="true"
                        :width="1200"
                        :height="675"
                    />

                    <div class="hero-overlay">
                        <div class="hero-content">
                            <a
                                href="{{ route(
                                    'categories.show',
                                    $mainArticle->category->slug
                                ) }}"
                                class="category"
                            >
                                {{ $mainArticle->category->name }}
                            </a>

                            <h2 class="hero-title">
                                <a
                                    href="{{ route(
                                        'articles.show',
                                        $mainArticle->slug
                                    ) }}"
                                >
                                    {{ $mainArticle->title }}
                                </a>
                            </h2>

                            @if($mainArticle->excerpt)
                                <p class="hero-excerpt">
                                    {{ $mainArticle->excerpt }}
                                </p>
                            @endif

                            <div
                                class="meta"
                                style="color: #ddd5c5;"
                            >
                                Par {{ $mainArticle->author->name }}
                                ·
                                {{ $mainArticle
                                    ->published_at
                                    ?->translatedFormat('d F Y') }}
                            </div>
                        </div>
                    </div>
                </article>

                <div class="side-stories">
                    @foreach($secondaryArticles as $article)
                        <article class="side-story">
                            <x-article-image
                                :article="$article"
                                :width="360"
                                :height="240"
                            />

                            <div class="side-story-content">
                                <a
                                    href="{{ route(
                                        'categories.show',
                                        $article->category->slug
                                    ) }}"
                                    class="category"
                                >
                                    {{ $article->category->name }}
                                </a>

                                <h3>
                                    <a
                                        href="{{ route(
                                            'articles.show',
                                            $article->slug
                                        ) }}"
                                    >
                                        {{ $article->title }}
                                    </a>
                                </h3>

                                <div class="meta">
                                    {{ $article
                                        ->published_at
                                        ?->translatedFormat('d M Y') }}
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section>
        <div class="section-heading">
            <h2 class="section-title">
                Dernières nouvelles
            </h2>

            <a
                href="{{ route('search') }}"
                class="section-link"
            >
                Explorer toute l’actualité →
            </a>
        </div>

        @if($latestArticles->isEmpty())
            <div class="empty-state">
                <h2>
                    Notre rédaction se prépare
                </h2>

                <p>
                    Les premiers articles seront bientôt disponibles.
                </p>
            </div>
        @else
            <div class="articles-grid">
                @foreach($latestArticles as $article)
                    <article class="article-card">
                        <x-article-image
                            :article="$article"
                            :width="600"
                            :height="400"
                        />

                        <div class="article-card-content">
                            <a
                                href="{{ route(
                                    'categories.show',
                                    $article->category->slug
                                ) }}"
                                class="category"
                            >
                                {{ $article->category->name }}
                            </a>

                            <h3>
                                <a
                                    href="{{ route(
                                        'articles.show',
                                        $article->slug
                                    ) }}"
                                >
                                    {{ $article->title }}
                                </a>
                            </h3>

                            @if($article->excerpt)
                                <p>
                                    {{ \Illuminate\Support\Str::limit(
                                        $article->excerpt,
                                        135
                                    ) }}
                                </p>
                            @endif

                            <div class="meta">
                                Par {{ $article->author->name }}
                                ·
                                {{ $article
                                    ->published_at
                                    ?->diffForHumans() }}
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top: 38px;">
                {{ $latestArticles->links() }}
            </div>
        @endif
    </section>

    @if($popularArticles->isNotEmpty())
        @php
            $mainPopular = $popularArticles->first();

            $otherPopular = $popularArticles
                ->skip(1);
        @endphp

        <section class="popular-section">
            <div class="section-heading">
                <h2 class="section-title">
                    Les plus lus
                </h2>

                <span class="section-link">
                    Tendances des 30 derniers jours
                </span>
            </div>

            <div class="popular-layout">
                <article class="popular-main">
                    <x-article-image
                        :article="$mainPopular"
                        class="popular-main-image"
                        :width="900"
                        :height="560"
                    />

                    <div class="popular-main-content">
                        <a
                            href="{{ route(
                                'categories.show',
                                $mainPopular->category->slug
                            ) }}"
                            class="category"
                        >
                            {{ $mainPopular->category->name }}
                        </a>

                        <h3>
                            <a
                                href="{{ route(
                                    'articles.show',
                                    $mainPopular->slug
                                ) }}"
                            >
                                {{ $mainPopular->title }}
                            </a>
                        </h3>

                        <div class="meta">
                            {{ number_format(
                                $mainPopular->views_count
                            ) }}
                            lecture(s)
                        </div>
                    </div>
                </article>

                <div class="popular-list">
                    @foreach($otherPopular as $popular)
                        <article class="popular-item">
                            <span class="popular-number">
                                {{ str_pad(
                                    $loop->iteration + 1,
                                    2,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                            </span>

                            <div>
                                <h3>
                                    <a
                                        href="{{ route(
                                            'articles.show',
                                            $popular->slug
                                        ) }}"
                                    >
                                        {{ $popular->title }}
                                    </a>
                                </h3>

                                <div class="meta">
                                    {{ number_format(
                                        $popular->views_count
                                    ) }}
                                    lecture(s)
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @foreach($categorySections as $categorySection)
        @php
            $categoryMainArticle =
                $categorySection->articles->first();

            $categorySideArticles =
                $categorySection->articles
                    ->skip(1)
                    ->take(3);
        @endphp

        <section class="category-section">
            <div class="section-heading">
                <h2 class="section-title">
                    {{ $categorySection->name }}
                </h2>

                <a
                    href="{{ route(
                        'categories.show',
                        $categorySection->slug
                    ) }}"
                    class="section-link"
                >
                    Toute la rubrique →
                </a>
            </div>

            <div class="category-feature-grid">
                <article class="category-main">
                    <x-article-image
                        :article="$categoryMainArticle"
                        class="category-main-image"
                        :width="900"
                        :height="560"
                    />

                    <div class="category-main-content">
                        <span class="category">
                            {{ $categorySection->name }}
                        </span>

                        <h3>
                            <a
                                href="{{ route(
                                    'articles.show',
                                    $categoryMainArticle->slug
                                ) }}"
                            >
                                {{ $categoryMainArticle->title }}
                            </a>
                        </h3>

                        @if($categoryMainArticle->excerpt)
                            <p>
                                {{ \Illuminate\Support\Str::limit(
                                    $categoryMainArticle->excerpt,
                                    180
                                ) }}
                            </p>
                        @endif

                        <div class="meta">
                            Par
                            {{ $categoryMainArticle->author->name }}
                            ·
                            {{ $categoryMainArticle
                                ->published_at
                                ?->translatedFormat('d F Y') }}
                        </div>
                    </div>
                </article>

                <div class="category-side-list">
                    @foreach($categorySideArticles as $article)
                        <article class="category-side-item">
                            <x-article-image
                                :article="$article"
                                class="category-side-image"
                                :width="360"
                                :height="260"
                            />

                            <div class="category-side-content">
                                <span class="category">
                                    {{ $article->category->name }}
                                </span>

                                <h3>
                                    <a
                                        href="{{ route(
                                            'articles.show',
                                            $article->slug
                                        ) }}"
                                    >
                                        {{ $article->title }}
                                    </a>
                                </h3>

                                <div class="meta">
                                    {{ $article
                                        ->published_at
                                        ?->translatedFormat('d M Y') }}
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach
@endsection