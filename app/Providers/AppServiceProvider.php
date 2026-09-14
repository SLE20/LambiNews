<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Category;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Subscriber;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        app()->setLocale('fr');

        Paginator::useBootstrapFive();

        $this->composeFrontViews();
        $this->composeDashboard();
    }

    private function composeFrontViews(): void
    {
        View::composer('front.*', function ($view): void {
            $navigationCategories = Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('position')
                ->orderBy('name')
                ->get();

            $breakingArticles = Article::query()
                ->published()
                ->where('is_breaking', true)
                ->latest('published_at')
                ->limit(3)
                ->get([
                    'id',
                    'title',
                    'slug',
                    'published_at',
                ]);

            $footerPages = Page::query()
                ->where('is_active', true)
                ->where('show_in_footer', true)
                ->orderBy('position')
                ->orderBy('title')
                ->get([
                    'id',
                    'title',
                    'slug',
                ]);

            $view->with([
                'navigationCategories' => $navigationCategories,
                'breakingArticles' => $breakingArticles,
                'footerPages' => $footerPages,
            ]);
        });
    }

    private function composeDashboard(): void
    {
        View::composer(backpack_view('dashboard'), function ($view): void {
            $user = backpack_user();

            if (! $user) {
                return;
            }

            $isAuthor = $user->role === 'author';

            $canManageEditorial = in_array(
                $user->role,
                ['admin', 'editor'],
                true
            );

            $isAdmin = $user->role === 'admin';

            /*
             * Requête principale des articles.
             * Un auteur ne voit que ses propres résultats.
             */
            $articleQuery = Article::query();

            if ($isAuthor) {
                $articleQuery->where('author_id', $user->id);
            }

            /*
             * Requête des consultations.
             * Pour un auteur, elle est limitée à ses articles.
             */
            $articleViewQuery = ArticleView::query();

            if ($isAuthor) {
                $articleViewQuery->whereHas(
                    'article',
                    function ($query) use ($user): void {
                        $query->where('author_id', $user->id);
                    }
                );
            }

            /*
             * Statistiques provenant réellement de la base.
             */
            $statistics = [
                'articles' => (clone $articleQuery)->count(),

                'published' => (clone $articleQuery)
                    ->where('status', 'published')
                    ->count(),

                'drafts' => (clone $articleQuery)
                    ->where('status', 'draft')
                    ->count(),

                'review' => (clone $articleQuery)
                    ->where('status', 'review')
                    ->count(),

                'scheduled' => (clone $articleQuery)
                    ->where('status', 'scheduled')
                    ->count(),

                'archived' => (clone $articleQuery)
                    ->where('status', 'archived')
                    ->count(),

                'total_views' => (clone $articleViewQuery)->count(),

                'views_today' => (clone $articleViewQuery)
                    ->whereBetween('viewed_at', [
                        now()->startOfDay(),
                        now()->endOfDay(),
                    ])
                    ->count(),

                'views_30_days' => (clone $articleViewQuery)
                    ->where('viewed_at', '>=', now()->subDays(29)->startOfDay())
                    ->count(),

                'unique_visitors_30_days' => (clone $articleViewQuery)
                    ->where('viewed_at', '>=', now()->subDays(29)->startOfDay())
                    ->distinct('visitor_hash')
                    ->count('visitor_hash'),

                'pending_comments' => $canManageEditorial
                    ? Comment::query()
                        ->where('status', 'pending')
                        ->count()
                    : 0,

                'unread_messages' => $canManageEditorial
                    ? ContactMessage::query()
                        ->where('status', 'unread')
                        ->count()
                    : 0,

                'subscribers' => $isAdmin
                    ? Subscriber::query()
                        ->where('is_active', true)
                        ->count()
                    : 0,
            ];

            /*
             * Articles ajoutés récemment.
             */
            $recentArticles = (clone $articleQuery)
                ->with([
                    'category',
                    'author',
                ])
                ->latest('created_at')
                ->limit(8)
                ->get();

            /*
             * Articles les plus consultés sur les 30 derniers jours.
             */
            $topArticlesQuery = Article::query()
                ->with([
                    'category',
                    'author',
                ])
                ->withCount([
                    'articleViews as period_views_count' => function (
                        $query
                    ): void {
                        $query->where(
                            'viewed_at',
                            '>=',
                            now()->subDays(29)->startOfDay()
                        );
                    },
                ])
                ->where('status', 'published');

            if ($isAuthor) {
                $topArticlesQuery->where(
                    'author_id',
                    $user->id
                );
            }

            $topArticles = $topArticlesQuery
                ->orderByDesc('period_views_count')
                ->orderByDesc('published_at')
                ->limit(5)
                ->get();

            /*
             * Consultations quotidiennes des 30 derniers jours.
             */
            $viewsByDayQuery = ArticleView::query()
                ->selectRaw('DATE(viewed_at) as view_date')
                ->selectRaw('COUNT(*) as total')
                ->where(
                    'viewed_at',
                    '>=',
                    now()->subDays(29)->startOfDay()
                );

            if ($isAuthor) {
                $viewsByDayQuery->whereHas(
                    'article',
                    function ($query) use ($user): void {
                        $query->where('author_id', $user->id);
                    }
                );
            }

            $viewsGroupedByDay = $viewsByDayQuery
                ->groupByRaw('DATE(viewed_at)')
                ->orderByRaw('DATE(viewed_at)')
                ->pluck('total', 'view_date');

            $chartLabels = [];
            $chartViews = [];

            $period = CarbonPeriod::create(
                now()->subDays(29)->startOfDay(),
                now()->startOfDay()
            );

            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');

                $chartLabels[] = $date->format('d/m');

                $chartViews[] = (int) (
                    $viewsGroupedByDay[$dateKey] ?? 0
                );
            }

            /*
             * Informations supplémentaires pour adapter
             * l’affichage selon le rôle.
             */
            $dashboardPermissions = [
                'is_author' => $isAuthor,
                'can_manage_editorial' => $canManageEditorial,
                'is_admin' => $isAdmin,
            ];

            $view->with([
                'statistics' => $statistics,
                'recentArticles' => $recentArticles,
                'topArticles' => $topArticles,
                'chartLabels' => $chartLabels,
                'chartViews' => $chartViews,
                'dashboardPermissions' => $dashboardPermissions,
            ]);
        });
    }
}