@extends('front.layouts.app')

@section('title', 'Partis et groupements politiques agréés — Élections 2026 — Lambi News')
@section('meta_description', 'Les structures politiques agréées par le CEP pour les élections 2026, leur numéro de campagne et leurs réponses au questionnaire de Lambi News.')

@push('styles')
    @include('front.elections._styles')
    <style>
        .elp__rules { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px; margin: 22px 0; }
        .elp__rule { padding: 14px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); font-size: .88rem; }
        .elp__rule b { display: block; margin-bottom: 4px; }
        .elp__progress { display: flex; align-items: center; gap: 12px; margin: 10px 0 22px; font-size: .9rem; }
        .elp__bar { flex: 1; max-width: 320px; height: 8px; border-radius: 999px; background: var(--border); overflow: hidden; }
        .elp__bar span { display: block; height: 100%; background: var(--primary); }
        .elp__search { display: flex; gap: 8px; max-width: 520px; margin-bottom: 18px; }
        .elp__search input { flex: 1; min-width: 0; padding: 12px 14px; border-radius: 999px; border: 1px solid var(--border); font: inherit; }
        .elp__search button { padding: 12px 20px; border-radius: 999px; border: 0; background: var(--black); color: var(--primary); font: inherit; font-weight: 800; cursor: pointer; }
        .elp__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
        .elp__party { display: grid; grid-template-columns: 54px minmax(0,1fr); gap: 12px; align-items: center; padding: 12px; color: inherit; transition: border-color .15s; }
        .elp__party:hover { border-color: var(--primary); }
        .elp__num {
            width: 54px; height: 54px; border-radius: 12px; display: grid; place-items: center; overflow: hidden;
            color: #fff; font-weight: 800; font-size: 1.15rem;
        }
        .elp__num img { width: 100%; height: 100%; object-fit: contain; background: #fff; }
        .elp__party strong { display: block; font-size: .95rem; line-height: 1.3; }
        .elp__party small { display: block; margin-top: 3px; font-size: .76rem; color: var(--muted); }
        .elp__answered { color: #166534 !important; font-weight: 700; }
        @media (max-width: 760px) { .elp__rules { grid-template-columns: minmax(0,1fr); } }
    </style>
@endpush

@section('content')
<section class="el">
    <div class="el__wrap">
        @include('front.elections._nav')

        <p class="el__eyebrow">Élections 2026</p>
        <h1 class="el__title">Partis et positions</h1>
        <p class="el__lead">
            Les {{ $total }} structures politiques agréées par le CEP, avec le numéro de campagne
            qui leur a été attribué. Nous leur avons soumis {{ $questions->count() }} questions ;
            leurs réponses sont publiées telles quelles.
        </p>

        <div class="elp__rules">
            <div class="elp__rule"><b>Aucun classement</b>Nous ne notons, ne classons et ne recommandons aucune structure.</div>
            <div class="elp__rule"><b>Réponses intactes</b>Chaque réponse est publiée telle que reçue, avec sa date.</div>
            <div class="elp__rule"><b>Le silence se voit</b>Une question restée sans réponse est affichée comme telle.</div>
        </div>

        <div class="elp__progress">
            <strong>{{ $answered }} / {{ $total }}</strong> ont répondu au questionnaire
            <span class="elp__bar"><span style="width: {{ $total ? round($answered / $total * 100) : 0 }}%"></span></span>
        </div>

        <form class="elp__search" method="GET" action="{{ route('elections.parties') }}" role="search">
            <input type="search" name="q" value="{{ $term }}" placeholder="Nom, sigle ou numéro" aria-label="Rechercher une structure">
            <button type="submit">Chercher</button>
        </form>

        @if($parties->isEmpty())
            <div class="el__empty">Aucune structure ne correspond à « {{ $term }} ».</div>
        @else
            <div class="elp__grid">
                @foreach($parties as $party)
                    <a class="el__card elp__party" href="{{ route('elections.party', $party->slug) }}">
                        <span class="elp__num" style="background: {{ $party->badgeColor() }}">
                            @if($party->logo)
                                <img src="{{ asset('storage/'.$party->logo) }}" alt="" loading="lazy">
                            @else
                                {{ $party->campaign_number ?? '—' }}
                            @endif
                        </span>
                        <span>
                            <strong>{{ $party->name }}</strong>
                            <small>
                                {{ $party->acronym }}@if($party->campaign_number) · n° {{ $party->campaign_number }}@endif
                            </small>
                            <small class="{{ $party->answers_count ? 'elp__answered' : '' }}">
                                {{ $party->answers_count ? '✓ '.$party->answers_count.' réponse(s)' : 'Pas encore de réponse' }}
                            </small>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif

        <p class="el__source" style="margin-top:20px">
            Source : Conseil électoral provisoire,
            <a href="https://cephaiti.ht/wp-content/uploads/2026/08/STRUCTURES-NUMEROS.pdf" target="_blank" rel="noopener">liste des structures politiques agréées et numéros attribués</a>
            (août 2026).
        </p>
    </div>
</section>
@endsection
