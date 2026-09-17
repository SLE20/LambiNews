<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Services\MonCashCheckout;
use App\Services\PayPalClient;
use App\Services\WalCashClient;
use App\Services\PollVoteRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PollController extends Controller
{
    /** Messages rendus au lecteur selon l'issue du vote. */
    private const MESSAGES = [
        PollVoteRecorder::RESULT_OK       => 'Merci, votre vote a été enregistré.',
        PollVoteRecorder::RESULT_ALREADY  => 'Vous avez déjà voté à ce sondage. Un seul vote par connexion.',
        PollVoteRecorder::RESULT_QUOTA    => 'Vous avez utilisé tous vos votes pour ce sondage.',
        PollVoteRecorder::RESULT_TOO_FAST => 'Le vote n’a pas été enregistré. Veuillez réessayer.',
        PollVoteRecorder::RESULT_CLOSED   => 'Ce sondage est clôturé.',
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
            'moncashReady'   => WalCashClient::fromConfig()->isConfigured(),
            'htgRate'        => WalCashClient::htgRate(),
        ];

        return view(
            $poll->isShowcase() ? 'front.polls.showcase' : 'front.polls.show',
            $data
        );
    }

    /**
     * Urne publique d'un sondage clôturé : un bulletin par ligne, pour
     * que chacun puisse recompter.
     *
     * Rien qui identifie un votant : ni empreinte, ni adresse, ni heure
     * précise — seulement le jour, qui ne permet pas de recouper.
     */
    public function ballots(string $slug): StreamedResponse
    {
        $poll = Poll::query()->with('options')->where('slug', $slug)->firstOrFail();

        abort_unless($poll->isClosed(), 404);

        $labels = $poll->options->pluck('label', 'id');

        return response()->streamDownload(function () use ($poll, $labels): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['bulletin', 'choix', 'jour', 'type'], ';');

            $n = 0;
            PollVote::query()
                ->where('poll_id', $poll->id)
                ->where('is_void', false)
                ->whereIn('payment_status', [PollVote::PAY_FREE, PollVote::PAY_PAID])
                // Trié par choix : l'ordre de dépôt ne se devine pas, et le
                // parcours par lots reste stable (un tri aléatoire ne l'est pas).
                ->orderBy('poll_option_id')
                ->orderBy('id')
                ->select(['id', 'poll_option_id', 'voted_at', 'payment_status'])
                ->lazy(500)
                ->each(function ($vote) use ($out, $labels, &$n): void {
                    fputcsv($out, [
                        ++$n,
                        $labels[$vote->poll_option_id] ?? '?',
                        optional($vote->voted_at)->toDateString(),
                        $vote->payment_status === PollVote::PAY_PAID ? 'payant' : 'gratuit',
                    ], ';');
                });

            fclose($out);
        }, 'urne-'.$poll->slug.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
                ->with('poll_status', 'Ce sondage est payant : '.$poll->formattedPrice().' par vote.');
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
            return response()->json(['message' => 'Ce sondage n’est pas payant.'], 422);
        }

        if (($refusal = $recorder->guard($poll, $request)) !== null) {
            return response()->json([
                'message' => self::MESSAGES[$refusal] ?? 'Le vote n’a pas été enregistré.',
            ], 422);
        }

        $option = $this->resolveOption($request, $poll);
        $price  = number_format((float) $poll->vote_price, 2, '.', '');

        $provider = $request->input('provider') === 'moncash' ? 'moncash' : 'paypal';

        $vote = $recorder->store(
            $poll,
            $option,
            $request,
            PollVote::PAY_PENDING,
            ['amount' => $price, 'provider' => $provider]
        );

        if ($provider === 'moncash') {
            $result = MonCashCheckout::open(
                $vote,
                (float) $price,
                $poll->currency ?: 'USD',
                'VOTE-'.$vote->id,
                'Lambi News — vote : '.$option->label,
                'paypal_order_id',
                ['poll' => $poll->slug]
            );

            if (! $result['ok']) {
                $vote->delete();

                return response()->json(['message' => $result['message']], 502);
            }

            return response()->json([
                'provider'     => 'moncash',
                'checkout_url' => $result['checkout_url'],
            ]);
        }

        try {
            $order = PayPalClient::fromConfig()->createOrder(
                $price,
                $poll->currency ?: 'USD',
                'VOTE-'.$vote->id,
                'Lambi News — vote : '.$option->label
            );
        } catch (Throwable $e) {
            Log::error('PayPal poll createOrder: '.$e->getMessage());
            $vote->delete();

            return response()->json([
                'message' => 'Impossible de joindre PayPal. Veuillez réessayer.',
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
                'message' => 'Le paiement n’a pas abouti. Aucun montant n’a été prélevé.',
            ], 502);
        }

        if (data_get($payload, 'status') !== 'COMPLETED') {
            return response()->json(['message' => 'PayPal n’a pas confirmé le paiement.'], 422);
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
