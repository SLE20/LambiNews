<?php

use App\Http\Controllers\Front\ArticleController;
use App\Http\Controllers\Front\AuthorController;
use App\Http\Controllers\Front\CategoryController;
use App\Http\Controllers\Front\CommentController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\FeedController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsletterController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Front\SearchController;
use App\Http\Controllers\Front\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Page d’accueil
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| Recherche
|--------------------------------------------------------------------------
*/

Route::get('/recherche', [SearchController::class, 'index'])
    ->name('search');

/*
|--------------------------------------------------------------------------
| Contact
|--------------------------------------------------------------------------
*/

Route::get('/contact', [ContactController::class, 'create'])
    ->name('contact.create');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('contact.store');

/*
|--------------------------------------------------------------------------
| Newsletter
|--------------------------------------------------------------------------
*/

Route::post(
    '/infolettre/inscription',
    [NewsletterController::class, 'store']
)
    ->middleware('throttle:5,1')
    ->name('newsletter.store');

/*
|--------------------------------------------------------------------------
| RSS, sitemap et robots
|--------------------------------------------------------------------------
*/

Route::get('/flux-rss.xml', [FeedController::class, 'index'])
    ->name('feed');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])
    ->name('sitemap');

Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin/',
        'Sitemap: '.route('sitemap'),
    ]);

    return response($content, 200)
        ->header(
            'Content-Type',
            'text/plain; charset=UTF-8'
        );
})->name('robots');

/*
|--------------------------------------------------------------------------
| Auteurs
|--------------------------------------------------------------------------
*/

Route::get(
    '/auteurs/{slug}',
    [AuthorController::class, 'show']
)->name('authors.show');

/*
|--------------------------------------------------------------------------
| Catégories
|--------------------------------------------------------------------------
*/

Route::get(
    '/rubriques/{slug}',
    [CategoryController::class, 'show']
)->name('categories.show');

/*
|--------------------------------------------------------------------------
| Commentaires
|--------------------------------------------------------------------------
*/

Route::post(
    '/articles/{slug}/commentaires',
    [CommentController::class, 'store']
)
    ->middleware('throttle:3,1')
    ->name('comments.store');

/*
|--------------------------------------------------------------------------
| Articles
|--------------------------------------------------------------------------
|
| Le paramètre doit s’appeler "article", comme le paramètre
| Article $article du contrôleur. ":slug" indique à Laravel
| de rechercher l’article avec sa colonne slug.
|
*/

Route::get(
    '/articles/{article:slug}',
    [ArticleController::class, 'show']
)->name('articles.show');

/*
|--------------------------------------------------------------------------
| Pages institutionnelles
|--------------------------------------------------------------------------
*/

Route::get(
    '/pages/{slug}',
    [PageController::class, 'show']
)->name('pages.show');