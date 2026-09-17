@extends('front.layouts.app')

@section('title', 'Annonce reçue — Lambi News')
@section('meta_description', 'Votre annonce a bien été reçue.')

@push('styles')
    @include('front.announcements._styles')
@endpush

@section('content')
<section class="anons">
    <div class="anons__wrap" style="max-width:620px; text-align:center">
        <div class="anons__body">
            <h1 class="anons__title">Merci, nous avons bien reçu votre annonce</h1>

            <p style="color:var(--muted)">
                Référence : <strong>{{ $announcement->reference }}</strong><br>
                Montant payé : <strong>{{ $announcement->getFormattedAmount() }}</strong>
            </p>

            <p>
                La rédaction relit votre annonce avant sa mise en ligne —
                cela prend généralement moins de 24 heures. Un message sera
                envoyé à <strong>{{ $announcement->requester_email }}</strong>
                dès sa publication.
            </p>

            <p style="margin-top:22px">
                <a href="{{ route('announcements.index') }}" class="anons__btn">
                    Voir les autres annonces
                </a>
            </p>
        </div>
    </div>
</section>
@endsection
