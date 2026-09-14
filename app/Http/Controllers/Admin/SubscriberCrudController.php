<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SubscriberRequest;
use App\Models\Subscriber;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class SubscriberCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        abort_unless(
            backpack_user()?->isAdmin(),
            403
        );

        CRUD::setModel(Subscriber::class);

        CRUD::setRoute(
            config('backpack.base.route_prefix').'/subscriber'
        );

        CRUD::setEntityNameStrings(
            'abonné',
            'abonnés'
        );
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name' => 'email',
            'label' => 'Adresse courriel',
            'type' => 'email',
        ]);

        CRUD::addColumn([
            'name' => 'is_active',
            'label' => 'Abonnement actif',
            'type' => 'boolean',
            'options' => [
                0 => 'Non',
                1 => 'Oui',
            ],
        ]);

        CRUD::addColumn([
            'name' => 'subscribed_at',
            'label' => 'Date d’inscription',
            'type' => 'datetime',
        ]);

        CRUD::addColumn([
            'name' => 'unsubscribed_at',
            'label' => 'Date de désinscription',
            'type' => 'datetime',
        ]);

        CRUD::orderBy('subscribed_at', 'desc');
    }

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(SubscriberRequest::class);

        CRUD::addField([
            'name' => 'email',
            'label' => 'Adresse courriel',
            'type' => 'email',
        ]);

        CRUD::addField([
            'name' => 'is_active',
            'label' => 'Abonnement actif',
            'type' => 'checkbox',
        ]);
    }
}