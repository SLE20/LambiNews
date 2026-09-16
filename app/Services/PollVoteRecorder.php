<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Enregistre une voix dans un sondage d'opinion.
 *
 * La règle est posée par la rédaction sur chaque sondage : un quota de
 * voix, et la façon de reconnaître un votant — l'appareil (par défaut)
 * ou la connexion entière. Reconnaître par IP fait taire tout un foyer
 * ou un cybercafé, ce qui n'est pas anodin en Haïti où les opérateurs
 * mobiles placent des milliers d'abonnés derrière une même adresse.
 *
 * Ce n'est pas un scrutin : le but est d'empêcher la triche ordinaire.
 * La vraie défense reste a posteriori — chaque voix est horodatée avec
 * son empreinte, ce qui permet de repérer et d'annuler une vague
 * artificielle (colonne is_void).
 */
class PollVoteRecorder
{
    /** Délai minimal entre l'affichage de la page et le vote, en secondes. */
    private const MIN_SECONDS_ON_PAGE = 2;

    /** Cookie d'appareil : rend l'empreinte stable d'une session à l'autre. */
    private const DEVICE_COOKIE = 'ln_voter';

    private const DEVICE_COOKIE_DAYS = 400;

    public const RESULT_OK         = 'ok';
    public const RESULT_ALREADY    = 'already_voted';
    public const RESULT_QUOTA      = 'quota_reached';
    public const RESULT_TOO_FAST   = 'too_fast';
    public const RESULT_CLOSED     = 'closed';
    public const RESULT_PAYMENT    = 'payment_required';

    /**
     * @return array{status: string, voted: bool, vote?: PollVote}
     */
    public function record(
        Poll $poll,
        PollOption $option,
        Request $request
    ): array {
        $guard = $this->guard($poll, $request);

        if ($guard !== null) {
            return ['status' => $guard, 'voted' => false];
        }

        // Un sondage payant n'enregistre rien avant d'avoir été réglé.
        if ($poll->isPaid()) {
            return ['status' => self::RESULT_PAYMENT, 'voted' => false];
        }

        $vote = $this->store($poll, $option, $request, PollVote::PAY_FREE);

        return ['status' => self::RESULT_OK, 'voted' => true, 'vote' => $vote];
    }

    /**
     * Contrôles communs aux sondages gratuits et payants.
     * Renvoie le motif de refus, ou null si le vote peut avoir lieu.
     */
    public function guard(Poll $poll, Request $request): ?string
    {
        if ($poll->isClosed()) {
            return self::RESULT_CLOSED;
        }

        /*
         * Piège à robots : un champ caché rempli, ou un envoi quasi
         * instantané, trahit un script plutôt qu'un lecteur.
         */
        if (filled($request->input('website'))) {
            return self::RESULT_TOO_FAST;
        }

        $openedAt = (int) $request->input('opened_at', 0);

        if ($openedAt > 0 && (time() - $openedAt) < self::MIN_SECONDS_ON_PAGE) {
            return self::RESULT_TOO_FAST;
        }

        $used  = $this->votesUsed($poll, $request);
        $quota = $poll->voteQuota();

        if ($used >= $quota) {
            return $quota === 1 ? self::RESULT_ALREADY : self::RESULT_QUOTA;
        }

        return null;
    }

    /** Écrit la voix et met à jour les compteurs. */
    public function store(
        Poll $poll,
        PollOption $option,
        Request $request,
        string $paymentStatus,
        array $payment = []
    ): PollVote {
        return DB::transaction(function () use ($poll, $option, $request, $paymentStatus, $payment) {
            $vote = PollVote::create([
                'poll_id'        => $poll->id,
                'poll_option_id' => $option->id,
                'voter_hash'     => $this->voterHash($request),
                'ip_hash'        => $this->ipHash($request),
                'user_agent'     => Str::limit((string) $request->userAgent(), 250, ''),
                'referrer'       => Str::limit((string) $request->headers->get('referer'), 250, ''),
                'payment_status' => $paymentStatus,
                'voted_at'       => now(),
                ...$payment,
            ]);

            // Une voix en attente de paiement ne compte pas encore.
            if ($paymentStatus !== PollVote::PAY_PENDING) {
                $this->bumpCounters($poll, $option);
            }

            return $vote;
        });
    }

