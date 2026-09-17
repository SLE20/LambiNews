@extends('front.layouts.app')

@section('title', 'Kanpay finansman — Lambi News')
@section('meta_description', 'Sipòte kanpay finansman Lambi News yo ak MonCash oswa PayPal.')

@push('styles')
    @include('front.fundraisers._styles')
    @include('front.fundraisers._card_styles')
@endpush

@section('content')
<section class="fr">
    <div class="fr__wrap">
        <header class="fr__head">
            <p class="fr__eyebrow">Finansman patisipatif</p>
            <h1 class="fr__title">Kanpay yo</h1>
            <p class="fr__lead">
                Sipòte yon pwojè ak MonCash oswa PayPal. Chak kontribisyon
                parèt an dirèk sou kanpay la.
            </p>
        </header>

        @if($fundraisers->isEmpty())
            <p class="fr__empty">Pa gen kanpay pou kounye a.</p>
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
