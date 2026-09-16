{{--
    Bloc de sondage réutilisable : liste, page dédiée et encart d'article.

    $poll     : le sondage
    $recorder : PollVoteRecorder, pour savoir si ce visiteur a déjà voté
    $compact  : version réduite pour la colonne de droite
--}}
@php
    $hasVoted   = $recorder->alreadyVoted($poll);
    $chosenId   = $hasVoted ? $recorder->votedOptionId($poll) : null;
    $closed     = $poll->isClosed();
    $showResult = $hasVoted || $closed || ! $poll->hide_results_before_vote;
    $total      = $poll->validVotes();
@endphp

<div class="poll{{ ($compact ?? false) ? ' poll--compact' : '' }}">

    <p class="poll__eyebrow">Sondage des lecteurs</p>

    <h3 class="poll__question">
        <a href="{{ route('polls.show', $poll->slug) }}">{{ $poll->question }}</a>
    </h3>

    @if($poll->description)
        <p class="poll__desc">{{ $poll->description }}</p>
    @endif

    @if($hasVoted || $closed)
        <div class="poll__results">
            @foreach($poll->options as $option)
                @php($pct = $poll->percentFor($option))
                <div class="poll__row{{ $chosenId === $option->id ? ' is-mine' : '' }}">
                    <div class="poll__rowhead">
                        <span>{{ $option->label }}</span>
                        <strong>{{ number_format($pct, 1, ',', ' ') }} %</strong>
                    </div>
                    <div class="poll__track">
                        <span class="poll__fill" style="width: {{ $pct }}%"></span>
                    </div>
                    <span class="poll__count">
                        {{ number_format($option->votes_count, 0, ',', ' ') }} voix
                    </span>
                </div>
            @endforeach
        </div>
    @else
        <form
            method="POST"
            action="{{ route('polls.vote', $poll->slug) }}"
            class="poll__form"
        >
            @csrf
            <input type="hidden" name="opened_at" value="{{ time() }}">

            {{-- Leurre à robots : un lecteur ne le voit jamais. --}}
            <div class="poll__trap" aria-hidden="true">
                <label>Ne pas remplir
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </label>
            </div>

            @foreach($poll->options as $option)
                <label class="poll__choice">
                    <input
                        type="radio"
                        name="poll_option_id"
                        value="{{ $option->id }}"
                        required
                    >
                    <span>
                        {{ $option->label }}
                        @if($option->subtitle)
                            <small>{{ $option->subtitle }}</small>
                        @endif
                    </span>
                </label>
            @endforeach

            <button type="submit" class="poll__submit">Voter</button>
        </form>
    @endif

    <p class="poll__meta">
        {{ number_format($total, 0, ',', ' ') }} participation{{ $total > 1 ? 's' : '' }}
        @if($closed) — sondage clos @endif
    </p>

    {{--
        Mention obligatoire : ce sondage mesure l'opinion des lecteurs de
        Lambi News, pas une intention de vote représentative.
    --}}
    <p class="poll__disclaimer">
        Sondage informel, réservé aux lecteurs de Lambi News. Ce n’est pas
        une enquête scientifique et les résultats ne représentent pas
        l’ensemble de la population.
    </p>
</div>
