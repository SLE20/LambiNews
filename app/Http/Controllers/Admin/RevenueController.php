<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueStats;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Tableau des revenus : dons, annonces, votes payants, campagnes,
 * plus l'audience que l'on vend (publicité, contenus sponsorisés,
 * infolettre). Réservé aux administrateurs : ce sont des chiffres
 * financiers, pas éditoriaux.
 */
class RevenueController extends Controller
{
    /** Périodes proposées en un clic, en jours. */
    public const PRESETS = [7 => '7 jours', 30 => '30 jours', 90 => '90 jours', 365 => '12 mois'];

    public function index(Request $request): View
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        $validated = $request->validate([
            'days'       => ['nullable', 'integer', 'in:'.implode(',', array_keys(self::PRESETS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        [$start, $end, $days] = $this->period($validated);

        $stats    = new RevenueStats($start, $end);
        $previous = $stats->previous();

        $total     = $stats->total();
        $lastTotal = $previous->total();
        $streams   = $stats->byStream();
        $lastByStream = $previous->byStream();

        foreach ($streams as $key => &$stream) {
            $stream['growth'] = RevenueStats::growth($stream['usd'], $lastByStream[$key]['usd']);
        }
        unset($stream);

        return view('admin.revenue', [
            'start'         => $start,
            'end'           => $end,
            'days'          => $days,
            'presets'       => self::PRESETS,
            'rate'          => $stats->rate(),
            'total'         => $total,
            'growth'        => RevenueStats::growth($total, $lastTotal),
            'payments'      => array_sum(array_column($streams, 'count')),
            'streams'       => $streams,
            'providers'     => $stats->byProvider(),
            'daily'         => $stats->daily(),
            'recent'        => $stats->recent(),
            'stale'         => $stats->stalePending(),
            'fundraisers'   => $stats->fundraisers(),
            'paidPolls'     => $stats->paidPolls(),
            'announcements' => $stats->announcements(),
            'ads'           => $stats->ads(),
            'sponsored'     => $stats->sponsored(),
            'polls'         => $stats->polls(),
            'newsletter'    => $stats->newsletter(),
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon, 2: ?int} */
    private function period(array $input): array
    {
        if (! empty($input['start_date']) || ! empty($input['end_date'])) {
            $end   = Carbon::parse($input['end_date'] ?? now())->endOfDay();
            $start = Carbon::parse($input['start_date'] ?? $end->copy()->subDays(29))->startOfDay();

            // Une année au plus : au-delà, le graphique quotidien devient illisible.
            if ($start->diffInDays($end) > 366) {
                $start = $end->copy()->subDays(365)->startOfDay();
            }

            return [$start, $end, null];
        }

        $days = (int) ($input['days'] ?? 30);

        return [now()->subDays($days - 1)->startOfDay(), now()->endOfDay(), $days];
    }
}
