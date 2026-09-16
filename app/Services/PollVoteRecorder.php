<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Enregistre une voix dans un sondage d’opinion.
 *
 * Ce n’est pas un scrutin : le but est d’empêcher la triche ordinaire,
 * pas de garantir l’unicité absolue d’un votant. La vraie défense est
 * a posteriori — chaque voix est horodatée et rattachée à une empreinte,
 * ce qui permet de repérer et d’annuler une vague artificielle.
 */
class PollVoteRecorder
{
    /** Voix acceptées par heure pour une même adresse IP. */
    private const MAX_PER_IP_PER_HOUR = 5;

    /** Délai minimal entre l’affichage de la page et le vote, en secondes. */
    private const MIN_SECONDS_ON_PAGE = 2;

    public const RESULT_OK          = 'ok';
    public const RESULT_ALREADY     = 'already_voted';
    public const RESULT_RATE_LIMIT  = 'rate_limited';
    public const RESULT_TOO_FAST    = 'too_fast';
    public const RESULT_CLOSED      = 'closed';

    /**
     * @return array{status: string, voted: bool}
     */
    public function record(
        Poll $poll,
        PollOption $option,
        Request $request
    ): array {
        if ($poll->isClosed()) {
            return ['status' => self::RESULT_CLOSED, 'voted' => false];
        }

        /*
         * Piège à robots : un champ caché rempli, ou un envoi quasi
         * instantané, trahit un script plutôt qu’un lecteur.
         */
        if (filled($request->input('website'))) {
            return ['status' => self::RESULT_TOO_FAST, 'voted' => false];
        }

        $openedAt = (int) $request->input('opened_at', 0);

        if ($openedAt > 0 && (time() - $openedAt) < self::MIN_SECONDS_ON_PAGE) {
            return ['status' => self::RESULT_TOO_FAST, 'voted' => false];
        }

        $ipHash     = $this->ipHash($request);
        $voterHash  = $this->voterHash($request);

        if ($this->alreadyVoted($poll, $voterHash)) {
            return ['status' => self::RESULT_ALREADY, 'voted' => false];
        }

        /*
         * Plafond par IP plutôt que blocage : en Haïti, Digicel et Natcom
         * placent des milliers d’abonnés mobiles derrière une même adresse
         * publique. Bloquer une IP reviendrait à faire taire un quartier.
         */
        if ($this->tooManyFromIp($poll, $ipHash)) {
            return ['status' => self::RESULT_RATE_LIMIT, 'voted' => false];
        }

        try {
            DB::transaction(function () use ($poll, $option, $voterHash, $ipHash, $request): void {
                PollVote::create([
                    'poll_id'        => $poll->id,
                    'poll_option_id' => $option->id,
                    'voter_hash'     => $voterHash,
                    'ip_hash'        => $ipHash,
                    'user_agent'     => Str::limit((string) $request->userAgent(), 250, ''),
                    'referrer'       => Str::limit((string) $request->headers->get('referer'), 250, ''),
                    'voted_at'       => now(),
                ]);

                // Compteurs via le query builder : pas de updated_at parasite.
                DB::table('poll_options')->where('id', $option->id)->increment('votes_count');
                DB::table('polls')->where('id', $poll->id)->increment('votes_count');
            });
        } catch (QueryException) {
            // L’index unique a joué : deux envois simultanés du même votant.
            return ['status' => self::RESULT_ALREADY, 'voted' => false];
        }

        return ['status' => self::RESULT_OK, 'voted' => true];
    }

    /** Le visiteur a-t-il déjà voté à ce sondage ? */
    public function alreadyVoted(Poll $poll, ?string $voterHash = null): bool
    {
        $voterHash ??= $this->voterHash(request());

        return PollVote::query()
            ->where('poll_id', $poll->id)
            ->where('voter_hash', $voterHash)
            ->exists();
    }

    /** Option choisie par ce visiteur, pour la mettre en évidence. */
    public function votedOptionId(Poll $poll): ?int
    {
        return PollVote::query()
            ->where('poll_id', $poll->id)
            ->where('voter_hash', $this->voterHash(request()))
            ->value('poll_option_id');
    }

    private function tooManyFromIp(Poll $poll, string $ipHash): bool
    {
        return PollVote::query()
            ->where('poll_id', $poll->id)
            ->where('ip_hash', $ipHash)
            ->where('voted_at', '>=', now()->subHour())
            ->count() >= self::MAX_PER_IP_PER_HOUR;
    }

    private function voterHash(Request $request): string
    {
        return hash_hmac(
            'sha256',
            implode('|', [
                (string) $request->ip(),
                (string) $request->userAgent(),
                $request->hasSession() ? $request->session()->getId() : '',
            ]),
            (string) config('app.key')
        );
    }

    private function ipHash(Request $request): string
    {
        return hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
    }
}
