<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class CategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);
        CRUD::setModel(Category::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/category');
        CRUD::setEntityNameStrings('rubrique', 'rubriques');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Nom',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'parent',
            'label' => 'Rubrique parente',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);

        CRUD::addColumn([
            'name' => 'position',
            'label' => 'Position',
            'type' => 'number',
        ]);

        CRUD::addColumn([
            'name' => 'is_active',
            'label' => 'Active',
            'type' => 'boolean',
            'options' => [
                0 => 'Non',
                1 => 'Oui',
            ],
        ]);

        CRUD::orderBy('position');
        CRUD::orderBy('name');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(CategoryRequest::class);

        CRUD::addField([
            'name' => 'name',
            'label' => 'Nom de la rubrique',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'slug',
            'label' => 'Adresse URL',
            'type' => 'text',
            'hint' => 'Laissez vide pour la générer automatiquement.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'parent_id',
            'label' => 'Rubrique parente',
            'type' => 'select',
            'entity' => 'parent',
            'model' => Category::class,
            'attribute' => 'name',
            'allows_null' => true,
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
        ]);

        CRUD::addField([
            'name' => 'color',
            'label' => 'Couleur',
            'type' => 'color',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'icon',
            'label' => 'Icône',
            'type' => 'text',
            'hint' => 'Exemple : la-newspaper',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'position',
            'label' => 'Position',
            'type' => 'number',
            'default' => 0,
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'is_active',
            'label' => 'Rubrique active',
            'type' => 'checkbox',
            'default' => true,
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}