    /** Appelé quand un paiement est confirmé : la voix devient valide. */
    public function confirmPaidVote(PollVote $vote): void
    {
        DB::transaction(function () use ($vote) {
            $vote->forceFill([
                'payment_status' => PollVote::PAY_PAID,
                'paid_at'        => now(),
            ])->save();

            $this->bumpCounters($vote->poll, $vote->option);
        });
    }

    private function bumpCounters(Poll $poll, PollOption $option): void
    {
        // Query builder brut : ne pas toucher updated_at.
        DB::table('poll_options')->where('id', $option->id)->increment('votes_count');
        DB::table('polls')->where('id', $poll->id)->increment('votes_count');
    }

    /**
     * Voix déjà émises par ce votant pour ce sondage.
     *
     * Selon le réglage du sondage, « ce votant » désigne l'appareil
     * (IP + navigateur + cookie) ou la connexion entière (IP). Les voix
     * annulées et celles restées impayées ne consomment pas le quota.
     */
    public function votesUsed(Poll $poll, ?Request $request = null): int
    {
        $request ??= request();

        $query = PollVote::query()
            ->where('poll_id', $poll->id)
            ->where('is_void', false)
            ->whereIn('payment_status', [PollVote::PAY_FREE, PollVote::PAY_PAID]);

        return $poll->identifiesByIp()
            ? $query->where('ip_hash', $this->ipHash($request))->count()
            : $query->where('voter_hash', $this->voterHash($request))->count();
    }

    /** Le visiteur a-t-il épuisé son quota ? */
    public function alreadyVoted(Poll $poll, ?string $ignored = null): bool
    {
        return $this->votesUsed($poll) >= $poll->voteQuota();
    }

    public function remainingVotes(Poll $poll): int
    {
        return max(0, $poll->voteQuota() - $this->votesUsed($poll));
    }

    /** Option choisie en dernier par ce visiteur, pour la mettre en évidence. */
    public function votedOptionId(Poll $poll): ?int
    {
        return $this->ownVotes($poll)->latest('voted_at')->value('poll_option_id');
    }

    /** @return array<int, int> Options déjà choisies, pour le vote multiple. */
    public function votedOptionIds(Poll $poll): array
    {
        return $this->ownVotes($poll)->pluck('poll_option_id')->all();
    }

    /** Voix de ce votant, selon le mode d'identification du sondage. */
    private function ownVotes(Poll $poll)
    {
        $query = PollVote::query()
            ->where('poll_id', $poll->id)
            ->where('is_void', false)
            ->whereIn('payment_status', [PollVote::PAY_FREE, PollVote::PAY_PAID]);

        return $poll->identifiesByIp()
            ? $query->where('ip_hash', $this->ipHash(request()))
            : $query->where('voter_hash', $this->voterHash(request()));
    }

    /**
     * Empreinte de l'appareil.
     *
     * Repose sur un cookie de longue durée plutôt que sur l'identifiant
     * de session : un téléphone et un ordinateur derrière le même WiFi
     * donnent deux empreintes distinctes, et l'empreinte survit à la
     * fermeture du navigateur.
     */
    public function voterHash(Request $request): string
    {
        return hash_hmac(
            'sha256',
            implode('|', [
                (string) $request->ip(),
                (string) $request->userAgent(),
                $this->deviceId($request),
            ]),
            (string) config('app.key')
        );
    }

    /** Identifiant d'appareil, créé au premier passage puis conservé. */
    public function deviceId(Request $request): string
    {
        $existing = $request->cookie(self::DEVICE_COOKIE);

        if (is_string($existing) && strlen($existing) === 32) {
            return $existing;
        }

        $id = Str::lower(Str::random(32));

        // File d'attente : le cookie part avec la réponse en cours.
        Cookie::queue(
            Cookie::make(self::DEVICE_COOKIE, $id, 60 * 24 * self::DEVICE_COOKIE_DAYS)
        );

        return $id;
    }

    public function ipHash(Request $request): string
    {
        return hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
    }
}
