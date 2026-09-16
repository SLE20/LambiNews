@extends('front.layouts.app')

@section('title', 'Mèsi pou kontribisyon ou — Lambi News')
@section('meta_description', 'Mèsi pou sipò ou nan kanpay la.')

@push('styles')
    @include('front.fundraisers._styles')
@endpush

@section('content')
<section class="fr">
    <div class="fr__wrap" style="max-width:600px;text-align:center">
        <div class="fr__panel">
            <div style="width:64px;height:64px;margin:0 auto 18px;border-radius:50%;
                        background:var(--primary);color:var(--black);display:grid;
                        place-items:center;font-size:1.9rem;font-weight:700">✓</div>

            <h1 class="fr__title" style="font-size:1.7rem">Mèsi anpil !</h1>

            <p class="fr__big">{{ $contribution->getFormattedAmount() }}</p>
            <p class="fr__sub">
                pou « {{ $contribution->fundraiser->title }} »<br>
                peye ak {{ $contribution->getProviderLabel() }}
            </p>

            <p style="margin:18px 0;padding:8px 16px;display:inline-block;border-radius:999px;
                      background:var(--background);border:1px solid var(--border);
                      font-family:ui-monospace,monospace;font-size:.9rem">
                {{ $contribution->reference }}
            </p>

            <p>
                <a href="{{ route('fundraisers.show', $contribution->fundraiser->slug) }}"
                   class="fr__submit" style="display:inline-block;width:auto;padding:12px 26px;
                          text-decoration:none">Retounen sou kanpay la</a>
            </p>
        </div>
    </div>
</section>
@endsection
