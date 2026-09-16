@extends('front.layouts.app')

@section('title', 'Rapport de campagne — '.$ad->name)

{{-- Page privée : elle ne doit jamais se retrouver dans un index. --}}
@section('meta_description', 'Rapport de campagne publicitaire.')

@push('styles')
<style>
    .rap { padding: 48px 16px 80px; }
    .rap__wrap { max-width: 880px; margin: 0 auto; }
    .rap__head { margin-bottom: 28px; }
    .rap__eyebrow {
        text-transform: uppercase; letter-spacing: .14em; font-size: .78rem;
        font-weight: 700; color: var(--primary-dark); margin: 0 0 8px;
    }
    .rap__title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.6rem, 4vw, 2.2rem); margin: 0 0 8px;
    }
    .rap__meta { color: var(--muted); font-size: .93rem; margin: 0; }
    .rap__grid {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 16px; margin: 28px 0 36px;
    }
    .rap__stat {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 22px; text-align: center;
    }
    .rap__value { font-size: 2rem; font-weight: 700; line-height: 1.1; }
    .rap__caption {
        margin-top: 6px; font-size: .8rem; color: var(--muted);
        text-transform: uppercase; letter-spacing: .06em;
    }
    .rap__h2 {
        font-family: "Playfair Display", Georgia, serif; font-size: 1.35rem;
        margin: 0 0 16px; padding-bottom: 10px;
        border-bottom: 2px solid var(--primary); display: inline-block;
    }
    .rap__table {
        width: 100%; border-collapse: collapse; background: var(--surface);
        border: 1px solid var(--border); border-radius: var(--radius);
        overflow: hidden;
    }
    .rap__table th, .rap__table td {
        padding: 10px 14px; font-size: .92rem; text-align: left;
        border-bottom: 1px solid var(--border);
    }
    .rap__table th { background: var(--background); font-weight: 600; }
    .rap__table tr:last-child td { border-bottom: 0; }
    .rap__bar {
        display: inline-block; height: 9px; border-radius: 999px;
        background: var(--primary); min-width: 3px; vertical-align: middle;
    }
    .rap__empty {
        padding: 30px; text-align: center; color: var(--muted);
        background: var(--surface); border: 1px dashed var(--border);
        border-radius: var(--radius);
    }
    .rap__note { margin-top: 26px; font-size: .82rem; color: var(--muted); }
    @media (max-width: 640px) { .rap__grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<section class="rap">
    <div class="rap__wrap">

        <header class="rap__head">
            <p class="rap__eyebrow">Rapport de campagne</p>
            <h1 class="rap__title">{{ $ad->name }}</h1>
            <p class="rap__meta">
                @if($ad->client_name){{ $ad->client_name }} — @endif
                {{ $ad->getPositionLabel() }} — {{ $ad->getScheduleLabel() }}
            </p>
        </header>

        <div class="rap__grid">
            <div class="rap__stat">
                <div class="rap__value">
                    {{ number_format($ad->impressions_count, 0, ',', ' ') }}
                </div>
                <div class="rap__caption">Affichages</div>
            </div>
            <div class="rap__stat">
                <div class="rap__value">
                    {{ number_format($ad->clicks_count, 0, ',', ' ') }}
                </div>
                <div class="rap__caption">Clics</div>
            </div>
            <div class="rap__stat">
                <div class="rap__value">
                    {{ number_format($ad->ctr, 2, ',', ' ') }} %
                </div>
                <div class="rap__caption">Taux de clics</div>
            </div>
        </div>

        <h2 class="rap__h2">Jour par jour</h2>

        @if($daily->isEmpty())
            <p class="rap__empty">
                Aucun affichage enregistré pour l’instant. Les chiffres
                apparaissent dès que la campagne démarre.
            </p>
        @else
            <table class="rap__table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Affichages</th>
                        <th>Clics</th>
                        <th style="width: 34%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($daily->reverse() as $day)
                        <tr>
                            <td>{{ $day->date->format('d/m/Y') }}</td>
                            <td>{{ number_format($day->impressions, 0, ',', ' ') }}</td>
                            <td>{{ number_format($day->clicks, 0, ',', ' ') }}</td>
                            <td>
                                <span
                                    class="rap__bar"
                                    style="width: {{ round(($day->impressions / $maxDaily) * 100) }}%"
                                ></span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <p class="rap__note">
            Un affichage est compté une seule fois par page consultée. Les
            clics sont comptés au moment où le lecteur est redirigé vers
            votre site. Page mise à jour en continu — conservez ce lien.
        </p>

    </div>
</section>
@endsection
