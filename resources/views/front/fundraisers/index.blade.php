@extends('front.layouts.app')

@section('title', 'Campagnes de financement — Lambi News')
@section('meta_description', 'Soutenez les campagnes de financement de Lambi News avec MonCash ou PayPal.')

@push('styles')
    @include('front.fundraisers._styles')
    @include('front.fundraisers._card_styles')
@endpush

@section('content')
<section class="fr">
    <div class="fr__wrap">
        <header class="fr__head">
            <p class="fr__eyebrow">Financement participatif</p>
            <h1 class="fr__title">Nos campagnes</h1>
            <p class="fr__lead">
                Soutenez un projet avec MonCash ou PayPal. Chaque contribution
                s’affiche en direct sur la campagne.
            </p>
        </header>

        @if($fundraisers->isEmpty())
            <p class="fr__empty">Aucune campagne pour le moment.</p>
        @else
            <div class="fr__grid">
                @foreach($fundraisers as $fundraiser)
                    @include('front.fundraisers._card', [
                        'fundraiser' => $fundraiser,
                        'context'    => 'list',
                    ])
                @endforeach
            </div>

            <div style="margin-top:28px">{{ $fundraisers->links() }}</div>
        @endif
    </div>
</section>
@endsection
