<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StatisticsController;

Route::group([
    'prefix' => config(
        'backpack.base.route_prefix',
        'admin'
    ),

    'middleware' => [
        'web',
        config(
            'backpack.base.middleware_key',
            'admin'
        ),
    ],

    'namespace' => 'App\Http\Controllers\Admin',
], function () {
    Route::crud(
        'article',
        'ArticleCrudController'
    );

    Route::crud(
        'category',
        'CategoryCrudController'
    );

    Route::crud(
        'tag',
        'TagCrudController'
    );

    Route::crud(
        'page',
        'PageCrudController'
    );

    Route::crud(
        'comment',
        'CommentCrudController'
    );

    Route::crud(
        'contact-message',
        'ContactMessageCrudController'
    );

    Route::crud(
        'user',
        'UserCrudController'
    );

    Route::crud(
        'subscriber',
        'SubscriberCrudController'
    );

    Route::crud(
        'donation',
        'DonationCrudController'
    );

    Route::crud(
        'ad',
        'AdCrudController'
    );

    Route::crud(
        'announcement',
        'AnnouncementCrudController'
    );

    Route::crud(
        'poll',
        'PollCrudController'
    );

    Route::crud(
        'poll-option',
        'PollOptionCrudController'
    );

    // Remet un sondage à zéro après une phase d'essai.
    Route::get(
        'poll/{id}/reset',
        [\App\Http\Controllers\Admin\PollCrudController::class, 'reset']
    )->name('poll.reset');

    Route::crud(
        'newsletter-campaign',
        'NewsletterCampaignCrudController'
    );

    // Déclenche l'envoi d'une infolettre préparée.
    Route::get(
        'newsletter-campaign/{id}/queue',
        [\App\Http\Controllers\Admin\NewsletterCampaignCrudController::class, 'queue']
    )->name('newsletter-campaign.queue');

    Route::get(
        'newsletter-campaign/{id}/retry',
        [\App\Http\Controllers\Admin\NewsletterCampaignCrudController::class, 'retry']
    )->name('newsletter-campaign.retry');
    Route::crud(
        'fundraiser',
        'FundraiserCrudController'
    );

    Route::crud(
        'fundraiser-contribution',
        'FundraiserContributionCrudController'
    );

    Route::crud(
        'site-setting',
        'SiteSettingCrudController'
    );

    // Vérifie les clés PayPal enregistrées.
    Route::get(
        'site-setting/test-paypal',
        [\App\Http\Controllers\Admin\SiteSettingCrudController::class, 'testPaypal']
    )->name('site-setting.test-paypal');

    // Vérifie la clé WalCash Pay (MonCash).
    Route::get(
        'site-setting/test-moncash',
        [\App\Http\Controllers\Admin\SiteSettingCrudController::class, 'testMonCash']
    )->name('site-setting.test-moncash');

    Route::get('statistiques', [StatisticsController::class, 'index'])
    ->name('admin.statistics');

    // Revenus : dons, annonces, votes payants, campagnes, publicité.
    Route::get('revenus', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])
        ->name('admin.revenue');
});