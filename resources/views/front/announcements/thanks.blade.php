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
            <h1 class="anons__title">Mèsi, nou resevwa anons ou an</h1>

            <p style="color:var(--muted)">
                Referans: <strong>{{ $announcement->reference }}</strong><br>
                Montan peye: <strong>{{ $announcement->getFormattedAmount() }}</strong>
            </p>

            <p>
                Redaksyon an ap li anons ou an anvan li parèt sou sit la —
                sa pran jeneralman mwens pase 24 èdtan. N ap voye yon mesaj
                nan <strong>{{ $announcement->requester_email }}</strong>
                lè li an liy.
            </p>

            <p style="margin-top:22px">
                <a href="{{ route('announcements.index') }}" class="anons__btn">
                    Wè lòt anons yo
                </a>
            </p>
        </div>
    </div>
</section>
@endsection
