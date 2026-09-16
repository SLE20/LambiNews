<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use App\Services\PollVoteRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PollController extends Controller
{
    public function index(PollVoteRecorder $recorder): View
    {
        $polls = Poll::query()
            ->with('options')
            ->orderByDesc('is_active')
            ->latest()
            ->paginate(10);

        return view('front.polls.index', compact('polls', 'recorder'));
    }

    public function show(PollVoteRecorder $recorder, string $slug): View
    {
        $poll = Poll::query()
            ->with('options')
            ->where('slug', $slug)
            ->firstOrFail();

        // Deux présentations : la vitrine pleine page, ou le bloc simple.
        return view(
            $poll->isShowcase() ? 'front.polls.showcase' : 'front.polls.show',
            compact('poll', 'recorder')
        );
    }

    public function vote(
        Request $request,
        PollVoteRecorder $recorder,
        string $slug
    ): RedirectResponse {
        $poll = Poll::query()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'poll_option_id' => ['required', 'integer'],
            'opened_at'      => ['nullable', 'integer'],
            // Champ leurre, invisible pour un lecteur.
            'website'        => ['nullable', 'string', 'max:100'],
        ]);

        $option = PollOption::query()
            ->where('poll_id', $poll->id)
            ->findOrFail($validated['poll_option_id']);

        $result = $recorder->record($poll, $option, $request);

        $messages = [
            PollVoteRecorder::RESULT_OK         => 'Mèsi, vòt ou an anrejistre.',
            PollVoteRecorder::RESULT_ALREADY    => 'Ou deja vote nan sondaj sa a.',
            PollVoteRecorder::RESULT_RATE_LIMIT => 'Twòp vòt soti sou menm koneksyon an. Tanpri tann yon ti moman.',
            PollVoteRecorder::RESULT_TOO_FAST   => 'Vòt la pa pase. Tanpri eseye ankò.',
            PollVoteRecorder::RESULT_CLOSED     => 'Sondaj sa a fèmen.',
        ];

        return redirect()
            ->route('polls.show', $poll->slug)
            ->with('poll_status', $messages[$result['status']] ?? '')
            ->with('poll_voted', $result['voted']);
    }
}
