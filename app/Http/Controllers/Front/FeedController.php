<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function index(): Response
    {
        $articles = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(30)
            ->get();

        return response()
            ->view('front.feed', compact('articles'))
            ->header(
                'Content-Type',
                'application/rss+xml; charset=UTF-8'
            );
    }
}