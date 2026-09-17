<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Chiffres de monétisation du site, toutes sources confondues.
 *
 * Quatre flux encaissent de l'argent : dons, annonces payantes, votes
 * payants et campagnes de financement. Ils ont des tables et des
 * statuts différents ; cette classe les ramène à une même forme —
 * montant, devise, prestataire, date de paiement — pour pouvoir les
 * additionner et les comparer.
 *
 * Les montants sont tenus en dollars : la gourde est convertie au taux
 * réglé par la rédaction (Réglages → htg_per_usd). Les montants en
 * gourdes restent affichés à part, pour qu'on retrouve ce que MonCash a
 * réellement versé.
 */
class RevenueStats
{
    public const STREAMS = [
        'donations'     => ['label' => 'Dons',                'icon' => 'la-heart',       'color' => '#d5a51f'],
        'announcements' => ['label' => 'Annonces payantes',   'icon' => 'la-bullhorn',    'color' => '#2563eb'],
        'votes'         => ['label' => 'Votes payants',       'icon' => 'la-poll',        'color' => '#7c3aed'],
        'fundraisers'   => ['label' => 'Campagnes',           'icon' => 'la-hand-holding-heart', 'color' => '#16a34a'],
    ];

    private float $rate;

    public function __construct(
        public readonly Carbon $start,
        public readonly Carbon $end,
    ) {
        $this->rate = WalCashClient::htgRate();
    }

    /** Période précédente de même durée, pour mesurer l'évolution. */
    public function previous(): self
    {
        $days = $this->start->diffInDays($this->end) + 1;

        return new self(
            $this->start->copy()->subDays((int) round($days))->startOfDay(),
            $this->start->copy()->subDay()->endOfDay(),
        );
    }

    public function rate(): float
    {
        return $this->rate;
    }

    /*
    |--------------------------------------------------------------------------
    | Sources d'encaissement
    |--------------------------------------------------------------------------
    |
    | Chaque requête renvoie les mêmes colonnes : amount, currency,
    | provider, paid_at, created_at, plus un statut ramené à
    | paid / pending / failed.
    |
    */

