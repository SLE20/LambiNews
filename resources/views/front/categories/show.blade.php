@extends('front.layouts.app')

@section('title', $category->name.' — Lambi News')

@section(
    'meta_description',
    $category->description ?: 'Découvrez les dernières nouvelles de la rubrique '.$category->name
)

@section('content')
    <header style="margin-bottom: 36px">
        <div class="category">Rubrique</div>
        <h1 class="section-title">{{ $category->name }}</h1>

        @if($category->description)
            <p class="article-excerpt">{{ $category->description }}</p>
        @endif
    </header>

    @if($articles->isEmpty())
        <p>Aucun article publié dans cette rubrique.</p>
    @else
        <div class="grid">
            @foreach($articles as $article)
                <article class="card">
                    @if($article->featured_image)
                        <img
                            class="card-image"
                            src="{{ asset('storage/'.$article->featured_image) }}"
                            alt="{{ $article->title }}"
                        >
                    @endif

                    <div class="card-content">
                        <div class="category">{{ $article->category->name }}</div>

                        <h3>
                            <a href="{{ route('articles.show', $article->slug) }}">
                                {{ $article->title }}
                            </a>
                        </h3>

                        @if($article->excerpt)
                            <p>{{ $article->excerpt }}</p>
                        @endif

                        <div class="meta">
                            Par {{ $article->author->name }}
                            · {{ $article->published_at?->translatedFormat('d F Y') }}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div style="margin-top: 32px">
            {{ $articles->links() }}
        </div>
    @endif
@endsection