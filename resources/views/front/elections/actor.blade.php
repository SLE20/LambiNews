@extends('front.layouts.app')

@section('title', $actor->name.' — rôle dans les élections — Lambi News')
@section('meta_description', $actor->summary)

@push('styles')
    @include('front.elections._styles')
    <style>
        .ela__head { display: flex; gap: 18px; align-items: center; margin-bottom: 22px; }
        .ela__icon { width: 76px; height: 76px; border-radius: 20px; display: grid; place-items: center; font-size: 2.3rem; background: var(--black); flex: none; }
        .ela__cols { display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: 16px; margin-top: 16px; }
        .ela__moments { counter-reset: m; list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
        .ela__moments li { counter-increment: m; display: flex; gap: 12px; align-items: center; }
        .ela__moments li::before {
            content: counter(m, decimal-leading-zero); flex: none; width: 38px; height: 38px; border-radius: 10px;
            display: grid; place-items: center; font-weight: 800; background: var(--primary); color: var(--black);
        }
        .ela__h3 { margin: 0 0 12px; font-size: 1rem; }
        .ela__others { display: flex; flex-wrap: wrap; gap: 8px; }
        .ela__others a { padding: 8px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--border); font-size: .86rem; font-weight: 700; color: inherit; }
        @media (max-width: 760px) { .ela__cols { grid-template-columns: minmax(0,1fr); } .ela__icon { width: 60px; height: 60px; font-size: 1.8rem; } }
    </style>
@endpush

@section('content')
<section class="el">
    <div class="el__wrap" style="max-width:900px">
        @include('front.elections._nav')

        <div class="ela__head">
            <span class="ela__icon" aria-hidden="true">{{ $actor->icon }}</span>
            <div>
                <p class="el__eyebrow">Acteur du processus électoral</p>
                <h1 class="el__title" style="margin:0">{{ $actor->name }}</h1>
            </div>
        </div>

        <p class="el__lead" style="font-size:1.1rem">{{ $actor->summary }}</p>

        <div class="el__card" style="margin-top:22px">
            <h2 class="ela__h3">Son rôle</h2>
            <p style="margin:0;line-height:1.7">{{ $actor->role }}</p>
        </div>

        <div class="ela__cols">
            @if($actor->lines('responsibilities'))
                <div class="el__card">
                    <h2 class="ela__h3">Responsabilités</h2>
                    <ul class="el__list">
                        @foreach($actor->lines('responsibilities') as $line)<li>{{ $line }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div style="display:grid;gap:16px;align-content:start">
                @if($actor->lines('moments'))
                    <div class="el__card">
                        <h2 class="ela__h3">Quand intervient-il ?</h2>
                        <ol class="ela__moments">
                            @foreach($actor->lines('moments') as $line)<li>{{ $line }}</li>@endforeach
                        </ol>
                    </div>
                @endif

                @if($actor->lines('watch_points'))
                    <div class="el__card" style="border-left:5px solid #dc2626">
                        <h2 class="ela__h3">Points de vigilance</h2>
                        <ul class="el__list">
                            @foreach($actor->lines('watch_points') as $line)<li>{{ $line }}</li>@endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        @if($actor->source_label)
            <p class="el__source">
                Référence :
                @if($actor->source_url)
                    <a href="{{ $actor->source_url }}" target="_blank" rel="noopener">{{ $actor->source_label }}</a>
                @else
                    {{ $actor->source_label }}
                @endif
                · Ces repères résument un rôle général ; les règles applicables sont celles publiées par les institutions compétentes.
            </p>
        @endif

        @if($others->isNotEmpty())
            <div class="el__section">
                <h2 class="el__h2">Les autres acteurs</h2>
                <div class="ela__others">
                    @foreach($others as $other)
                        <a href="{{ route('elections.actor', $other->slug) }}">{{ $other->icon }} {{ $other->name }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
