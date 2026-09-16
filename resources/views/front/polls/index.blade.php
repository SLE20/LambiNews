@extends('front.layouts.app')

@section('title', 'Sondages des lecteurs — Lambi News')
@section('meta_description', 'Les sondages d’opinion proposés aux lecteurs de Lambi News.')

@push('styles')
    @include('front.polls._styles')
    <style>
        .polls { padding: 44px 16px 80px; }
        .polls__wrap { max-width: 760px; margin: 0 auto; }
        .polls__title {
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(1.8rem, 5vw, 2.5rem); margin: 0 0 10px; text-align: center;
        }
        .polls__lead { color: var(--muted); text-align: center; margin: 0 0 30px; }
        .polls__list { display: grid; gap: 20px; }
        .polls__empty {
            padding: 40px; text-align: center; color: var(--muted);
            background: var(--surface); border: 1px dashed var(--border);
            border-radius: var(--radius);
        }
    </style>
@endpush

@section('content')
<section class="polls">
    <div class="polls__wrap">
        <h1 class="polls__title">Sondages des lecteurs</h1>
        <p class="polls__lead">
            Donnez votre avis sur les sujets qui font l’actualité.
        </p>

        @if($polls->isEmpty())
            <p class="polls__empty">Aucun sondage pour le moment.</p>
        @else
            <div class="polls__list">
                @foreach($polls as $poll)
                    @include('front.polls._poll', ['poll' => $poll, 'recorder' => $recorder])
                @endforeach
            </div>

            <div style="margin-top:26px">{{ $polls->links() }}</div>
        @endif
    </div>
</section>
@endsection
