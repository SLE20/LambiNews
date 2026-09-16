<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        /*
         * Articles mis en avant.
         */
        $featuredArticles = Article::query()
            ->with([
                'category',
                'author',
            ])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')
            ->limit(5)
            ->get();

        /*
         * Si aucun article n’est marqué comme vedette,
         * les cinq publications les plus récentes sont utilisées.
         */
        if ($featuredArticles->isEmpty()) {
            $featuredArticles = Article::query()
                ->with([
                    'category',
                    'author',
                ])
                ->where('status', 'published')
                ->where(function ($query): void {
                    $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->latest('published_at')
                ->limit(5)
                ->get();
        }

        /*
         * Derniers articles avec pagination.
         */
        $latestArticles = Article::query()
            ->with([
                'category',
                'author',
            ])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')
            ->paginate(12);

        /*
         * Articles les plus populaires des 30 derniers jours.
         *
         * On utilise article_views pour mesurer les consultations
         * réellement enregistrées pendant la période.
         */
        $popularArticles = Article::query()
            ->with([
                'category',
                'author',
            ])
            ->withCount([
                'articleViews as recent_views_count' => function ($query): void {
                    $query->where(
                        'viewed_at',
                        '>=',
                        now()->subDays(30)
                    );
                },
            ])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('recent_views_count')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        /*
         * Sections par catégorie.
         */
        $categorySections = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        /*
         * Charge quatre publications pour chaque catégorie.
         */
        $categorySections->each(function (Category $category): void {
            /*
             * Rubrique et sous-rubriques : les articles sont le plus
             * souvent rangés dans une sous-rubrique, si bien qu'une
             * rubrique de premier niveau paraîtrait vide.
             */
            $categoryIds = $category->activeChildren()
                ->pluck('id')
                ->push($category->id)
                ->unique()
                ->values();

            $articles = Article::query()
                ->with([
                    'category',
                    'author',
                ])
                ->whereIn('category_id', $categoryIds)
                ->where('status', 'published')
                ->where(function ($query): void {
                    $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->latest('published_at')
                ->limit(4)
                ->get();

            $category->setRelation('articles', $articles);
        });

        /*
         * Supprime de la page les catégories qui ne possèdent
         * aucune publication.
         */
        $categorySections = $categorySections
            ->filter(function (Category $category): bool {
                return $category->articles->isNotEmpty();
            })
            ->values();

        return view('front.home', [
            'featuredArticles' => $featuredArticles,
            'latestArticles' => $latestArticles,
            'popularArticles' => $popularArticles,
            'categorySections' => $categorySections,
        ]);
    }
}