    private function source(string $stream): Builder
    {
        return match ($stream) {
            'donations' => DB::table('donations')->select([
                'amount', 'currency', 'provider', 'paid_at', 'created_at',
                DB::raw("CASE status WHEN 'completed' THEN 'paid'
                    WHEN 'pending' THEN 'pending' ELSE 'failed' END AS state"),
            ]),

            // Une annonce payée reste une recette, même rejetée ensuite.
            'announcements' => DB::table('announcements')->select([
                'amount', 'currency', 'provider', 'paid_at', 'created_at',
                DB::raw("CASE WHEN paid_at IS NOT NULL THEN 'paid'
                    WHEN status = 'pending_payment' THEN 'pending' ELSE 'failed' END AS state"),
            ]),

            // La devise est portée par le sondage, pas par le vote.
            'votes' => DB::table('poll_votes')
                ->join('polls', 'polls.id', '=', 'poll_votes.poll_id')
                ->where('poll_votes.payment_status', '!=', 'free')
                ->select([
                    'poll_votes.amount', 'polls.currency', 'poll_votes.provider',
                    'poll_votes.paid_at', 'poll_votes.created_at',
                    DB::raw("CASE poll_votes.payment_status WHEN 'paid' THEN 'paid'
                        WHEN 'pending_payment' THEN 'pending' ELSE 'failed' END AS state"),
                ]),

            'fundraisers' => DB::table('fundraiser_contributions')->select([
                'amount', 'currency', 'provider', 'paid_at', 'created_at',
                DB::raw("CASE status WHEN 'completed' THEN 'paid'
                    WHEN 'pending' THEN 'pending' ELSE 'failed' END AS state"),
            ]),
        };
    }

    /** Toutes les sources dans une seule requête, étiquetées par flux. */
    private function union(): Builder
    {
        $query = null;

        foreach (array_keys(self::STREAMS) as $stream) {
            $part = $this->source($stream)->addSelect(DB::raw("'{$stream}' AS stream"));
            $query = $query ? $query->unionAll($part) : $part;
        }

        return DB::query()->fromSub($query, 'tx');
    }

    /** Expression SQL du montant converti en dollars. */
    private function usdExpression(): string
    {
        $rate = number_format($this->rate, 6, '.', '');

        return "CASE WHEN UPPER(currency) = 'HTG' THEN amount / {$rate} ELSE amount END";
    }

    private function paidInPeriod(): Builder
    {
        return $this->union()
            ->where('state', 'paid')
            ->whereBetween('paid_at', [$this->start, $this->end]);
    }

    /*
    |--------------------------------------------------------------------------
    | Indicateurs
    |--------------------------------------------------------------------------
    */

    /**
     * Recette par flux : total en dollars, part en gourdes, nombre de
     * paiements, panier moyen et taux de réussite des paiements lancés.
     *
     * @return array<string, array<string, mixed>>
     */
    public function byStream(): array
    {
        $usd = $this->usdExpression();

        $paid = $this->paidInPeriod()
            ->groupBy('stream')
            ->selectRaw("stream, COUNT(*) AS n, SUM({$usd}) AS usd,
                SUM(CASE WHEN UPPER(currency) = 'HTG' THEN amount ELSE 0 END) AS htg,
                SUM(CASE WHEN UPPER(currency) <> 'HTG' THEN amount ELSE 0 END) AS usd_native")
            ->get()->keyBy('stream');

        // Les tentatives se datent à leur création : un paiement lancé
        // le 30 et payé le 1er compte bien dans la période du 30.
        $attempts = $this->union()
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('stream')
            ->selectRaw("stream,
                SUM(CASE WHEN state = 'paid' THEN 1 ELSE 0 END) AS ok,
                SUM(CASE WHEN state = 'pending' THEN 1 ELSE 0 END) AS pending,
                COUNT(*) AS total")
            ->get()->keyBy('stream');

        $out = [];

        foreach (self::STREAMS as $key => $meta) {
            $p = $paid->get($key);
            $a = $attempts->get($key);
            $count = (int) ($p->n ?? 0);
            $total = (float) ($p->usd ?? 0);

            $out[$key] = $meta + [
                'usd'        => $total,
                'htg'        => (float) ($p->htg ?? 0),
                'usd_native' => (float) ($p->usd_native ?? 0),
                'count'      => $count,
                'average'    => $count > 0 ? $total / $count : 0.0,
                'pending'    => (int) ($a->pending ?? 0),
                'attempts'   => (int) ($a->total ?? 0),
                'success'    => ($a->total ?? 0) > 0 ? round(($a->ok / $a->total) * 100, 1) : null,
            ];
        }

        return $out;
    }

    /** Recette totale en dollars. */
    public function total(): float
    {
        return (float) $this->paidInPeriod()->selectRaw("SUM({$this->usdExpression()}) AS usd")->value('usd');
    }

    /** Répartition PayPal / MonCash. @return array<string, array{usd: float, count: int}> */
    public function byProvider(): array
    {
        return $this->paidInPeriod()
            ->groupBy('provider')
            ->selectRaw("provider, COUNT(*) AS n, SUM({$this->usdExpression()}) AS usd")
            ->get()
            ->mapWithKeys(fn ($r) => [
                (string) ($r->provider ?: 'paypal') => ['usd' => (float) $r->usd, 'count' => (int) $r->n],
            ])
            ->all();
    }

    /**
     * Série quotidienne par flux, pour le graphique empilé.
     *
     * @return array{labels: list<string>, series: array<string, list<float>>}
     */
    public function daily(): array
    {
        $rows = $this->paidInPeriod()
            ->groupBy('stream', 'day')
            ->selectRaw("stream, DATE(paid_at) AS day, SUM({$this->usdExpression()}) AS usd")
            ->get();

        $index = [];
        foreach ($rows as $r) {
            $index[$r->stream][$r->day] = (float) $r->usd;
        }

        $labels = [];
        $series = array_fill_keys(array_keys(self::STREAMS), []);

        foreach (CarbonPeriod::create($this->start->copy()->startOfDay(), $this->end->copy()->startOfDay()) as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');

            foreach (array_keys(self::STREAMS) as $stream) {
                $series[$stream][] = round($index[$stream][$key] ?? 0, 2);
            }
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /** Derniers paiements confirmés, tous flux confondus. */
    public function recent(int $limit = 12): Collection
    {
        $pick = fn (string $table, string $stream, string $label, array $where) => DB::table($table)
            ->where($where)
            ->whereNotNull('paid_at')
            ->select(['amount', 'currency', 'provider', 'paid_at', DB::raw("'{$stream}' AS stream"), DB::raw("{$label} AS who")]);

        $votes = DB::table('poll_votes')
            ->join('polls', 'polls.id', '=', 'poll_votes.poll_id')
            ->where('poll_votes.payment_status', 'paid')
            ->select([
                'poll_votes.amount', 'polls.currency', 'poll_votes.provider', 'poll_votes.paid_at',
                DB::raw("'votes' AS stream"), DB::raw('polls.question AS who'),
            ]);

        return DB::query()->fromSub(
            $pick('donations', 'donations', "CASE WHEN is_anonymous = 1 THEN 'Anonyme' ELSE COALESCE(donor_name, 'Anonyme') END", [['status', 'completed']])
                ->unionAll($pick('announcements', 'announcements', 'title', []))
                ->unionAll($votes)
                ->unionAll($pick('fundraiser_contributions', 'fundraisers', "CASE WHEN is_anonymous = 1 THEN 'Anonyme' ELSE COALESCE(donor_name, 'Anonyme') END", [['status', 'completed']])),
            'tx'
        )->orderByDesc('paid_at')->limit($limit)->get();
    }

    /** Paiements lancés il y a plus d'une heure et jamais aboutis. */
    public function stalePending(): int
    {
        return $this->union()
            ->where('state', 'pending')
            ->where('created_at', '<', now()->subHour())
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Détail par produit
    |--------------------------------------------------------------------------
    */

    public function fundraisers(): Collection
    {
        return DB::table('fundraisers')
            ->whereIn('status', ['active', 'closed'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('raised_amount')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'status', 'goal_amount', 'raised_amount', 'currency', 'contributions_count']);
    }

    public function paidPolls(): Collection
    {
        return DB::table('polls')
            ->leftJoin('poll_votes', function ($join) {
                $join->on('poll_votes.poll_id', '=', 'polls.id')
                    ->where('poll_votes.payment_status', 'paid');
            })
            ->where('polls.is_paid', true)
            ->groupBy('polls.id', 'polls.question', 'polls.currency', 'polls.vote_price')
            ->orderByDesc(DB::raw('SUM(poll_votes.amount)'))
            ->limit(6)
            ->get([
                'polls.id', 'polls.question', 'polls.currency', 'polls.vote_price',
                DB::raw('COUNT(poll_votes.id) AS paid_votes'),
                DB::raw('COALESCE(SUM(poll_votes.amount), 0) AS revenue'),
            ]);
    }

    /** Annonces : volume par type et file de modération. */
    public function announcements(): array
    {
        $byType = DB::table('announcements')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$this->start, $this->end])
            ->groupBy('type')
            ->selectRaw("type, COUNT(*) AS n, SUM({$this->usdExpression()}) AS usd")
            ->orderByDesc('usd')
            ->get();

        return [
            'by_type'   => $byType,
            'to_review' => DB::table('announcements')->where('status', 'paid')->count(),
            'live'      => DB::table('announcements')->where('status', 'published')->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Audience monétisable
    |--------------------------------------------------------------------------
    */

    /** Publicité : affichages, clics, taux de clic, meilleurs emplacements. */
    public function ads(): array
    {
        $range = [$this->start->toDateString(), $this->end->toDateString()];

        $totals = DB::table('ad_daily_stats')
            ->whereBetween('date', $range)
            ->selectRaw('COALESCE(SUM(impressions), 0) AS impressions, COALESCE(SUM(clicks), 0) AS clicks')
            ->first();

        $top = DB::table('ad_daily_stats')
            ->join('ads', 'ads.id', '=', 'ad_daily_stats.ad_id')
            ->whereBetween('ad_daily_stats.date', $range)
            ->groupBy('ads.id', 'ads.name', 'ads.client_name', 'ads.position')
            ->orderByDesc(DB::raw('SUM(ad_daily_stats.impressions)'))
            ->limit(6)
            ->get([
                'ads.id', 'ads.name', 'ads.client_name', 'ads.position',
                DB::raw('SUM(ad_daily_stats.impressions) AS impressions'),
                DB::raw('SUM(ad_daily_stats.clicks) AS clicks'),
            ]);

        $today = now()->toDateString();

        return [
            'impressions' => (int) $totals->impressions,
            'clicks'      => (int) $totals->clicks,
            'ctr'         => $totals->impressions > 0 ? round($totals->clicks / $totals->impressions * 100, 2) : 0,
            'active'      => DB::table('ads')
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today))
                ->count(),
            'top'         => $top,
        ];
    }

    /** Articles sponsorisés : combien, et quelle audience ils ont eue. */
    public function sponsored(): array
    {
        $articles = DB::table('articles')
            ->where('is_sponsored', true)
            ->leftJoin('article_views', function ($join) {
                $join->on('article_views.article_id', '=', 'articles.id')
                    ->whereBetween('article_views.viewed_at', [$this->start, $this->end]);
            })
            ->groupBy('articles.id', 'articles.title', 'articles.slug', 'articles.sponsor_name')
            ->orderByDesc(DB::raw('COUNT(article_views.id)'))
            ->limit(6)
            ->get([
                'articles.id', 'articles.title', 'articles.slug', 'articles.sponsor_name',
                DB::raw('COUNT(article_views.id) AS views'),
            ]);

        return [
            'count'    => DB::table('articles')->where('is_sponsored', true)->count(),
            'views'    => (int) $articles->sum('views'),
            'articles' => $articles,
        ];
    }

    /** Sondages : participation gratuite et payante. */
    public function polls(): array
    {
        $votes = DB::table('poll_votes')
            ->where('is_void', false)
            ->whereBetween('voted_at', [$this->start, $this->end]);

        return [
            'open'   => DB::table('polls')->where('is_active', true)->count(),
            'votes'  => (clone $votes)->count(),
            'voters' => (clone $votes)->distinct()->count('voter_hash'),
            'paid'   => (clone $votes)->where('payment_status', 'paid')->count(),
        ];
    }

    /** Infolettre : croissance de la liste et fiabilité des envois. */
    public function newsletter(): array
    {
        $sends = DB::table('newsletter_sends')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('status')
            ->pluck(DB::raw('COUNT(*)'), 'status');

        return [
            'active'       => DB::table('subscribers')->where('is_active', true)->count(),
            'joined'       => DB::table('subscribers')->whereBetween('created_at', [$this->start, $this->end])->count(),
            'left'         => DB::table('subscribers')->whereBetween('unsubscribed_at', [$this->start, $this->end])->count(),
            'sent'         => (int) ($sends['sent'] ?? 0),
            'failed'       => (int) ($sends['failed'] ?? 0),
            'campaigns'    => DB::table('newsletter_campaigns')->whereBetween('sent_at', [$this->start, $this->end])->count(),
        ];
    }

    /** Variation en pourcentage ; null quand il n'y a pas de base. */
    public static function growth(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? null : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
