<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        /*
         * Recherche de la rubrique.
         */
        $category = Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        /*
         * IDs :
         * - rubrique actuelle
         * - sous-rubriques actives
         */
        $categoryIds = $category
            ->activeChildren()
            ->pluck('id')
            ->push($category->id)
            ->unique()
            ->values();

        /*
         * Articles publiés appartenant
         * à la rubrique ou à ses sous-rubriques.
         */
        $articles = Article::query()
            ->published()
            ->with([
                'category',
                'author',
            ])
            ->whereIn(
                'category_id',
                $categoryIds
            )
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view(
            'front.categories.show',
            [
                'category' => $category,
                'articles' => $articles,
            ]
        );
    }
}