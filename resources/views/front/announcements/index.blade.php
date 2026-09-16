@extends('front.layouts.app')

@section('title', 'Annonces — Lambi News')

@section(
    'meta_description',
    'Avis de décès, remerciements, félicitations et annonces '
    .'commerciales publiés dans Lambi News.'
)

@push('styles')
    @include('front.announcements._styles')
@endpush

@section('content')
<section class="anons">
    <div class="anons__wrap">

        <header class="anons__head">
            <p class="anons__eyebrow">Annonces</p>
            <h1 class="anons__title">Avis et annonces</h1>
            <p class="anons__lead">
                Avis de décès, remerciements, messes de souvenir,
                félicitations et annonces commerciales.
            </p>
        </header>

        <nav class="anons__filters">
            <a
                href="{{ route('announcements.index') }}"
                class="anons__filter{{ $activeType === '' ? ' is-active' : '' }}"
            >Toutes</a>

            @foreach($catalogue as $key => $row)
                <a
                    href="{{ route('announcements.index', ['type' => $key]) }}"
                    class="anons__filter{{ $activeType === $key ? ' is-active' : '' }}"
                >{{ $row['label'] }}</a>
            @endforeach
        </nav>

        @if($announcements->isEmpty())
            <p class="anons__empty">
                Aucune annonce publiée pour le moment.
            </p>
        @else
            <div class="anons__list">
                @foreach($announcements as $announcement)
                    <a
                        href="{{ route('announcements.show', $announcement->slug) }}"
                        class="anons__card"
                    >
                        <span class="anons__tag">{{ $announcement->getTypeLabel() }}</span>

                        <h2>{{ $announcement->title }}</h2>

                        <p class="anons__excerpt">
                            {{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 180) }}
                        </p>

                        <p class="anons__date">
                            Publiée le
                            {{ $announcement->published_at?->translatedFormat('d F Y') }}
                        </p>
                    </a>
                @endforeach
            </div>

            <div style="margin-top:28px">
                {{ $announcements->links() }}
            </div>
        @endif

        <div class="anons__cta">
            <h2>Publier une annonce</h2>
            <p>
                Votre annonce paraît {{ \App\Models\Announcement::DISPLAY_DAYS }} jours
                sur Lambi News, lue en Haïti et dans la diaspora.
            </p>
            <a href="{{ route('announcements.create') }}" class="anons__btn">
                Commencer
            </a>
        </div>

    </div>
</section>
@endsection
