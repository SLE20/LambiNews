@extends('front.layouts.app')

@section('title', 'Annoncer sur Lambi News — dossier de presse')

@section(
    'meta_description',
    'Audience, emplacements publicitaires et formats disponibles sur '
    .'Lambi News. Chiffres issus de nos statistiques de lecture.'
)

@push('styles')
<style>
    .kit { padding: 48px 16px 80px; }
    .kit__wrap { max-width: 980px; margin: 0 auto; }
    .kit__head { text-align: center; margin-bottom: 40px; }
    .kit__eyebrow {
        text-transform: uppercase; letter-spacing: .14em; font-size: .78rem;
        font-weight: 700; color: var(--primary-dark); margin: 0 0 8px;
    }
    .kit__title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.9rem, 5vw, 2.8rem); margin: 0 0 14px; line-height: 1.15;
    }
    .kit__lead { color: var(--muted); max-width: 60ch; margin: 0 auto; }

    .kit__grid {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 16px; margin-bottom: 40px;
    }
    .kit__stat {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 22px 18px; text-align: center;
    }
    .kit__value {
        font-size: clamp(1.5rem, 3.4vw, 2.1rem); font-weight: 700;
        color: var(--black); line-height: 1.1;
    }
    .kit__caption {
        margin-top: 6px; font-size: .82rem; color: var(--muted);
        text-transform: uppercase; letter-spacing: .06em;
    }

    .kit__section { margin-bottom: 40px; }
    .kit__h2 {
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.5rem; margin: 0 0 16px;
        padding-bottom: 10px; border-bottom: 2px solid var(--primary);
        display: inline-block;
    }
    .kit__table {
        width: 100%; border-collapse: collapse;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); overflow: hidden;
    }
    .kit__table th, .kit__table td {
        padding: 12px 16px; text-align: left; font-size: .93rem;
        border-bottom: 1px solid var(--border);
    }
    .kit__table th { background: var(--background); font-weight: 600; }
    .kit__table tr:last-child td { border-bottom: 0; }
    .kit__bar {
        height: 8px; border-radius: 999px; background: var(--primary);
        min-width: 4px; display: inline-block; vertical-align: middle;
    }
    .kit__cta {
        text-align: center; background: var(--black); color: #fff;
        border-radius: var(--radius); padding: 34px 24px;
    }
    .kit__cta h2 {
        font-family: "Playfair Display", Georgia, serif;
        margin: 0 0 10px; font-size: 1.6rem;
    }
    .kit__cta p { color: rgba(255,255,255,.75); margin: 0 0 20px; }
    .kit__btn {
        display: inline-block; padding: 13px 28px; border-radius: 999px;
        background: var(--primary); color: var(--black); font-weight: 700;
    }
    .kit__note {
        margin-top: 26px; font-size: .82rem; color: var(--muted);
        text-align: center;
    }
    @media (max-width: 760px) {
        .kit__grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')
<section class="kit">
    <div class="kit__wrap">

        <header class="kit__head">
            <p class="kit__eyebrow">Dossier de presse</p>
            <h1 class="kit__title">Annoncer sur Lambi News</h1>
            <p class="kit__lead">
                Voici notre audience réelle, mesurée sur le site. Ces chiffres
                proviennent de nos statistiques de lecture, pas d’une estimation.
            </p>
        </header>

        <div class="kit__grid">
            <div class="kit__stat">
                <div class="kit__value">{{ number_format($stats['views_30'], 0, ',', ' ') }}</div>
                <div class="kit__caption">Pages vues / 30 j</div>
            </div>
            <div class="kit__stat">
                <div class="kit__value">{{ number_format($stats['visitors_30'], 0, ',', ' ') }}</div>
                <div class="kit__caption">Lecteurs uniques / 30 j</div>
            </div>
            <div class="kit__stat">
                <div class="kit__value">{{ number_format($stats['articles_30'], 0, ',', ' ') }}</div>
                <div class="kit__caption">Articles / 30 j</div>
            </div>
            <div class="kit__stat">
                <div class="kit__value">{{ number_format($stats['subscribers'], 0, ',', ' ') }}</div>
                <div class="kit__caption">Abonnés infolettre</div>
            </div>
        </div>

        {{-- ---------------- Emplacements ---------------- --}}
        <div class="kit__section">
            <h2 class="kit__h2">Emplacements disponibles</h2>

            <table class="kit__table">
                <thead>
                    <tr>
                        <th>Emplacement</th>
                        <th>Pages concernées</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($positions as $key => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>
                                @switch($key)
                                    @case('header')
                                    @case('footer')
                                        Toutes les pages
                                        @break
                                    @case('home_top')
                                        Page d’accueil
                                        @break
                                    @case('in_article')
                                    @case('below_article')
                                    @case('sidebar_top')
                                    @case('sidebar_bottom')
                                        Pages d’articles
                                        @break
                                    @default
                                        —
                                @endswitch
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ---------------- Appareils ---------------- --}}
        @if(! empty($stats['devices']))
            <div class="kit__section">
                <h2 class="kit__h2">Sur quel appareil nos lecteurs nous lisent</h2>

                @php($deviceTotal = max(1, array_sum($stats['devices'])))

                <table class="kit__table">
                    <tbody>
                        @foreach($stats['devices'] as $device => $count)
                            <tr>
                                <td style="width: 28%">{{ ucfirst($device) }}</td>
                                <td>
                                    <span
                                        class="kit__bar"
                                        style="width: {{ round(($count / $deviceTotal) * 100) }}%"
                                    ></span>
                                    <span style="margin-left:10px">
                                        {{ round(($count / $deviceTotal) * 100) }} %
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ---------------- Provenance ---------------- --}}
        @if(! empty($stats['sources']))
            <div class="kit__section">
                <h2 class="kit__h2">D’où viennent nos lecteurs</h2>

                <table class="kit__table">
                    <tbody>
                        @foreach($stats['sources'] as $host => $count)
                            <tr>
                                <td>{{ $host }}</td>
                                <td style="text-align:right">
                                    {{ number_format($count, 0, ',', ' ') }} visites
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ---------------- Rubriques ---------------- --}}
        @if(! empty($stats['top_categories']))
            <div class="kit__section">
                <h2 class="kit__h2">Nos rubriques les plus fournies</h2>

                <table class="kit__table">
                    <tbody>
                        @foreach($stats['top_categories'] as $name => $count)
                            <tr>
                                <td>{{ $name }}</td>
                                <td style="text-align:right">{{ $count }} articles</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="kit__cta">
            <h2>Parlons de votre campagne</h2>
            <p>
                Formats, durée et tarifs : écrivez-nous, nous répondons sous
                48 heures.
            </p>
            <a href="{{ route('contact.create') }}" class="kit__btn">
                Nous contacter
            </a>
        </div>

        <p class="kit__note">
            Chiffres arrêtés au
            {{ $stats['generated_at']->translatedFormat('d F Y à H\hi') }}.
            Chaque annonceur reçoit un lien privé pour suivre ses propres
            impressions et clics en temps réel.
        </p>

    </div>
</section>
@endsection
