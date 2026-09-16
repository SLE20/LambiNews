{{--
    Aperçu d'un sondage dans la liste.

    On ne rejoue pas tout le sondage ici : un titre, deux choix en
    aperçu et le nombre de participations suffisent à donner envie. La
    carte entière est cliquable, pour ne pas obliger le lecteur à viser
    le titre.
--}}
@php
    $preview   = $poll->options->take(2);
    $remaining = max(0, $poll->options->count() - $preview->count());
    $total     = $poll->validVotes();
    $closed    = $poll->isClosed();
    $daysLeft  = $poll->daysLeft();
@endphp

<a href="{{ route('polls.show', $poll->slug) }}" class="pcard">

    @if($poll->hero_image)
        {{-- Couverture : ce qui donne son allure à la carte. --}}
        <span class="pcard__cover">
            <img
                src="{{ asset('storage/'.$poll->hero_image) }}"
                alt=""
                loading="lazy"
            >
        </span>
    @endif

    <div class="pcard__head">
        <span class="pcard__eyebrow">Sondage des lecteurs</span>

        @if($closed)
            <span class="pcard__state pcard__state--closed">Fèmen</span>
        @else
            <span class="pcard__state">
                <i></i> An kou
            </span>
        @endif
    </div>

    <h2 class="pcard__title">{{ $poll->question }}</h2>

    @if($poll->description)
        <p class="pcard__desc">
            {{ \Illuminate\Support\Str::limit($poll->description, 130) }}
        </p>
    @endif

    {{-- Aperçu : deux choix, puis le nombre de choix restants. --}}
    <div class="pcard__options">
        @foreach($preview as $option)
            <span class="pcard__option">
                <span
                    class="pcard__avatar"
                    style="--o-color: {{ $option->displayColor($loop->index) }}"
                >
                    @if($option->image)
                        <img
                            src="{{ asset('storage/'.$option->image) }}"
                            alt=""
                            loading="lazy"
                        >
                    @else
                        {{ \Illuminate\Support\Str::of($option->label)->substr(0, 1)->upper() }}
                    @endif
                </span>

                <span class="pcard__optlabel">
                    {{ \Illuminate\Support\Str::limit($option->label, 22) }}
                </span>
            </span>
        @endforeach

        @if($remaining > 0)
            <span class="pcard__more">+{{ $remaining }} lòt chwa</span>
        @endif
    </div>

    <div class="pcard__foot">
        <span class="pcard__meta">
            {{ number_format($total, 0, ',', ' ') }} patisipasyon{{ $total > 1 ? 's' : '' }}

            @if(! $closed && $daysLeft !== null)
                · rete {{ $daysLeft }} jou
            @endif

            @if($poll->isPaid())
                · {{ $poll->formattedPrice() }} pa vòt
            @endif
        </span>

        <span class="pcard__cta">
            {{ $closed ? 'Wè rezilta yo' : 'Vote kounye a' }} →
        </span>
    </div>
</a>
