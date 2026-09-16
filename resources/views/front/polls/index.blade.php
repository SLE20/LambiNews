@extends('front.layouts.app')

@section('title', 'Sondages des lecteurs — Lambi News')
@section('meta_description', 'Les sondages d’opinion proposés aux lecteurs de Lambi News.')

@push('styles')
<style>
    .polls { padding: 44px 16px 80px; }
    .polls__wrap { max-width: 900px; margin: 0 auto; }
    .polls__title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.8rem, 5vw, 2.5rem); margin: 0 0 10px; text-align: center;
    }
    .polls__lead { color: var(--muted); text-align: center; margin: 0 0 32px; }
    .polls__list { display: grid; gap: 18px; }
    .polls__empty {
        padding: 40px; text-align: center; color: var(--muted);
        background: var(--surface); border: 1px dashed var(--border);
        border-radius: var(--radius);
    }

    /* ---------- Carte d'aperçu ---------- */
    .pcard {
        display: block;
        padding: 22px 24px;
        overflow: hidden;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        color: inherit;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .pcard:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow);
        transform: translateY(-2px);
    }
    .pcard__cover {
        display: block;
        margin: -22px -24px 16px;
        aspect-ratio: 16 / 5;
        overflow: hidden;
        background: var(--border);
    }
    .pcard__cover img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .pcard__head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; margin-bottom: 10px;
    }
    .pcard__eyebrow {
        font-size: .7rem; font-weight: 700; letter-spacing: .14em;
        text-transform: uppercase; color: var(--primary-dark);
    }
    .pcard__state {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: .72rem; font-weight: 700; color: #16a34a;
    }
    .pcard__state i {
        width: 7px; height: 7px; border-radius: 50%; background: #16a34a;
    }
    .pcard__state--closed { color: var(--muted); }
    .pcard__title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.15rem, 2.6vw, 1.45rem);
        line-height: 1.32; margin: 0 0 8px;
    }
    .pcard:hover .pcard__title { color: var(--primary-dark); }
    .pcard__desc { color: var(--muted); font-size: .9rem; margin: 0 0 16px; }

    .pcard__options {
        display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
        margin-bottom: 16px;
    }
    .pcard__option {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 6px 14px 6px 6px;
        border: 1px solid var(--border); border-radius: 999px;
        background: var(--background); font-size: .85rem;
    }
    .pcard__avatar {
        width: 30px; height: 30px; border-radius: 50%; flex: none;
        background: var(--o-color, var(--primary));
        display: grid; place-items: center; overflow: hidden;
        color: #fff; font-weight: 700; font-size: .82rem;
    }
    .pcard__avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pcard__more { font-size: .84rem; color: var(--muted); }

    .pcard__foot {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; flex-wrap: wrap;
        padding-top: 14px; border-top: 1px solid var(--border);
    }
    .pcard__meta { font-size: .82rem; color: var(--muted); }
    .pcard__cta { font-size: .88rem; font-weight: 700; color: var(--primary-dark); }

    @media (max-width: 560px) {
        .pcard { padding: 18px 16px; }
        .pcard__cover { margin: -18px -16px 14px; aspect-ratio: 16 / 7; }
        .pcard__optlabel { max-width: 92px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
                    @include('front.polls._card', ['poll' => $poll])
                @endforeach
            </div>

            <div style="margin-top:26px">{{ $polls->links() }}</div>
        @endif
    </div>
</section>
@endsection
