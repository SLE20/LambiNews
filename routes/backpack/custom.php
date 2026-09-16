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
    Route::get('statistiques', [StatisticsController::class, 'index'])
    ->name('admin.statistics');
});