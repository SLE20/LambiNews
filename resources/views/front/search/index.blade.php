@extends('front.layouts.app')

@section('title', $term ? 'Recherche : '.$term : 'Recherche')

@section('content')
    <h1 class="section-title">Recherche</h1>

    @if($term === '')
        <p>Saisissez un mot ou une expression pour rechercher un article.</p>
    @elseif($articles->isEmpty())
        <p>Aucun résultat trouvé pour « {{ $term }} ».</p>
    @else
        <p>
            {{ $articles->total() }} résultat(s) pour
            « <strong>{{ $term }}</strong> »
        </p>

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