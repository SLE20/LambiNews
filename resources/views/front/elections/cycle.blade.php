@extends('front.layouts.app')

@section('title', 'Le cycle électoral expliqué — Lambi News')
@section('meta_description', 'Avant, pendant et après le vote : les huit étapes d’une élection en Haïti, et les acteurs qui interviennent à chacune.')

@php
    $periods = [
        ['Avant le scrutin', 'Poser les règles, préparer le terrain, inscrire les électeurs et les candidats.', [
            ['Le cadre légal', 'Le décret électoral fixe les règles du jeu : postes à pourvoir, conditions pour être candidat, mode de scrutin, contentieux.', ['Gouvernement', 'CEP']],
            ['La planification', 'Le CEP arrête un calendrier et un budget, recrute et forme son personnel, prépare la logistique et la sécurité.', ['CEP', 'Gouvernement', 'Partenaires internationaux']],
            ['L’inscription des électeurs', 'Les citoyens s’inscrivent au registre électoral dans les centres ouverts à cet effet et obtiennent leur carte.', ['Électeurs', 'ONI', 'CEP']],
            ['Les candidatures', 'Les partis et groupements sont agréés, reçoivent un numéro de campagne et déposent leurs candidats, que le CEP examine avant de publier les listes.', ['Partis et groupements', 'Candidats', 'CEP']],
        ]],
        ['Le temps du vote', 'Convaincre, puis voter dans le calme et compter les voix sous le regard des observateurs.', [
            ['La campagne', 'Pendant la période officielle, les candidats présentent leurs programmes. Le code de conduite et les règles de financement s’appliquent.', ['Candidats', 'Partis', 'Médias']],
            ['Le jour du scrutin', 'Les électeurs votent dans leur centre. Mandataires des partis et observateurs accrédités suivent les opérations, puis le dépouillement.', ['Électeurs', 'CEP', 'Société civile']],
        ]],
        ['Après le vote', 'Compiler, proclamer, trancher les litiges, puis tirer les leçons pour le prochain cycle.', [
            ['Résultats et contestations', 'Les procès-verbaux sont compilés au centre de tabulation. Le CEP publie les résultats ; les recours sont jugés par les bureaux du contentieux.', ['CEP', 'Candidats']],
            ['Bilan et archives', 'Les rapports d’observation sont publiés, les documents archivés, et les recommandations nourrissent le cycle suivant.', ['Société civile', 'Partenaires internationaux', 'CEP']],
        ]],
    ];
    $step = 0;
@endphp

@push('styles')
    @include('front.elections._styles')
    <style>
        .ely__periods { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px; margin: 26px 0 8px; }
        .ely__period { padding: 16px; border-radius: 12px; background: var(--black); color: #fff; }
        .ely__period b { display: block; font-size: 1.6rem; color: var(--primary); }
        .ely__period span { font-size: .86rem; color: rgba(255,255,255,.7); }
        .ely__block { margin-top: 36px; }
        .ely__step { display: grid; grid-template-columns: 58px minmax(0,1fr); gap: 16px; margin-bottom: 14px; }
        .ely__num {
            width: 58px; height: 58px; border-radius: 16px; display: grid; place-items: center;
            font-size: 1.4rem; font-weight: 800; background: var(--primary); color: var(--black);
        }
        .ely__step h3 { margin: 0 0 6px; font-size: 1.08rem; }
        .ely__step p { margin: 0; color: var(--muted); line-height: 1.6; }
        .ely__who { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .ely__who span { padding: 3px 10px; border-radius: 999px; background: var(--background); font-size: .76rem; font-weight: 700; }
        .ely__continuous { margin-top: 30px; border-left: 5px solid var(--primary); }
        @media (max-width: 760px) { .ely__periods { grid-template-columns: minmax(0,1fr); } }
    </style>
@endpush

@section('content')
<section class="el">
    <div class="el__wrap" style="max-width:960px">
        @include('front.elections._nav')

        <p class="el__eyebrow">Comprendre</p>
        <h1 class="el__title">Le cycle électoral</h1>
        <p class="el__lead">
            Une élection ne se résume pas au jour du vote. Elle commence des mois plus
            tôt et se termine bien après la proclamation des résultats. Voici les
            grandes étapes, et qui intervient à chacune.
        </p>

        <div class="ely__periods">
            @foreach($periods as $i => [$name, $intro])
                <div class="ely__period"><b>0{{ $i + 1 }}</b><strong>{{ $name }}</strong><br><span>{{ $intro }}</span></div>
            @endforeach
        </div>

        @foreach($periods as [$name, $intro, $steps])
            <div class="ely__block">
                <h2 class="el__h2">{{ $name }}</h2>
                @foreach($steps as [$title, $text, $who])
                    @php($step++)
                    <div class="el__card ely__step">
                        <span class="ely__num">{{ $step }}</span>
                        <div>
                            <h3>{{ $title }}</h3>
                            <p>{{ $text }}</p>
                            <div class="ely__who">@foreach($who as $w)<span>{{ $w }}</span>@endforeach</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="el__card ely__continuous">
            <h2 class="el__h2" style="margin-bottom:8px">Tout au long du cycle</h2>
            <p style="margin:0;color:var(--muted);line-height:1.6">
                L’éducation civique, l’information des électeurs et le travail des médias
                ne s’arrêtent à aucune étape. Un électeur bien informé sait où s’inscrire,
                où voter et comment contester une irrégularité.
            </p>
        </div>

        @if($actors->isNotEmpty())
            <div class="el__section">
                <h2 class="el__h2">Les acteurs, un par un</h2>
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
    </div>
</section>
@endsection
