<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\View\View;
use App\Models\Article;

class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $categoryIds = $category->children()
            ->where('is_active', true)
            ->pluck('id')
            ->push($category->id);

        $articles = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->whereIn('category_id', $categoryIds)
            ->latest('published_at')
            ->paginate(12);

        return view('front.categories.show', compact(
            'category',
            'articles',
        ));
    }
}