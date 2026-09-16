<?php

namespace App\Services;

use App\Models\Ad;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Choisit la bannière à afficher pour un emplacement et tient les compteurs.
 */
class AdServer
{
    /**
     * Bannières déjà servies pendant cette requête.
     *
     * Deux appels au même emplacement dans une même page (colonne de droite
     * répétée en version mobile, par exemple) ne doivent pas compter deux
     * impressions ni afficher deux fois la même bannière.
     *
     * @var array<string, Ad|null>
     */
    private array $servedThisRequest = [];

    /**
     * Renvoie la bannière à afficher, ou null si l’emplacement est vide.
     */
    public function pick(string $position): ?Ad
    {
        if (array_key_exists($position, $this->servedThisRequest)) {
            return $this->servedThisRequest[$position];
        }

        $candidates = Ad::query()
            ->live()
            ->forPosition($position)
            ->get();

        $ad = $this->weightedPick($candidates);

        if ($ad !== null && $ad->type !== Ad::TYPE_ADSENSE) {
            // Les régies comptent leurs propres impressions ; compter les
            // nôtres en plus donnerait deux chiffres contradictoires.
            $this->recordImpression($ad);
        }

        return $this->servedThisRequest[$position] = $ad;
    }

    /**
     * Tirage pondéré : une bannière de poids 3 sort trois fois plus
     * souvent qu’une bannière de poids 1.
     *
     * @param Collection<int, Ad> $ads
     */
    private function weightedPick(Collection $ads): ?Ad
    {
        if ($ads->isEmpty()) {
            return null;
        }

        if ($ads->count() === 1) {
            return $ads->first();
        }

        $total = (int) $ads->sum(fn (Ad $ad) => max(1, $ad->weight));

        $ticket = random_int(1, $total);
        $cursor = 0;

        foreach ($ads as $ad) {
            $cursor += max(1, $ad->weight);

            if ($ticket <= $cursor) {
                return $ad;
            }
        }

        return $ads->last();
    }

    public function recordImpression(Ad $ad): void
    {
        $this->bump($ad, 'impressions');
    }

    public function recordClick(Ad $ad): void
    {
        $this->bump($ad, 'clicks');
    }

    /**
     * Incrémente le compteur global et la ligne du jour.
     *
     * Passe par le query builder brut : increment() d’Eloquent toucherait
     * updated_at, ce qui ferait passer chaque affichage pour une
     * modification de la bannière.
     */
    private function bump(Ad $ad, string $metric): void
    {
        $column = $metric === 'clicks' ? 'clicks_count' : 'impressions_count';

        DB::table('ads')
            ->where('id', $ad->id)
            ->increment($column);

        $today = now()->toDateString();

        // upsert : crée la ligne du jour, ou incrémente celle qui existe.
        DB::table('ad_daily_stats')->upsert(
            [[
                'ad_id'       => $ad->id,
                'date'        => $today,
                'impressions' => $metric === 'impressions' ? 1 : 0,
                'clicks'      => $metric === 'clicks' ? 1 : 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]],
            ['ad_id', 'date'],
            [$metric => DB::raw($metric.' + 1'), 'updated_at' => now()]
        );
    }
}
