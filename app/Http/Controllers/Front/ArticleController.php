<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleViewTracker;
use Illuminate\Contracts\View\View;

class ArticleController extends Controller
{
    public function show(
        Article $article,
        ArticleViewTracker $viewTracker
    ): View {
        abort_unless(
            $article->status === 'published'
            && (
                $article->published_at === null
                || $article->published_at->lte(now())
            ),
            404
        );

        /*
         * Enregistre la consultation.
         * Les répétitions du même visiteur sont ignorées
         * pendant 30 minutes.
         */
        $viewTracker->record($article, request());

        $article->load([
            'category',
            'author',
            'tags',
        ]);

        /*
         * Commentaires approuvés.
         */
        $comments = $article->comments()
            ->where('status', 'approved')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /*
         * Articles similaires de la même catégorie.
         */
        $relatedArticles = Article::query()
            ->with([
                'category',
                'author',
            ])
            ->whereKeyNot($article->id)
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere(
                        'published_at',
                        '<=',
                        now()
                    );
            })
            ->when(
                $article->category_id,
                function ($query) use ($article): void {
                    $query->where(
                        'category_id',
                        $article->category_id
                    );
                }
            )
            ->latest('published_at')
            ->limit(4)
            ->get();

        /*
         * Articles les plus lus.
         *
         * On exclut l'article actuellement consulté.
         */
        $popularArticles = Article::query()
            ->with([
                'category',
                'author',
            ])
            ->whereKeyNot($article->id)
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere(
                        'published_at',
                        '<=',
                        now()
                    );
            })
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('front.articles.show', [
            'article' => $article,
            'comments' => $comments,
            'relatedArticles' => $relatedArticles,
            'popularArticles' => $popularArticles,
        ]);
    }
}