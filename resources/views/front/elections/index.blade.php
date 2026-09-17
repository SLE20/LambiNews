@extends('front.layouts.app')

@section('title', 'Élections 2026 — calendrier, centres de vote et partis — Lambi News')
@section('meta_description', 'Le guide des élections 2026 en Haïti : calendrier du CEP, centres d’inscription et de vote, structures politiques et leurs positions, acteurs du processus.')

@push('styles')
    @include('front.elections._styles')
    <style>
        .elh__hero {
            display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 20px; align-items: stretch;
        }
        .elh__intro {
            padding: 30px; border-radius: var(--radius); color: #fff;
            background: radial-gradient(circle at 85% 20%, rgba(216,169,34,.28), transparent 45%), linear-gradient(135deg, #0b0b0b, #22201b);
        }
        .elh__intro .el__title { color: #fff; }
        .elh__intro .el__lead { color: rgba(255,255,255,.72); }
        .elh__actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
        .elh__btn {
            display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 999px;
            font-weight: 800; background: var(--primary); color: var(--black);
        }
        .elh__btn--ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,.35); }
        .elh__stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 10px; margin-top: 26px; }
        .elh__stat strong { display: block; font-size: 1.7rem; color: var(--primary); line-height: 1.1; }
        .elh__stat span { font-size: .78rem; color: rgba(255,255,255,.65); }

        .elh__next { display: flex; flex-direction: column; gap: 10px; border-top: 4px solid var(--primary); }
        .elh__next-label { font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
        .elh__next-title { margin: 0; font-family: "Playfair Display", Georgia, serif; font-size: 1.35rem; line-height: 1.25; }
        .elh__countdown { display: flex; align-items: baseline; gap: 8px; }
        .elh__countdown strong { font-size: 2.6rem; line-height: 1; }

        .elh__timeline { display: grid; gap: 0; }
        .elh__step { display: grid; grid-template-columns: 120px 1fr auto; gap: 14px; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .elh__step:last-child { border-bottom: 0; }
        .elh__date { font-size: .82rem; font-weight: 700; color: var(--muted); }

        .elh__oni { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px; }
        .elh__oni div { padding: 16px; border-radius: 12px; background: var(--background); text-align: center; }
        .elh__oni strong { display: block; font-size: 1.5rem; }
        .elh__oni span { font-size: .8rem; color: var(--muted); }

        .elh__depts { display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: 8px; }
        .elh__dept { padding: 12px; border-radius: 12px; background: var(--background); color: inherit; }
        .elh__dept:hover { outline: 2px solid var(--primary); }
        .elh__dept strong { display: block; font-size: 1.2rem; }
        .elh__dept span { font-size: .78rem; color: var(--muted); }

        .elh__frame { width: 100%; height: 640px; border: 0; border-radius: 12px; background: var(--background); }

        .elh__articles { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 16px; }
        .elh__article { color: inherit; }
        .elh__article img { width: 100%; aspect-ratio: 16/9; object-fit: cover; border-radius: 10px; background: var(--border); }
        .elh__article strong { display: block; margin-top: 8px; line-height: 1.35; }

        @media (max-width: 900px) {
            .elh__hero, .elh__articles { grid-template-columns: minmax(0,1fr); }
            .elh__depts { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .elh__step { grid-template-columns: minmax(0,1fr) auto; }
            .elh__date { grid-column: 1 / -1; }
            .elh__frame { height: 520px; }
        }
        @media (max-width: 520px) {
            .elh__oni, .elh__stats { grid-template-columns: minmax(0,1fr); }
        }
    </style>
@endpush

@section('content')
<section class="el">
    <div class="el__wrap">
        @include('front.elections._nav')

        <div class="elh__hero">
            <div class="elh__intro">
                <p class="el__eyebrow" style="color:var(--primary)">Élections 2026-2027</p>
                <h1 class="el__title">Comprendre le vote, pas à pas</h1>
                <p class="el__lead">
                    Les dates du CEP, les centres où s’inscrire et voter, les structures
                    politiques en lice et ce qu’elles proposent. Chaque information porte
                    sa source et sa date de vérification.
                </p>

                <div class="elh__actions">
                    <a class="elh__btn" href="{{ route('elections.where') }}">📍 Où voter ?</a>
                    <a class="elh__btn elh__btn--ghost" href="{{ route('elections.calendar') }}">📅 Le calendrier</a>
                    <a class="elh__btn elh__btn--ghost" href="{{ route('polls.index') }}">🗳 Donner mon avis</a>
                </div>

                <div class="elh__stats">
                    @php($centerTotal = array_sum(array_column($centers, 'count')))
                    <div class="elh__stat">
                        @if($centerTotal > 0)
                            <strong>{{ number_format($centerTotal, 0, ',', ' ') }}</strong>
                            <span>centres d’inscription et de vote</span>
                        @else
                            <strong>{{ $questionCount }}</strong>
                            <span>questions posées aux partis</span>
                        @endif
                    </div>
                    <div class="elh__stat">
                        <strong>{{ $parties }}</strong>
                        <span>structures politiques agréées</span>
                    </div>
                    <div class="elh__stat">
                        <strong>10</strong>
                        <span>départements</span>
                    </div>
                </div>
            </div>

            <div class="el__card elh__next">
                <span class="elh__next-label">Prochaine échéance</span>
                @if($next)
                    @php($days = (int) now()->startOfDay()->diffInDays($next->starts_on, false))
                    <h2 class="elh__next-title">{{ $next->title }}</h2>
                    <div>
                        <span class="el__badge el__badge--{{ $next->status }}">{{ $next->statusLabel() }}</span>
                        <span style="margin-left:6px;font-weight:700">{{ $next->periodLabel() }}</span>
                    </div>
                    @if($days > 0)
                        <div class="elh__countdown"><strong>{{ $days }}</strong><span>jour(s) restant(s)</span></div>
                    @elseif($next->ends_on && $next->ends_on->isFuture())
                        <div class="elh__countdown">
                            <strong>{{ (int) now()->startOfDay()->diffInDays($next->ends_on, false) }}</strong>
                            <span>jour(s) avant la fin</span>
                        </div>
                    @endif
                    <p class="el__source">
                        Source : {{ $next->source_label ?: 'CEP' }}
                        @if($next->verified_on) · vérifié le {{ $next->verified_on->translatedFormat('j F Y') }} @endif
                    </p>
                    <a class="el__more" href="{{ route('elections.calendar') }}">Voir le calendrier complet →</a>
                @else
                    <p style="color:var(--muted);margin:0">
                        Le calendrier est en cours de vérification par la rédaction.
                    </p>
                    <a class="el__more" href="https://cephaiti.ht/" target="_blank" rel="noopener">Consulter le CEP ↗</a>
                @endif
            </div>
        </div>

        {{-- ------------ Calendrier (aperçu) ------------ --}}
        @if($upcoming->isNotEmpty())
            <div class="el__section">
                <h2 class="el__h2">Les grandes étapes</h2>
                <div class="el__card elh__timeline">
                    @foreach($upcoming->take(6) as $event)
                        <div class="elh__step">
                            <span class="elh__date">{{ $event->periodLabel() }}</span>
                            <strong>{{ $event->title }}</strong>
                            <span class="el__badge el__badge--{{ $event->status }}">{{ $event->statusLabel() }}</span>
                        </div>
                    @endforeach
                </div>
                <p style="margin-top:10px"><a class="el__more" href="{{ route('elections.calendar') }}">Toutes les échéances et leur historique →</a></p>
            </div>
        @endif

        {{-- ------------ Chiffres officiels ------------ --}}
        @if($oni)
            <div class="el__section">
                <h2 class="el__h2">Chiffres officiels de l’ONI</h2>
                <div class="el__card">
                    <div class="elh__oni">
                        <div><strong>{{ number_format($oni['women'], 0, ',', ' ') }}</strong><span>femmes</span></div>
                        <div><strong>{{ number_format($oni['men'], 0, ',', ' ') }}</strong><span>hommes</span></div>
                        <div><strong>{{ number_format($oni['total'], 0, ',', ' ') }}</strong><span>total</span></div>
                    </div>
                    <p class="el__source">
                        Source : Office national d’identification
                        @if($oni['checked']) · chiffres consultés le {{ \Illuminate\Support\Carbon::parse($oni['checked'])->translatedFormat('j F Y') }} @endif
                        @if($oni['url']) · <a href="{{ $oni['url'] }}" target="_blank" rel="noopener">vérifier auprès de l’ONI</a> @endif
                    </p>
                </div>
            </div>
        @endif

        {{-- ------------ Centres par département ------------ --}}
        @if(array_sum(array_column($centers, 'count')) > 0)
            <div class="el__section">
                <h2 class="el__h2">Centres d’inscription et de vote</h2>
                <div class="elh__depts">
                    @foreach($centers as $key => $dept)
                        <a class="elh__dept" href="{{ route('elections.where', ['departement' => $key]) }}">
                            <strong>{{ $dept['count'] }}</strong>
                            <span>{{ $dept['label'] }}</span>
                        </a>
                    @endforeach
                </div>
                <p style="margin-top:10px"><a class="el__more" href="{{ route('elections.where') }}">Trouver mon centre →</a></p>
            </div>
        @endif

        {{-- ------------ Tableau du CEP ------------ --}}
        @if($cepStats)
            <div class="el__section">
                <h2 class="el__h2">Avancement de l’inscription (CEP)</h2>
                <div class="el__card" style="padding:10px">
                    <iframe class="elh__frame" src="{{ $cepStats }}" loading="lazy"
                            title="Statistiques de l’inscription des électeurs publiées par le CEP"
                            referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
                <p class="el__source">
                    Tableau publié par le Conseil électoral provisoire ·
                    <a href="{{ $cepStats }}" target="_blank" rel="noopener">ouvrir en plein écran ↗</a>
                </p>
            </div>
        @endif

        {{-- ------------ Acteurs ------------ --}}
        @if($actors->isNotEmpty())
            <div class="el__section">
                <h2 class="el__h2">Qui fait quoi ?</h2>
                <div class="el__grid">
                    @foreach($actors as $actor)
                        <a class="el__card el__actor" href="{{ route('elections.actor', $actor->slug) }}">
                            <span class="el__actor-icon" aria-hidden="true">{{ $actor->icon }}</span>
                            <strong>{{ $actor->name }}</strong>
                            <span>{{ $actor->summary }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ------------ Articles ------------ --}}
        @if($articles->isNotEmpty())
            <div class="el__section">
                <h2 class="el__h2">Nos derniers articles sur les élections</h2>
                <div class="elh__articles">
                    @foreach($articles as $article)
                        <a class="elh__article" href="{{ route('articles.show', $article) }}">
                            @if($article->featured_image)
                                <img src="{{ $article->thumbUrl(400) }}" alt="" loading="lazy">
                            @endif
                            <strong>{{ $article->title }}</strong>
                            <span class="el__source">{{ optional($article->published_at)->translatedFormat('j F Y') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
