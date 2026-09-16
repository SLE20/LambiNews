{{--
    Accroche de sondage dans la colonne d'un article.

    Volontairement réduite : un titre, un seul choix en aperçu et un
    lien. On ne vote pas depuis l'article — le lecteur arrive sur la page
    du sondage, où les règles et les résultats sont visibles.
--}}
@php
    $teaser = $poll->options->first();
    $others = max(0, $poll->options->count() - 1);
@endphp

<a href="{{ route('polls.show', $poll->slug) }}" class="pteaser">
    <span class="pteaser__eyebrow">Sondage des lecteurs</span>

    <span class="pteaser__title">{{ $poll->question }}</span>

    @if($teaser)
        <span class="pteaser__option">
            <span
                class="pteaser__avatar"
                style="--o-color: {{ $teaser->displayColor(0) }}"
            >
                @if($teaser->image)
                    <img src="{{ asset('storage/'.$teaser->image) }}" alt="" loading="lazy">
                @else
                    {{ \Illuminate\Support\Str::of($teaser->label)->substr(0, 1)->upper() }}
                @endif
            </span>

            <span class="pteaser__label">
                {{ \Illuminate\Support\Str::limit($teaser->label, 24) }}
                @if($others > 0)
                    <small>+{{ $others }} lòt chwa</small>
                @endif
            </span>
        </span>
    @endif

    <span class="pteaser__cta">Ale vote →</span>
</a>

<style>
    .pteaser {
        display: block;
        padding: 18px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--surface);
        color: inherit;
        transition: border-color .15s, box-shadow .15s;
    }
    .pteaser:hover { border-color: var(--primary); box-shadow: var(--shadow); }
    .pteaser__eyebrow {
        display: block; margin-bottom: 8px;
        font-size: .68rem; font-weight: 700; letter-spacing: .13em;
        text-transform: uppercase; color: var(--primary-dark);
    }
    .pteaser__title {
        display: block; margin-bottom: 14px;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.02rem; line-height: 1.35;
    }
    .pteaser__option {
        display: flex; align-items: center; gap: 10px;
        padding: 8px; margin-bottom: 14px;
        border: 1px solid var(--border); border-radius: 10px;
        background: var(--background);
    }
    .pteaser__avatar {
        width: 36px; height: 36px; border-radius: 50%; flex: none;
        background: var(--o-color, var(--primary));
        display: grid; place-items: center; overflow: hidden;
        color: #fff; font-weight: 700;
    }
    .pteaser__avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pteaser__label { font-size: .9rem; font-weight: 600; line-height: 1.3; }
    .pteaser__label small {
        display: block; font-weight: 400; color: var(--muted); font-size: .78rem;
    }
    .pteaser__cta {
        display: inline-block; font-size: .86rem; font-weight: 700;
        color: var(--primary-dark);
    }
</style>
