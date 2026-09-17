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
use App\Http\Controllers\Front\ElectionController;
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

/*
|--------------------------------------------------------------------------
| Élections 2026
|--------------------------------------------------------------------------
*/

Route::prefix('elections')->name('elections.')->controller(ElectionController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/calendrier', 'calendar')->name('calendar');
    Route::get('/cycle-electoral', 'cycle')->name('cycle');
    Route::get('/acteurs/{slug}', 'actor')->name('actor');
    Route::get('/ou-voter', 'whereToVote')->name('where');
    Route::get('/centres.json', 'centers')->middleware('throttle:60,1')->name('centers');
    Route::get('/partis', 'parties')->name('parties');
    Route::get('/partis/{slug}', 'party')->name('party');
});

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

Route::get('/sondages', [PollController::class, 'index'])
    ->name('polls.index');

// Urne publique d'un sondage clôturé (avant /sondages/{slug}).
Route::get('/sondages/{slug}/urne.csv', [PollController::class, 'ballots'])
    ->middleware('throttle:10,1')
    ->name('polls.ballots');

Route::get('/sondages/{slug}', [PollController::class, 'show'])
    ->name('polls.show');

Route::post('/sondages/{slug}/vote', [PollController::class, 'vote'])
    ->middleware('throttle:20,1')
    ->name('polls.vote');

// Sondage payant : commande PayPal puis capture.
Route::post('/sondages/{slug}/paiement', [PollController::class, 'payStart'])
    ->middleware('throttle:15,1')
    ->name('polls.pay.start');

Route::post('/sondages/{slug}/paiement/confirmer', [PollController::class, 'payCapture'])
    ->middleware('throttle:15,1')
    ->name('polls.pay.capture');

/*
|--------------------------------------------------------------------------
| Anons peye (avi lanmò, remèsiman, felisitasyon, biznis)
|--------------------------------------------------------------------------
*/

Route::get('/annonces', [AnnouncementController::class, 'index'])
    ->name('announcements.index');

Route::get('/annonces/publier', [AnnouncementController::class, 'create'])
    ->name('announcements.create');

Route::post('/annonces/creer', [AnnouncementController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('announcements.store');

Route::post('/annonces/confirmer', [AnnouncementController::class, 'capture'])
    ->middleware('throttle:10,1')
    ->name('announcements.capture');

Route::get('/annonces/merci/{reference}', [AnnouncementController::class, 'thanks'])
    ->name('announcements.thanks');

// En dernier : ce motif attraperait sinon /anons/pibliye.
Route::get('/annonces/{slug}', [AnnouncementController::class, 'show'])
    ->name('announcements.show');

/*
|--------------------------------------------------------------------------
| Clics publicitaires
|--------------------------------------------------------------------------
*/

Route::get('/publicite/{ad}/clic', AdClickController::class)
    ->name('ads.click');

// Rapport de campagne, protégé par un jeton non devinable.
Route::get('/publicite/rapport/{token}', AdReportController::class)
    ->where('token', '[a-z0-9]{28}')
    ->name('ads.report');

// Dossier de presse : chiffres d’audience pour les annonceurs.
Route::get('/kit-media', [MediaKitController::class, 'index'])
    ->name('media-kit');

/*
|--------------------------------------------------------------------------
| Kanpay finansman (GoFundMe)
|--------------------------------------------------------------------------
*/

Route::get('/campagnes', [FundraiserController::class, 'index'])
    ->name('fundraisers.index');

Route::get('/campagnes/merci/{reference}', [FundraiserController::class, 'thanks'])
    ->name('fundraisers.thanks');

Route::get('/campagnes/moncash/{reference}', [FundraiserController::class, 'moncashReturn'])
    ->name('fundraisers.moncash.return');

Route::post('/campagnes/{slug}/contribuer', [FundraiserController::class, 'contribute'])
    ->middleware('throttle:10,1')
    ->name('fundraisers.contribute');

Route::post('/campagnes/{slug}/confirmer', [FundraiserController::class, 'capture'])
    ->middleware('throttle:10,1')
    ->name('fundraisers.capture');

// En dernier : ce motif attraperait sinon /kanpay/mesi.
Route::get('/campagnes/{slug}', [FundraiserController::class, 'show'])
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

Route::get('/soutenir', [DonationController::class, 'create'])
    ->name('donations.create');

Route::post('/soutenir/creer', [DonationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('donations.store');

Route::post('/soutenir/confirmer', [DonationController::class, 'capture'])
    ->middleware('throttle:10,1')
    ->name('donations.capture');

Route::post('/soutenir/annuler', [DonationController::class, 'cancel'])
    ->middleware('throttle:10,1')
    ->name('donations.cancel');

Route::get('/soutenir/merci/{reference}', [DonationController::class, 'thanks'])
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
    '/infolettre/desabonnement/{token}',
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
        'Disallow: /publicite/',
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

Route::get('/vignettes/{width}/{slug}.jpg', ThumbnailController::class)
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

/*
|--------------------------------------------------------------------------
| Anciennes adresses en créole
|--------------------------------------------------------------------------
|
| Les adresses publiques sont passées en français. Les anciennes restent
| en service : liens déjà partagés, pages en cache, rapports envoyés aux
| annonceurs, liens de désinscription dans les infolettres déjà parties.
|
| Les pages redirigent en 301 pour transmettre leur référencement. Le
| clic publicitaire, la vignette et la désinscription sont servis
| directement : un clic ne doit pas coûter un aller-retour, et Gmail
| appelle la désinscription en POST, qu'une redirection casserait.
|
*/

Route::permanentRedirect('/sondaj', '/sondages');
Route::get('/sondaj/{slug}', fn (string $slug) => redirect()->route('polls.show', $slug, 301));

Route::permanentRedirect('/anons', '/annonces');
Route::permanentRedirect('/anons/pibliye', '/annonces/publier');
Route::get('/anons/mesi/{reference}', fn (string $reference) => redirect()->route('announcements.thanks', $reference, 301));
Route::get('/anons/{slug}', fn (string $slug) => redirect()->route('announcements.show', $slug, 301));

Route::permanentRedirect('/kanpay', '/campagnes');
Route::get('/kanpay/mesi/{reference}', fn (string $reference) => redirect()->route('fundraisers.thanks', $reference, 301));
Route::get('/kanpay/moncash/{reference}', fn (string $reference) => redirect()->route('fundraisers.moncash.return', $reference, 301));
Route::get('/kanpay/{slug}', fn (string $slug) => redirect()->route('fundraisers.show', $slug, 301));

Route::permanentRedirect('/soutni', '/soutenir');
Route::get('/soutni/mesi/{reference}', fn (string $reference) => redirect()->route('donations.thanks', $reference, 301));

Route::permanentRedirect('/kit-medya', '/kit-media');

Route::get('/reklam/{ad}/klik', AdClickController::class)->whereNumber('ad');
Route::get('/reklam/rapo/{token}', fn (string $token) => redirect()->route('ads.report', $token, 301));

Route::get('/vinyet/{width}/{slug}.jpg', ThumbnailController::class)
    ->where('width', '[0-9]{3,4}')
    ->where('slug', '[A-Za-z0-9\-_]+');

Route::match(['get', 'post'], '/infolettre/dezabone/{token}', [NewsletterController::class, 'unsubscribe'])
    ->where('token', '[a-z0-9]{32}');
