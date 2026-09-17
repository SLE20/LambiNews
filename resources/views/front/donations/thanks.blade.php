@extends('front.layouts.app')

@section('title', 'Merci pour votre soutien — Lambi News')

@section('meta_description', 'Merci pour votre don à Lambi News.')

@push('styles')
<style>
    .mesi { padding: 64px 16px 88px; }
    .mesi__wrap {
        max-width: 620px; margin: 0 auto; text-align: center;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); box-shadow: var(--shadow); padding: 40px 28px;
    }
    .mesi__mark {
        width: 68px; height: 68px; margin: 0 auto 20px; border-radius: 50%;
        display: grid; place-items: center;
        background: var(--primary); color: var(--black);
        font-size: 2rem; font-weight: 700;
    }
    .mesi__title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.7rem, 4.5vw, 2.4rem); margin: 0 0 12px;
    }
    .mesi__amount {
        font-size: 1.5rem; font-weight: 700; color: var(--primary-dark);
        margin: 0 0 18px;
    }
    .mesi__ref {
        display: inline-block; margin: 6px 0 22px; padding: 8px 16px;
        border-radius: 999px; background: var(--background);
        border: 1px solid var(--border);
        font-family: ui-monospace, Menlo, Consolas, monospace; font-size: .92rem;
    }
    .mesi__text { color: var(--muted); margin: 0 0 26px; }
    .mesi__back {
        display: inline-block; padding: 13px 26px; border-radius: 999px;
        background: var(--black); color: #fff; font-weight: 600;
    }
    .mesi__back:hover { background: var(--black-light); }
</style>
@endpush

@section('content')
<section class="mesi">
    <div class="mesi__wrap">
        <div class="mesi__mark" aria-hidden="true">✓</div>

        <h1 class="mesi__title">Merci infiniment{{ $donation->is_anonymous ? '' : ', '.$donation->donor_name }} !</h1>

        <p class="mesi__amount">
            {{ number_format((float) $donation->amount, 2) }} {{ $donation->currency }}
        </p>

        <p class="mesi__ref">Référence : {{ $donation->reference }}</p>

        <p class="mesi__text">
            Votre don a bien été reçu. Il finance directement le travail
            des journalistes de Lambi News.
            @if($donation->donor_email)
                Un reçu a été envoyé à {{ $donation->donor_email }}.
            @endif
        </p>

        <a href="{{ route('home') }}" class="mesi__back">Retour au site</a>
    </div>
</section>
@endsection
