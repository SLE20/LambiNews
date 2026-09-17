{{--
    Appel à soutenir une campagne, placé là où le lecteur est déjà
    engagé : colonne de l'accueil, et au-dessus du sondage dans un
    article. Même forme aux deux endroits, pour qu'il la reconnaisse.

    On ne contribue pas d'ici : le clic mène à la campagne, où le
    montant, le bénéficiaire et l'histoire sont visibles avant de payer.
--}}
<a href="{{ route('fundraisers.show', $fundraiser->slug) }}" class="fdr">
    @if($fundraiser->cover_image || $fundraiser->photo)
        <span class="fdr__cover">
            @if($fundraiser->cover_image)
                <img src="{{ asset('storage/'.$fundraiser->cover_image) }}" alt="" loading="lazy">
            @endif

            @if($fundraiser->photo)
                <span class="fdr__photo">
                    <img src="{{ asset('storage/'.$fundraiser->photo) }}"
                         alt="{{ $fundraiser->beneficiary ?: $fundraiser->title }}" loading="lazy">
                </span>
            @endif
        </span>
    @endif

    <span class="fdr__body{{ $fundraiser->photo ? ' fdr__body--offset' : '' }}">
        <span class="fdr__eyebrow">💛 Kanpay finansman</span>

        <span class="fdr__title">{{ $fundraiser->title }}</span>

        @if($fundraiser->summary)
            <span class="fdr__summary">
                {{ \Illuminate\Support\Str::limit($fundraiser->summary, 95) }}
            </span>
        @endif

        <span class="fdr__bar">
            <span class="fdr__fill" style="width: {{ $fundraiser->percent() }}%"></span>
        </span>

        <span class="fdr__amounts">
            <b>{{ $fundraiser->formatted((float) $fundraiser->raised_amount) }}</b>
            <span>sou {{ $fundraiser->formatted((float) $fundraiser->goal_amount) }}</span>
        </span>

        <span class="fdr__meta">
            {{ number_format($fundraiser->rawPercent(), 0) }} % atenn ·
            {{ $fundraiser->contributions_count }} kontribisyon
            @if($fundraiser->daysLeft() !== null)
                · rete {{ $fundraiser->daysLeft() }} jou
            @endif
        </span>

        <span class="fdr__cta">Kontribye kounye a</span>
    </span>
</a>

@once
@push('styles')
<style>
    .fdr {
        display: block; overflow: hidden;
        border: 1px solid var(--border); border-radius: 12px;
        background: var(--surface); color: inherit;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .fdr:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow); transform: translateY(-2px);
    }
    .fdr__cover {
        display: block; position: relative;
        aspect-ratio: 16/9; background: var(--border);
    }
    .fdr__cover > img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .fdr__photo {
        position: absolute; left: 14px; bottom: -24px; z-index: 2;
        width: 56px; height: 56px; border-radius: 50%; overflow: hidden;
        border: 3px solid var(--surface); background: var(--surface);
        box-shadow: 0 2px 10px rgba(0,0,0,.18);
    }
    .fdr__photo img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .fdr__body { display: block; padding: 16px; }
    .fdr__body--offset { padding-top: 32px; }

    .fdr__eyebrow {
        display: block; margin-bottom: 7px;
        font-size: .68rem; font-weight: 700; letter-spacing: .12em;
        text-transform: uppercase; color: var(--primary-dark);
    }
    .fdr__title {
        display: block; margin-bottom: 7px;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.05rem; line-height: 1.32; font-weight: 700;
    }
    .fdr__summary {
        display: block; margin-bottom: 13px;
        font-size: .85rem; line-height: 1.5; color: var(--muted);
    }

    .fdr__bar {
        display: block; height: 9px; border-radius: 999px;
        background: var(--border); overflow: hidden;
    }
    .fdr__fill {
        display: block; height: 100%; border-radius: 999px;
        background: var(--primary);
    }
    .fdr__amounts {
        display: flex; justify-content: space-between; gap: 10px;
        margin-top: 9px; font-size: .84rem;
    }
    .fdr__amounts b { color: var(--primary-dark); font-weight: 800; }
    .fdr__amounts span { color: var(--muted); }
    .fdr__meta {
        display: block; margin-top: 8px;
        font-size: .76rem; color: var(--muted);
    }
    .fdr__cta {
        display: block; margin-top: 14px; padding: 11px;
        border-radius: 8px; text-align: center;
        background: var(--primary); color: var(--black);
        font-size: .88rem; font-weight: 800;
    }
    .fdr:hover .fdr__cta { background: var(--primary-dark); color: #fff; }
</style>
@endpush
@endonce
