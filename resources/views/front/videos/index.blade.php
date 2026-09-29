@extends('front.layouts.app')

@section('title', $current || $term ? 'Médias — Lambi News' : 'Médias — reportages et vidéos de Lambi News')
@section('meta_description', 'Reportages, entretiens et directs de Lambi News : toutes nos vidéos au même endroit.')

@push('styles')
    @include('front.videos._styles')
@endpush

@section('content')
<section class="vid">
    <div class="vid__wrap">

        {{-- Puces de rubriques : la barre de filtres de la page d'accueil vidéo. --}}
        <div class="vid__chips" role="navigation" aria-label="Rubriques vidéo">
            <a href="{{ route('videos.index') }}" class="vid__chip {{ $current === '' && $term === '' ? 'is-active' : '' }}">Tout</a>
            @foreach($chips as $chip)
                <a href="{{ route('videos.index', ['rubrique' => $chip->slug]) }}"
                   class="vid__chip {{ $current === $chip->slug ? 'is-active' : '' }}">
                    {{ $chip->name }} <small>{{ $chip->videos_count }}</small>
                </a>
            @endforeach

            <form class="vid__search" method="GET" action="{{ route('videos.index') }}" role="search">
                <input type="search" name="q" value="{{ $term }}" placeholder="Rechercher une vidéo" aria-label="Rechercher une vidéo">
            </form>
        </div>

        @if($featured)
            {{-- Vidéo à la une : grande image à gauche, texte à droite. --}}
            <a class="vid__hero" href="{{ route('videos.show', $featured->slug) }}">
                <span class="vid__hero-thumb">
                    @if($featured->thumbUrl())
                        <img src="{{ $featured->thumbUrl() }}" alt="" loading="eager">
                    @endif
                    <span class="vid__play" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path fill="currentColor" d="M8 5v14l11-7z"/></svg>
                    </span>
                    @if($featured->durationLabel())
                        <span class="vid__time">{{ $featured->durationLabel() }}</span>
                    @endif
                </span>

                <span class="vid__hero-body">
                    <span class="vid__eyebrow">À la une</span>
                    <strong class="vid__hero-title">{{ $featured->title }}</strong>
                    @if($featured->description)
                        <span class="vid__hero-desc">{{ \Illuminate\Support\Str::limit($featured->description, 220) }}</span>
                    @endif
                    <span class="vid__meta">
                        {{ $featured->category?->name ?: 'Lambi News' }} · {{ $featured->viewsLabel() }} · {{ $featured->agoLabel() }}
                    </span>
                    <span class="vid__cta">Regarder ▸</span>
                </span>
            </a>
        @endif

        @if($videos->isEmpty())
            <div class="vid__empty">
                @if($term !== '')
                    Aucune vidéo ne correspond à « {{ $term }} ».
                @else
                    Aucune vidéo pour le moment.
                @endif
            </div>
        @else
            <div class="vid__grid">
                @foreach($videos as $video)
                    <a class="vid__card" href="{{ route('videos.show', $video->slug) }}">
                        <span class="vid__thumb">
                            @if($video->thumbUrl())
                                <img src="{{ $video->thumbUrl() }}" alt="" loading="lazy">
                            @else
                                <span class="vid__noimg" aria-hidden="true">▶</span>
                            @endif
                            @if($video->durationLabel())
                                <span class="vid__time">{{ $video->durationLabel() }}</span>
                            @endif
                        </span>

                        <span class="vid__info">
                            <span class="vid__avatar" aria-hidden="true">LN</span>
                            <span class="vid__text">
                                <strong class="vid__title">{{ $video->title }}</strong>
                                <span class="vid__channel">
                                    Lambi News{{ $video->category ? ' · '.$video->category->name : '' }}
                                </span>
                                <span class="vid__meta">{{ $video->viewsLabel() }} · {{ $video->agoLabel() }}</span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="vid__pages">{{ $videos->links() }}</div>
        @endif
    </div>
</section>
@endsection
