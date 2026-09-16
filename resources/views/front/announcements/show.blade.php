@extends('front.layouts.app')

@section('title', $announcement->title.' — Annonces — Lambi News')

@section(
    'meta_description',
    \Illuminate\Support\Str::limit(strip_tags($announcement->body), 160)
)

@push('styles')
    @include('front.announcements._styles')
@endpush

@section('content')
<section class="anons">
    <div class="anons__wrap" style="max-width:760px">

        <header class="anons__head" style="text-align:left">
            <span class="anons__tag">{{ $announcement->getTypeLabel() }}</span>
            <h1 class="anons__title">{{ $announcement->title }}</h1>
        </header>

        <div class="anons__body">
            @if($announcement->photo)
                <img
                    src="{{ asset('storage/'.$announcement->photo) }}"
                    alt="{{ $announcement->title }}"
                    class="anons__photo"
                    loading="lazy"
                >
            @endif

            {{ $announcement->body }}

            <p class="anons__meta">
                Annonce publiée le
                {{ $announcement->published_at?->translatedFormat('d F Y') }}.
                Contenu fourni et payé par le demandeur.
            </p>
        </div>

        <p style="margin-top:24px">
            <a href="{{ route('announcements.index') }}">← Toutes les annonces</a>
        </p>

    </div>
</section>
@endsection
