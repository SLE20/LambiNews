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
        'site-setting',
        'SiteSettingCrudController'
    );

    Route::get('statistiques', [StatisticsController::class, 'index'])
    ->name('admin.statistics');
});