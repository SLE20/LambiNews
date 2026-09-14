<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function show(string $slug): View
    {
        $author = User::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereIn(
                'role',
                ['admin', 'editor', 'author']
            )
            ->firstOrFail();

        $articles = $author->articles()
            ->published()
            ->with([
                'category',
                'author',
            ])
            ->latest('published_at')
            ->paginate(12);

        return view(
            'front.authors.show',
            compact(
                'author',
                'articles'
            )
        );
    }
}