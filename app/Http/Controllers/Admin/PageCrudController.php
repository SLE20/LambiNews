<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PageRequest;
use App\Models\Page;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PageCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        abort_unless(
            backpack_user()?->isEditor(),
            403
        );

        CRUD::setModel(Page::class);

        CRUD::setRoute(
            config('backpack.base.route_prefix').'/page'
        );

        CRUD::setEntityNameStrings(
            'page',
            'pages'
        );
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name' => 'title',
            'label' => 'Titre',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'slug',
            'label' => 'Adresse URL',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'is_active',
            'label' => 'Active',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'show_in_footer',
            'label' => 'Pied de page',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'position',
            'label' => 'Position',
            'type' => 'number',
        ]);

        CRUD::orderBy('position');
        CRUD::orderBy('title');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(PageRequest::class);

        CRUD::addField([
            'name' => 'title',
            'label' => 'Titre',
            'type' => 'text',
        ]);

        CRUD::addField([
            'name' => 'slug',
            'label' => 'Adresse URL',
            'type' => 'text',
            'hint' => 'Laissez vide pour la générer automatiquement.',
        ]);

        CRUD::addField([
            'name' => 'content',
            'label' => 'Contenu',
            'type' => 'summernote',
        ]);

        CRUD::addField([
            'name' => 'is_active',
            'label' => 'Page active',
            'type' => 'checkbox',
            'default' => true,
        ]);

        CRUD::addField([
            'name' => 'show_in_footer',
            'label' => 'Afficher dans le pied de page',
            'type' => 'checkbox',
            'default' => false,
        ]);

        CRUD::addField([
            'name' => 'position',
            'label' => 'Position',
            'type' => 'number',
            'default' => 0,
        ]);

        CRUD::addField([
            'name' => 'meta_title',
            'label' => 'Titre SEO',
            'type' => 'text',
            'tab' => 'Référencement',
        ]);

        CRUD::addField([
            'name' => 'meta_description',
            'label' => 'Description SEO',
            'type' => 'textarea',
            'tab' => 'Référencement',
            'attributes' => [
                'rows' => 4,
                'maxlength' => 500,
            ],
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}