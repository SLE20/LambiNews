<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\View\View;

/**
 * Rapport de campagne consultable par l’annonceur.
 *
 * Accessible par un jeton dans l’URL, sans compte : un client à
 * Port-au-Prince doit pouvoir vérifier ses chiffres depuis son téléphone
 * sans qu’on lui crée un accès à l’administration.
 */
class AdReportController extends Controller
{
    public function __invoke(string $token): View
    {
        $ad = Ad::query()
            ->where('report_token', $token)
            ->firstOrFail();

        // 60 derniers jours, du plus ancien au plus récent.
        $daily = $ad->dailyStats()
            ->where('date', '>=', now()->subDays(59)->toDateString())
            ->orderBy('date')
            ->get();

        return view('front.ad-report', [
            'ad'        => $ad,
            'daily'     => $daily,
            'maxDaily'  => max(1, (int) $daily->max('impressions')),
        ]);
    }
}
