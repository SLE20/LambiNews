@extends('front.layouts.app')

@section('title', 'Calendrier électoral 2026 — Lambi News')
@section('meta_description', 'Toutes les échéances des élections 2026 en Haïti publiées par le CEP, avec leur source, leur date de vérification et l’historique des reports.')

@push('styles')
    @include('front.elections._styles')
    <style>
        .elc__line { position: relative; margin-left: 12px; padding-left: 30px; border-left: 3px solid var(--border); }
        .elc__item { position: relative; margin-bottom: 18px; }
        .elc__item::before {
            content: ""; position: absolute; left: -41px; top: 20px; width: 17px; height: 17px; border-radius: 50%;
            background: var(--surface); border: 3px solid var(--border);
        }
        .elc__item--done::before { background: #16a34a; border-color: #16a34a; }
        .elc__item--ongoing::before { background: var(--primary); border-color: var(--primary); box-shadow: 0 0 0 6px rgba(216,169,34,.22); }
        .elc__item--postponed::before { border-color: #dc2626; }
        .elc__item.is-next { border-color: var(--primary); box-shadow: 0 8px 24px rgba(216,169,34,.18); }
        .elc__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; }
        .elc__period { font-weight: 800; color: var(--primary-dark); font-size: .9rem; }
        .elc__title { margin: 6px 0 0; font-size: 1.12rem; }
        .elc__desc { margin: 8px 0 0; color: var(--muted); line-height: 1.6; }
        .elc__history { margin-top: 12px; padding: 10px 12px; border-radius: 10px; background: #fdf2f2; font-size: .84rem; }
        .elc__history summary { cursor: pointer; font-weight: 700; color: #9b1c1c; }
        .elc__history ul { margin: 8px 0 0; padding-left: 18px; }
        .elc__summary { display: flex; flex-wrap: wrap; gap: 10px; margin: 18px 0 30px; }
        .elc__pill { padding: 8px 14px; border-radius: 999px; background: var(--surface); border: 1px solid var(--border); font-size: .86rem; }
        .elc__pill strong { margin-right: 4px; }
    </style>
@endpush

@section('content')
<section class="el">
    <div class="el__wrap" style="max-width:900px">
        @include('front.elections._nav')

        <p class="el__eyebrow">Élections 2026</p>
        <h1 class="el__title">Calendrier électoral</h1>
        <p class="el__lead">
            Les échéances annoncées par le Conseil électoral provisoire. Quand une date
            change, l’ancienne reste affichée : le lecteur voit chaque report.
        </p>

        @if($events->isNotEmpty())
            <div class="elc__summary">
                @foreach(\App\Models\ElectionEvent::STATUSES as $key => $label)
                    @php($n = $events->where('status', $key)->count())
                    @if($n)
                        <span class="elc__pill"><strong>{{ $n }}</strong>{{ mb_strtolower($label) }}</span>
                    @endif
                @endforeach
                @if($postponed)
                    <span class="elc__pill" style="border-color:#f5c2c2"><strong>{{ $postponed }}</strong>modification(s) de date archivée(s)</span>
                @endif
            </div>

            <div class="elc__line">
                @foreach($events as $event)
                    <article class="el__card elc__item elc__item--{{ $event->status }} {{ $next && $next->id === $event->id ? 'is-next' : '' }}"
                             id="echeance-{{ $event->id }}">
                        <div class="elc__head">
                            <span class="elc__period">{{ $event->periodLabel() }}</span>
                            <span class="el__badge el__badge--{{ $event->status }}">
                                {{ $next && $next->id === $event->id ? 'Prochaine · ' : '' }}{{ $event->statusLabel() }}
                            </span>
                        </div>

                        <h2 class="elc__title">{{ $event->title }}</h2>

                        @if($event->description)
                            <p class="elc__desc">{{ $event->description }}</p>
                        @endif

                        <p class="el__source">
                            Source :
                            @if($event->source_url)
                                <a href="{{ $event->source_url }}" target="_blank" rel="noopener">{{ $event->source_label ?: 'CEP' }}</a>
                            @else
                                {{ $event->source_label ?: 'CEP' }}
                            @endif
                            @if($event->verified_on) · vérifié le {{ $event->verified_on->translatedFormat('j F Y') }} @endif
                        </p>

                        @if($event->revisions->isNotEmpty())
                            <details class="elc__history">
                                <summary>Modifiée {{ $event->revisions->count() }} fois — voir l’historique</summary>
                                <ul>
                                    @foreach($event->revisions as $rev)
                                        <li>{{ $rev->changed_at->translatedFormat('j F Y') }} — {{ $rev->summary() }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="el__empty" style="margin-top:26px">
                Le calendrier est en cours de vérification par la rédaction.
                En attendant, consultez <a href="https://cephaiti.ht/" target="_blank" rel="noopener">le site du CEP</a>.
            </div>
        @endif
    </div>
</section>
@endsection
