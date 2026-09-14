@extends('front.layouts.app')

@section(
    'title',
    $author->name.' — Lambi News'
)

@section(
    'meta_description',
    $author->bio
        ?: 'Découvrez les articles de '.$author->name.' sur Lambi News.'
)

@push('styles')
    <style>
        .author-profile {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr);
            gap: 32px;
            margin-bottom: 55px;
            padding: clamp(25px, 5vw, 42px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: white;
            box-shadow: var(--shadow);
        }

        .author-profile-photo,
        .author-profile-placeholder {
            width: 180px;
            height: 180px;
            border: 4px solid var(--primary);
            border-radius: 50%;
            object-fit: cover;
        }

        .author-profile-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--black);
            background: var(--primary);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 70px;
            font-weight: 800;
        }

        .author-profile h1 {
            margin: 0 0 5px;
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(34px, 5vw, 50px);
            line-height: 1.1;
        }

        .author-job {
            color: var(--primary-dark);
            font-weight: 800;
        }

        .author-bio {
            max-width: 720px;
            color: var(--muted);
            font-size: 17px;
        }

        .author-socials {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 18px;
        }

        .author-social {
            display: inline-flex;
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .author-social:hover {
            color: var(--black);
            border-color: var(--primary);
            background: var(--primary);
        }

        @media (max-width: 650px) {
            .author-profile {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .author-profile-photo,
            .author-profile-placeholder {
                margin-inline: auto;
            }

            .author-socials {
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <section class="author-profile">
        <div>
            @if($author->photo)
                <img
                    src="{{ asset('storage/'.$author->photo) }}"
                    alt="{{ $author->name }}"
                    class="author-profile-photo"
                >
            @else
                <div class="author-profile-placeholder">
                    {{ strtoupper(
                        mb_substr($author->name, 0, 1)
                    ) }}
                </div>
            @endif
        </div>

        <div>
            <h1>
                {{ $author->name }}
            </h1>

            @if($author->job_title)
                <div class="author-job">
                    {{ $author->job_title }}
                </div>
            @endif

            @if($author->bio)
                <p class="author-bio">
                    {{ $author->bio }}
                </p>
            @endif

            <div class="author-socials">
                @if($author->facebook_url)
                    <a
                        href="{{ $author->facebook_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="author-social"
                    >
                        Facebook
                    </a>
                @endif

                @if($author->instagram_url)
                    <a
                        href="{{ $author->instagram_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="author-social"
                    >
                        Instagram
                    </a>
                @endif

                @if($author->tiktok_url)
                    <a
                        href="{{ $author->tiktok_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="author-social"
                    >
                        TikTok
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section>
        <div class="section-heading">
            <h2 class="section-title">
                Articles de {{ $author->name }}
            </h2>

            <span class="meta">
                {{ $articles->total() }} article(s)
            </span>
        </div>

        @if($articles->isEmpty())
            <div class="empty-state">
                <h2>
                    Aucun article publié
                </h2>
            </div>
        @else
            <div class="articles-grid">
                @foreach($articles as $article)
                    <article class="article-card">
                        @if($article->featured_image)
                            <img
                                src="{{ asset(
                                    'storage/'.$article->featured_image
                                ) }}"
                                alt="{{ $article->title }}"
                            >
                        @else
                            <div class="image-placeholder"></div>
                        @endif

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
                                        130
                                    ) }}
                                </p>
                            @endif

                            <div class="meta">
                                {{ $article->published_at?->translatedFormat(
                                    'd F Y'
                                ) }}
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top: 38px;">
                {{ $articles->links() }}
            </div>
        @endif
    </section>
@endsection