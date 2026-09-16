<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Category;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Dossier de presse public.
 *
 * Donne à un annonceur des chiffres d’audience vérifiables avant de
 * discuter d’un prix. Les chiffres sortent de la table article_views,
 * pas d’une estimation.
 */
class MediaKitController extends Controller
{
    /** Les chiffres bougent lentement : inutile de les recalculer à chaque visite. */
    private const CACHE_MINUTES = 60;

    public function index(): View
    {
        $stats = Cache::remember(
            'media_kit.stats',
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->buildStats()
        );

        return view('front.media-kit', [
            'stats'     => $stats,
            'positions' => Ad::positions(),
        ]);
    }

    /** @return array<string, mixed> */
    private function buildStats(): array
    {
        $since30 = now()->subDays(29)->startOfDay();
        $since90 = now()->subDays(89)->startOfDay();

        $views30 = ArticleView::query()->where('viewed_at', '>=', $since30);

        return [
            'views_30'    => (clone $views30)->count(),
            'views_90'    => ArticleView::query()->where('viewed_at', '>=', $since90)->count(),

            'visitors_30' => (clone $views30)
                ->distinct('visitor_hash')
                ->count('visitor_hash'),

            'articles_total' => Article::query()->published()->count(),

            'articles_30' => Article::query()
                ->published()
                ->where('published_at', '>=', $since30)
                ->count(),

            'categories' => Category::query()->where('is_active', true)->count(),

            'subscribers' => Subscriber::query()->where('is_active', true)->count(),

            /*
             * Répartition par appareil : un annonceur veut savoir s’il
             * doit fournir un visuel pensé pour le mobile.
             */
            'devices' => (clone $views30)
                ->selectRaw('device_type, COUNT(*) as total')
                ->whereNotNull('device_type')
                ->groupBy('device_type')
                ->orderByDesc('total')
                ->pluck('total', 'device_type')
                ->all(),

            /*
             * D’où viennent les lecteurs. On ne garde que le domaine :
             * l’URL complète serait du bruit dans un dossier de presse.
             */
            'sources' => (clone $views30)
                ->whereNotNull('referrer')
                ->where('referrer', '!=', '')
                ->pluck('referrer')
                ->map(fn ($url) => parse_url((string) $url, PHP_URL_HOST) ?: null)
                ->filter()
                ->map(fn ($host) => preg_replace('/^www\./', '', (string) $host))
                ->countBy()
                ->sortDesc()
                ->take(6)
                ->all(),

            'top_categories' => Article::query()
                ->published()
                ->with('category')
                ->get()
                ->groupBy(fn (Article $a) => $a->category?->name ?? '—')
                ->map(fn ($group) => $group->count())
                ->sortDesc()
                ->take(6)
                ->all(),

            'generated_at' => now(),
        ];
    }
}
