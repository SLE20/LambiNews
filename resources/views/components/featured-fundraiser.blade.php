{{--
    Appel à soutenir une campagne, placé là où le lecteur est déjà
    engagé : colonne de l'accueil, et au-dessus du sondage dans un
    article. Même carte que sur /campagnes, pour qu'il la reconnaisse.

    On ne contribue pas d'ici : le clic mène à la campagne, où le
    montant, le bénéficiaire et l'histoire sont visibles avant de payer.
--}}
@include('front.fundraisers._card', ['fundraiser' => $fundraiser, 'context' => 'promo'])

@once
    @push('styles')
        @include('front.fundraisers._card_styles')
    @endpush
@endonce
