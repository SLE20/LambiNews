{{--
    Carte de campagne — une seule et même forme partout : liste des
    campagnes, colonne de l'accueil, colonne d'un article. Le lecteur
    la reconnaît d'un coup d'œil où qu'il la croise.

    $fundraiser : la campagne.
    $context    : 'list' sur /kanpay (on montre l'état de la campagne),
                  'promo' ailleurs (on rappelle de quoi il s'agit).
--}}
@php($context = $context ?? 'promo')

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
        @if($context === 'list')
            <span class="fdr__badge{{ $fundraiser->isOpen() ? '' : ' fdr__badge--closed' }}">
                {{ $fundraiser->isOpen() ? 'An kou' : 'Fèmen' }}
            </span>
        @else
            <span class="fdr__eyebrow">💛 Kanpay finansman</span>
        @endif

        <span class="fdr__title">{{ $fundraiser->title }}</span>

        @if($fundraiser->summary)
            <span class="fdr__summary">
                {{ \Illuminate\Support\Str::limit($fundraiser->summary, 120) }}
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
            @if($fundraiser->daysLeft() !== null && $fundraiser->isOpen())
                · rete {{ $fundraiser->daysLeft() }} jou
            @endif
        </span>

        <span class="fdr__cta">
            {{ $fundraiser->isOpen() ? 'Kontribye kounye a' : 'Wè tout detay yo' }}
        </span>
    </span>
</a>
