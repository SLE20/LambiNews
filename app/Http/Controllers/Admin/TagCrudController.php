<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class TagCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);
        CRUD::setModel(Tag::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/tag');
        CRUD::setEntityNameStrings('mot-clé', 'mots-clés');
    }

    protected function setupListOperation(): void
{
    CRUD::addColumn([
        'name' => 'name',
        'label' => 'Nom',
        'type' => 'text',
    ]);

    CRUD::addColumn([
        'name' => 'slug',
        'label' => 'Adresse URL',
        'type' => 'text',
    ]);

    CRUD::orderBy('name');
}

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(TagRequest::class);

        CRUD::addField([
            'name' => 'name',
            'label' => 'Nom',
            'type' => 'text',
        ]);

        CRUD::addField([
            'name' => 'slug',
            'label' => 'Adresse URL',
            'type' => 'text',
            'hint' => 'Laissez vide pour la générer automatiquement.',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}