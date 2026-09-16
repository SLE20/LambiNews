<?php

namespace App\View\Components;

use App\Models\Poll;
use App\Services\PollVoteRecorder;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Encart « sondage » : affiche le sondage ouvert le plus récent.
 *
 *     <x-latest-poll />
 *
 * Ne rend rien s’il n’y a aucun sondage ouvert.
 */
class LatestPoll extends Component
{
    public ?Poll $poll = null;

    public PollVoteRecorder $recorder;

    public function __construct(public bool $compact = true)
    {
        $this->poll = Poll::query()->open()->with('options')->latest()->first();
        $this->recorder = app(PollVoteRecorder::class);
    }

    public function shouldRender(): bool
    {
        // Il faut au moins deux choix pour qu'il y ait un arbitrage.
        return $this->poll !== null && $this->poll->options->count() >= 2;
    }

    public function render(): View
    {
        return view('components.latest-poll');
    }
}
