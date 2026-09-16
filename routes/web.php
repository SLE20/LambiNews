<?php

use App\Http\Controllers\Front\AdClickController;
use App\Http\Controllers\Front\AdReportController;
use App\Http\Controllers\Front\AnnouncementController;
use App\Http\Controllers\Front\ArticleController;
use App\Http\Controllers\Front\AuthorController;
use App\Http\Controllers\Front\CategoryController;
use App\Http\Controllers\Front\CommentController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\DonationController;
use App\Http\Controllers\Front\FeedController;
use App\Http\Controllers\Front\FundraiserController;
use App\Http\Controllers\Front\WalCashWebhookController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\MediaKitController;
use App\Http\Controllers\Front\NewsletterController;
use App\Http\Controllers\Front\OgImageController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Front\PollController;
use App\Http\Controllers\Front\SearchController;
use App\Http\Controllers\Front\SitemapController;
use App\Http\Controllers\Front\ThumbnailController;
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
| Sondaj opinyon lektè
|--------------------------------------------------------------------------
*/

Route::get('/sondaj', [PollController::class, 'index'])
    ->name('polls.index');

Route::get('/sondaj/{slug}', [PollController::class, 'show'])
    ->name('polls.show');

Route::post('/sondaj/{slug}/vote', [PollController::class, 'vote'])
    ->middleware('throttle:20,1')
    ->name('polls.vote');

// Sondage payant : commande PayPal puis capture.
Route::post('/sondaj/{slug}/peman', [PollController::class, 'payStart'])
    ->middleware('throttle:15,1')
    ->name('polls.pay.start');

Route::post('/sondaj/{slug}/peman/konfime', [PollController::class, 'payCapture'])
    ->middleware('throttle:15,1')
    ->name('polls.pay.capture');

/*
|--------------------------------------------------------------------------
| Anons peye (avi lanmò, remèsiman, felisitasyon, biznis)
|--------------------------------------------------------------------------
*/

Route::get('/anons', [AnnouncementController::class, 'index'])
    ->name('announcements.index');

Route::get('/anons/pibliye', [AnnouncementController::class, 'create'])
    ->name('announcements.create');

Route::post('/anons/kreye', [AnnouncementController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('announcements.store');

Route::post('/anons/konfime', [AnnouncementController::class, 'capture'])
    ->middleware('throttle:10,1')
    ->name('announcements.capture');

Route::get('/anons/mesi/{reference}', [AnnouncementController::class, 'thanks'])
    ->name('announcements.thanks');

// En dernier : ce motif attraperait sinon /anons/pibliye.
Route::get('/anons/{slug}', [AnnouncementController::class, 'show'])
    ->name('announcements.show');

/*
|--------------------------------------------------------------------------
| Clics publicitaires
|--------------------------------------------------------------------------
*/

Route::get('/reklam/{ad}/klik', AdClickController::class)
    ->name('ads.click');

// Rapport de campagne, protégé par un jeton non devinable.
Route::get('/reklam/rapo/{token}', AdReportController::class)
    ->where('token', '[a-z0-9]{28}')
    ->name('ads.report');

// Dossier de presse : chiffres d’audience pour les annonceurs.
Route::get('/kit-medya', [MediaKitController::class, 'index'])
    ->name('media-kit');

/*
|--------------------------------------------------------------------------
| Kanpay finansman (GoFundMe)
|--------------------------------------------------------------------------
*/

Route::get('/kanpay', [FundraiserController::class, 'index'])
    ->name('fundraisers.index');

Route::get('/kanpay/mesi/{reference}', [FundraiserController::class, 'thanks'])
    ->name('fundraisers.thanks');

Route::get('/kanpay/moncash/{reference}', [FundraiserController::class, 'moncashReturn'])
    ->name('fundraisers.moncash.return');

Route::post('/kanpay/{slug}/kontribye', [FundraiserController::class, 'contribute'])
    ->middleware('throttle:10,1')
    ->name('fundraisers.contribute');

Route::post('/kanpay/{slug}/konfime', [FundraiserController::class, 'capture'])
    ->middleware('throttle:10,1')
    ->name('fundraisers.capture');

// En dernier : ce motif attraperait sinon /kanpay/mesi.
Route::get('/kanpay/{slug}', [FundraiserController::class, 'show'])
    ->name('fundraisers.show');

/*
|--------------------------------------------------------------------------
| Webhook WalCash Pay (MonCash)
|--------------------------------------------------------------------------
|
| Signé en HMAC-SHA256 ; c'est la seule source de vérité pour un
| paiement MonCash. Les deux adresses sont acceptées, la seconde parce
| que c'est celle qu'attend le tableau de bord WalCash.
|
*/

Route::post('/webhooks/walcash', WalCashWebhookController::class)
    ->name('webhooks.walcash');

Route::post('/webhooks/walcash.php', WalCashWebhookController::class)
    ->name('webhooks.walcash.php');

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

// GET et POST : Gmail appelle ce lien en POST (List-Unsubscribe-Post).
Route::match(
    ['get', 'post'],
    '/infolettre/dezabone/{token}',
    [NewsletterController::class, 'unsubscribe']
)
    ->where('token', '[a-z0-9]{32}')
    ->name('newsletter.unsubscribe');

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
        // Rapports de campagne : liens privés remis aux annonceurs.
        'Disallow: /reklam/',
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
| Vignettes d'articles
|--------------------------------------------------------------------------
|
| Les originaux pèsent deux à trois mégaoctets ; on ne les sert jamais
| dans une liste.
|
*/

Route::get('/vinyet/{width}/{slug}.jpg', ThumbnailController::class)
    ->where('width', '[0-9]{3,4}')
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('thumbnail');

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