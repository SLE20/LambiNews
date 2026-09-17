@extends('front.layouts.app')

@section('title', $party->displayName().' — Élections 2026 — Lambi News')
@section('meta_description', 'Numéro de campagne et réponses de '.$party->displayName().' au questionnaire de Lambi News pour les élections 2026.')

@push('styles')
    @include('front.elections._styles')
    <style>
        .elq__head { display: flex; gap: 18px; align-items: center; margin-bottom: 20px; }
        .elq__logo {
            width: 92px; height: 92px; border-radius: 20px; flex: none; overflow: hidden;
            display: grid; place-items: center; color: #fff; font-size: 2rem; font-weight: 800;
        }
        .elq__logo img { width: 100%; height: 100%; object-fit: contain; background: #fff; }
        .elq__facts { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .elq__facts span { padding: 5px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--border); font-size: .84rem; }
        .elq__qa { display: grid; gap: 12px; }
        .elq__theme { font-size: .7rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--primary-dark); }
        .elq__question { margin: 4px 0 10px; font-size: 1.02rem; }
        .elq__answer { margin: 0; line-height: 1.7; white-space: pre-line; }
        .elq__silence { margin: 0; color: var(--muted); font-style: italic; }
        .elq__item--silent { background: var(--background); }
        @media (max-width: 560px) { .elq__head { align-items: flex-start; } .elq__logo { width: 66px; height: 66px; font-size: 1.4rem; } }
    </style>
@endpush

@section('content')
<section class="el">
    <div class="el__wrap" style="max-width:860px">
        @include('front.elections._nav')

        <div class="elq__head">
            <span class="elq__logo" style="background: {{ $party->badgeColor() }}">
                @if($party->logo)
                    <img src="{{ asset('storage/'.$party->logo) }}" alt="Logo {{ $party->acronym ?: $party->name }}">
                @else
                    {{ $party->campaign_number ?? mb_substr($party->acronym ?: $party->name, 0, 2) }}
                @endif
            </span>
            <div>
                <p class="el__eyebrow">Structure politique agréée</p>
                <h1 class="el__title" style="margin:0;font-size:clamp(1.5rem,4vw,2.2rem)">{{ $party->name }}</h1>
                <div class="elq__facts">
                    @if($party->acronym)<span>Sigle : <strong>{{ $party->acronym }}</strong></span>@endif
                    @if($party->campaign_number)<span>Numéro de campagne : <strong>{{ $party->campaign_number }}</strong></span>@endif
                    @if($party->leader)<span>{{ $party->leader }}</span>@endif
                    @if($party->website)<span><a href="{{ $party->website }}" target="_blank" rel="noopener nofollow">Site web ↗</a></span>@endif
                </div>
            </div>
        </div>

        @if($party->description)
            <p class="el__lead">{{ $party->description }}</p>
        @endif

        <div class="el__section" style="margin-top:28px">
            <h2 class="el__h2">Réponses au questionnaire</h2>

            <div class="elq__qa">
                @foreach($questions as $question)
                    @php($answer = $answers->get($question->id))
                    <article class="el__card {{ $answer ? '' : 'elq__item--silent' }}">
                        <span class="elq__theme">{{ $question->theme }}</span>
                        <h3 class="elq__question">{{ $question->question }}</h3>
                        @if($answer)
                            <p class="elq__answer">{{ $answer->answer }}</p>
                            <p class="el__source">
                                @if($answer->received_on) Réponse reçue le {{ $answer->received_on->translatedFormat('j F Y') }} @endif
                                @if($answer->source_url) · <a href="{{ $answer->source_url }}" target="_blank" rel="noopener nofollow">document source</a> @endif
                            </p>
                        @else
                            <p class="elq__silence">Pas de réponse à ce jour.</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>

        <p class="el__source" style="margin-top:22px">
            Réponses publiées sans modification de fond. Lambi News ne note, ne classe et ne recommande
            aucune structure. Vous représentez ce parti ? Écrivez-nous via la
            <a href="{{ route('contact.create') }}">page contact</a>.
        </p>

        <p style="margin-top:18px"><a class="el__more" href="{{ route('elections.parties') }}">← Toutes les structures</a></p>
    </div>
</section>
@endsection
