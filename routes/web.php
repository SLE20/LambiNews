<?php

use App\Http\Controllers\Front\AdClickController;
use App\Http\Controllers\Front\ArticleController;
use App\Http\Controllers\Front\AuthorController;
use App\Http\Controllers\Front\CategoryController;
use App\Http\Controllers\Front\CommentController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\DonationController;
use App\Http\Controllers\Front\FeedController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsletterController;
use App\Http\Controllers\Front\OgImageController;
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
| Clics publicitaires
|--------------------------------------------------------------------------
*/

Route::get('/reklam/{ad}/klik', AdClickController::class)
    ->name('ads.click');

/*
|--------------------------------------------------------------------------
| Sipò / donasyon (PayPal)
|--------------------------------------------------------------------------
*/

Route::get('/soutni', [DonationController::class, 'create'])
    ->name('donations.create');

Route::post('/soutni/kreye', [DonationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('donations.store');

Route::post('/soutni/konfime', [DonationController::class, 'capture'])
    ->middleware('throttle:10,1')
    ->name('donations.capture');

Route::post('/soutni/anile', [DonationController::class, 'cancel'])
    ->middleware('throttle:10,1')
    ->name('donations.cancel');

Route::get('/soutni/mesi/{reference}', [DonationController::class, 'thanks'])
    ->name('donations.thanks');

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

Route::get('/sitemap-news.xml', [SitemapController::class, 'news'])
    ->name('sitemap.news');

/*
|--------------------------------------------------------------------------
| ads.txt
|--------------------------------------------------------------------------
|
| Exigé par les régies programmatiques pour prouver qui a le droit de
| vendre l’inventaire du site. Sans ce fichier, AdSense finit par cesser
| de diffuser. Généré à partir de ADSENSE_PUBLISHER_ID.
|
*/

Route::get('/ads.txt', function () {
    $publisherId = config('services.adsense.publisher_id');

    abort_if(blank($publisherId), 404);

    return response(
        'google.com, '.$publisherId.', DIRECT, f08c47fec0942fa0',
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8']
    );
})->name('ads.txt');

Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin/',
        // Résultats de recherche : contenu mince, dupliqué par paramètre.
        'Disallow: /recherche',
        'Disallow: /*?q=',
        '',
        'Sitemap: '.route('sitemap'),
        'Sitemap: '.route('sitemap.news'),
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
| Image de partage (Open Graph)
|--------------------------------------------------------------------------
|
| 1200×630, le format attendu par Facebook, WhatsApp et LinkedIn.
| {version} est une empreinte de l’article : elle force les réseaux
| sociaux à régénérer l’aperçu quand le titre ou la photo changent.
|
*/

Route::get(
    '/og/{version}/{slug}.jpg',
    [OgImageController::class, 'show']
)
    ->where('version', '[a-f0-9]{10}')
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('og.image');

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