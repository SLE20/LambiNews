<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Services\PayPalClient;
use App\Services\PollVoteRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PollController extends Controller
{
    /** Messages rendus au lecteur selon l'issue du vote. */
    private const MESSAGES = [
        PollVoteRecorder::RESULT_OK       => 'Mèsi, vòt ou an anrejistre.',
        PollVoteRecorder::RESULT_ALREADY  => 'Ou deja vote nan sondaj sa a. Yon sèl vòt pa koneksyon.',
        PollVoteRecorder::RESULT_QUOTA    => 'Ou fin itilize tout vòt ou yo nan sondaj sa a.',
        PollVoteRecorder::RESULT_TOO_FAST => 'Vòt la pa pase. Tanpri eseye ankò.',
        PollVoteRecorder::RESULT_CLOSED   => 'Sondaj sa a fèmen.',
    ];

    public function index(PollVoteRecorder $recorder): View
    {
        $polls = Poll::query()
            ->with('options')
            // Un sondage à moins de deux choix n'est pas encore prêt.
            ->has('options', '>=', 2)
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

        $data = [
            'poll'           => $poll,
            'recorder'       => $recorder,
            'paypalClientId' => PayPalClient::publicClientId(),
            'paypalReady'    => PayPalClient::fromConfig()->isConfigured(),
        ];

        return view(
            $poll->isShowcase() ? 'front.polls.showcase' : 'front.polls.show',
            $data
        );
    }

    /** Vote gratuit, envoyé par le formulaire. */
    public function vote(
        Request $request,
        PollVoteRecorder $recorder,
        string $slug
    ): RedirectResponse {
        $poll   = Poll::query()->where('slug', $slug)->firstOrFail();
        $option = $this->resolveOption($request, $poll);

        $result = $recorder->record($poll, $option, $request);

        // Un sondage payant ne passe pas par ici : il faut régler d'abord.
        if ($result['status'] === PollVoteRecorder::RESULT_PAYMENT) {
            return redirect()
                ->route('polls.show', $poll->slug)
                ->with('poll_status', 'Sondaj sa a peyan : '.$poll->formattedPrice().' pou chak vòt.');
        }

        return redirect()
            ->route('polls.show', $poll->slug)
            ->with('poll_status', self::MESSAGES[$result['status']] ?? '')
            ->with('poll_voted', $result['voted']);
    }

    /**
     * Sondage payant : ouvre la commande PayPal et met la voix en
     * attente. Elle ne comptera qu'une fois le paiement confirmé.
     */
    public function payStart(
        Request $request,
        PollVoteRecorder $recorder,
        string $slug
    ): JsonResponse {
        $poll = Poll::query()->where('slug', $slug)->firstOrFail();

        if (! $poll->isPaid()) {
            return response()->json(['message' => 'Sondaj sa a pa peyan.'], 422);
        }

        if (($refusal = $recorder->guard($poll, $request)) !== null) {
            return response()->json([
                'message' => self::MESSAGES[$refusal] ?? 'Vòt la pa pase.',
            ], 422);
        }

        $option = $this->resolveOption($request, $poll);
        $price  = number_format((float) $poll->vote_price, 2, '.', '');

        $vote = $recorder->store(
            $poll,
            $option,
            $request,
            PollVote::PAY_PENDING,
            ['amount' => $price]
        );

        try {
            $order = PayPalClient::fromConfig()->createOrder(
                $price,
                $poll->currency ?: 'USD',
                'VOTE-'.$vote->id,
                'Lambi News — vòt: '.$option->label
            );
        } catch (Throwable $e) {
            Log::error('PayPal poll createOrder: '.$e->getMessage());
            $vote->delete();

            return response()->json([
                'message' => 'Nou pa rive kontakte PayPal. Tanpri eseye ankò.',
            ], 502);
        }

        $vote->forceFill(['paypal_order_id' => $order['id']])->save();

        return response()->json(['orderID' => $order['id']]);
    }

    /** Capture le paiement : la voix devient valide et compte. */
    public function payCapture(
        Request $request,
        PollVoteRecorder $recorder,
        string $slug
    ): JsonResponse {
        $validated = $request->validate([
            'orderID' => ['required', 'string', 'max:64'],
        ]);

        $vote = PollVote::query()
            ->with(['poll', 'option'])
            ->where('paypal_order_id', $validated['orderID'])
            ->firstOrFail();

        if ($vote->payment_status === PollVote::PAY_PAID) {
            return response()->json(['redirect_to' => route('polls.show', $slug)]);
        }

        try {
            $payload = PayPalClient::fromConfig()->captureOrder($validated['orderID']);
        } catch (Throwable $e) {
            Log::error('PayPal poll captureOrder: '.$e->getMessage());

            return response()->json([
                'message' => 'Peman an pa pase. Okenn kòb pa pran sou kont ou.',
            ], 502);
        }

        if (data_get($payload, 'status') !== 'COMPLETED') {
            return response()->json(['message' => 'PayPal pa konfime peman an.'], 422);
        }

        $vote->forceFill([
            'paypal_capture_id' => data_get($payload, 'purchase_units.0.payments.captures.0.id'),
        ])->save();

        $recorder->confirmPaidVote($vote);

        return response()->json(['redirect_to' => route('polls.show', $slug)]);
    }

    /** Valide que l'option appartient bien au sondage. */
    private function resolveOption(Request $request, Poll $poll): PollOption
    {
        $validated = $request->validate([
            'poll_option_id' => ['required', 'integer'],
            'opened_at'      => ['nullable', 'integer'],
            // Champ leurre, invisible pour un lecteur.
            'website'        => ['nullable', 'string', 'max:100'],
        ]);

        return PollOption::query()
            ->where('poll_id', $poll->id)
            ->findOrFail($validated['poll_option_id']);
    }
}
