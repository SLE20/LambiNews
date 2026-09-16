@extends('front.layouts.app')

@section('title', 'Kanpay finansman — Lambi News')
@section('meta_description', 'Sipòte kanpay finansman Lambi News yo ak MonCash oswa PayPal.')

@push('styles')
    @include('front.fundraisers._styles')
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
                    <a href="{{ route('fundraisers.show', $fundraiser->slug) }}" class="fr__card">
                        @if($fundraiser->cover_image)
                            <span class="fr__cover">
                                <img src="{{ asset('storage/'.$fundraiser->cover_image) }}" alt="" loading="lazy">
                            </span>
                        @endif

                        <div class="fr__body">
                            <span class="fr__badge {{ $fundraiser->isOpen() ? '' : 'fr__badge--closed' }}">
                                {{ $fundraiser->isOpen() ? 'An kou' : 'Fèmen' }}
                            </span>

                            <h2 class="fr__cardtitle">{{ $fundraiser->title }}</h2>

                            @if($fundraiser->summary)
                                <p class="fr__summary">
                                    {{ \Illuminate\Support\Str::limit($fundraiser->summary, 110) }}
                                </p>
                            @endif

                            <div class="fr__bar">
                                <span class="fr__fill" style="width: {{ $fundraiser->percent() }}%"></span>
                            </div>

                            <div class="fr__amounts">
                                <span class="fr__raised">{{ $fundraiser->formatted((float) $fundraiser->raised_amount) }}</span>
                                <span class="fr__goal">sou {{ $fundraiser->formatted((float) $fundraiser->goal_amount) }}</span>
                            </div>

                            <p class="fr__meta">
                                {{ $fundraiser->contributions_count }} kontribisyon
                                @if($fundraiser->daysLeft() !== null && $fundraiser->isOpen())
                                    · rete {{ $fundraiser->daysLeft() }} jou
                                @endif
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div style="margin-top:28px">{{ $fundraisers->links() }}</div>
        @endif
    </div>
</section>
@endsection
