<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $articles = Article::query()
            ->published()
            ->latest('updated_at')
            ->get([
                'slug',
                'updated_at',
            ]);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'slug',
                'updated_at',
            ]);

        return response()
            ->view(
                'front.sitemap',
                compact('articles', 'categories')
            )
            ->header(
                'Content-Type',
                'application/xml; charset=UTF-8'
            );
    }
}