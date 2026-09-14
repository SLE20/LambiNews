<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Comment;
use App\Models\Subscriber;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->subDays(29)->startOfDay();

        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : now()->endOfDay();

        /*
         * Limite la période à une année pour éviter
         * des requêtes inutilement trop lourdes.
         */
        if ($startDate->diffInDays($endDate) > 365) {
            $startDate = $endDate->copy()
                ->subDays(364)
                ->startOfDay();
        }

        $baseViewsQuery = ArticleView::query()
            ->whereBetween('viewed_at', [
                $startDate,
                $endDate,
            ]);

        $totalViews = (clone $baseViewsQuery)->count();

        $uniqueVisitors = (clone $baseViewsQuery)
            ->distinct('visitor_hash')
            ->count('visitor_hash');

        $totalArticles = Article::query()->count();

        $publishedArticles = Article::query()
            ->where('status', 'published')
            ->where(function ($query) use ($endDate): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', $endDate);
            })
            ->count();

        $articlesPublishedDuringPeriod = Article::query()
            ->where('status', 'published')
            ->whereBetween('published_at', [
                $startDate,
                $endDate,
            ])
            ->count();

        $newSubscribers = Subscriber::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->count();

        $newComments = Comment::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->count();

        $averageViewsPerArticle = $publishedArticles > 0
            ? round($totalViews / $publishedArticles, 1)
            : 0;

        /*
         * Prépare toutes les dates afin que le graphique
         * affiche aussi les journées sans consultation.
         */
        $viewsGroupedByDay = ArticleView::query()
            ->selectRaw('DATE(viewed_at) as view_date')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('viewed_at', [
                $startDate,
                $endDate,
            ])
            ->groupByRaw('DATE(viewed_at)')
            ->orderByRaw('DATE(viewed_at)')
            ->pluck('total', 'view_date');

        $chartLabels = [];
        $chartViews = [];

        foreach (
            CarbonPeriod::create(
                $startDate->copy()->startOfDay(),
                $endDate->copy()->startOfDay()
            ) as $date
        ) {
            $dateKey = $date->format('Y-m-d');

            $chartLabels[] = $date->format('d/m');
            $chartViews[] = (int) ($viewsGroupedByDay[$dateKey] ?? 0);
        }

        /*
         * Articles les plus consultés pendant la période.
         */
        $topArticles = ArticleView::query()
            ->join(
                'articles',
                'articles.id',
                '=',
                'article_views.article_id'
            )
            ->leftJoin(
                'categories',
                'categories.id',
                '=',
                'articles.category_id'
            )
            ->whereBetween('article_views.viewed_at', [
                $startDate,
                $endDate,
            ])
            ->select([
                'articles.id',
                'articles.title',
                'articles.slug',
                'categories.name as category_name',
            ])
            ->selectRaw('COUNT(article_views.id) as total_views')
            ->selectRaw(
                'COUNT(DISTINCT article_views.visitor_hash) '
                .'as unique_visitors'
            )
            ->groupBy(
                'articles.id',
                'articles.title',
                'articles.slug',
                'categories.name'
            )
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();

        /*
         * Performances par catégorie.
         */
        $topCategories = ArticleView::query()
            ->join(
                'articles',
                'articles.id',
                '=',
                'article_views.article_id'
            )
            ->join(
                'categories',
                'categories.id',
                '=',
                'articles.category_id'
            )
            ->whereBetween('article_views.viewed_at', [
                $startDate,
                $endDate,
            ])
            ->select([
                'categories.id',
                'categories.name',
            ])
            ->selectRaw('COUNT(article_views.id) as total_views')
            ->selectRaw(
                'COUNT(DISTINCT articles.id) as articles_count'
            )
            ->groupBy(
                'categories.id',
                'categories.name'
            )
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();

        /*
         * Performances par auteur.
         */
        $topAuthors = ArticleView::query()
            ->join(
                'articles',
                'articles.id',
                '=',
                'article_views.article_id'
            )
            ->join(
                'users',
                'users.id',
                '=',
                'articles.author_id'
            )
            ->whereBetween('article_views.viewed_at', [
                $startDate,
                $endDate,
            ])
            ->select([
                'users.id',
                'users.name',
            ])
            ->selectRaw('COUNT(article_views.id) as total_views')
            ->selectRaw(
                'COUNT(DISTINCT articles.id) as articles_count'
            )
            ->groupBy(
                'users.id',
                'users.name'
            )
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();

        /*
         * Répartition par type d’appareil.
         */
        $deviceStatistics = ArticleView::query()
            ->whereBetween('viewed_at', [
                $startDate,
                $endDate,
            ])
            ->select(
                DB::raw(
                    "COALESCE(device_type, 'inconnu') as device"
                )
            )
            ->selectRaw('COUNT(*) as total')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get();

        /*
         * Répartition par navigateur.
         */
        $browserStatistics = ArticleView::query()
            ->whereBetween('viewed_at', [
                $startDate,
                $endDate,
            ])
            ->select(
                DB::raw(
                    "COALESCE(browser, 'Inconnu') as browser_name"
                )
            )
            ->selectRaw('COUNT(*) as total')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return view('admin.statistics.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalViews' => $totalViews,
            'uniqueVisitors' => $uniqueVisitors,
            'totalArticles' => $totalArticles,
            'publishedArticles' => $publishedArticles,
            'articlesPublishedDuringPeriod' => $articlesPublishedDuringPeriod,
            'newSubscribers' => $newSubscribers,
            'newComments' => $newComments,
            'averageViewsPerArticle' => $averageViewsPerArticle,
            'chartLabels' => $chartLabels,
            'chartViews' => $chartViews,
            'topArticles' => $topArticles,
            'topCategories' => $topCategories,
            'topAuthors' => $topAuthors,
            'deviceStatistics' => $deviceStatistics,
            'browserStatistics' => $browserStatistics,
        ]);
    }
}