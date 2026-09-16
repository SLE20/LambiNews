<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Plan du site complet : articles, rubriques, auteurs et pages
     * institutionnelles.
     */
    public function index(): Response
    {
        $articles = Article::query()
            ->published()
            ->with('category')
            ->latest('updated_at')
            ->get([
                'id',
                'slug',
                'title',
                'featured_image',
                'updated_at',
            ]);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'slug',
                'updated_at',
            ]);

        /*
         * Auteurs ayant au moins un article publié : une page d’auteur vide
         * n’apporte rien à l’indexation.
         */
        $authors = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'editor', 'author'])
            ->whereHas('articles', fn ($query) => $query->published())
            ->orderBy('name')
            ->get([
                'slug',
                'updated_at',
            ]);

        $pages = Page::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get([
                'slug',
                'updated_at',
            ]);

        return $this->xml('front.sitemap', compact(
            'articles',
            'categories',
            'authors',
            'pages'
        ));
    }

    /**
     * Plan du site Google Actualités.
     *
     * Ce format n’accepte que les articles des deux derniers jours et se
     * limite à 1 000 entrées ; c’est ce qui permet une reprise rapide dans
     * l’onglet Actualités.
     */
    public function news(): Response
    {
        $articles = Article::query()
            ->published()
            // Google Actualités refuse le contenu sponsorisé.
            ->editorial()
            ->where('published_at', '>=', now()->subDays(2))
            ->latest('published_at')
            ->limit(1000)
            ->get([
                'slug',
                'title',
                'published_at',
            ]);

        return $this->xml('front.sitemap-news', compact('articles'));
    }

    /** @param array<string, mixed> $data */
    private function xml(string $view, array $data): Response
    {
        return response()
            ->view($view, $data)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
