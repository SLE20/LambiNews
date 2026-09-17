<?php

namespace App\Http\Controllers\Admin;

use App\Models\PartyQuestion;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PartyQuestionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(PartyQuestion::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/party-question');
        CRUD::setEntityNameStrings('question', 'questionnaire aux partis');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'position', 'label' => '#']);
        CRUD::addColumn(['name' => 'theme', 'label' => 'Thème']);
        CRUD::addColumn(['name' => 'question', 'label' => 'Question', 'limit' => 90]);
        CRUD::addColumn(['name' => 'is_active', 'label' => 'Active', 'type' => 'boolean']);
        CRUD::orderBy('position');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(['theme' => 'required|string|max:60', 'question' => 'required|string|max:400']);

        CRUD::addField(['name' => 'theme', 'label' => 'Thème', 'type' => 'text', 'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'position', 'label' => 'Ordre', 'type' => 'number', 'wrapper' => ['class' => 'form-group col-md-2']]);
        CRUD::addField(['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => true, 'wrapper' => ['class' => 'form-group col-md-6 pt-4']]);
        CRUD::addField(['name' => 'question', 'label' => 'Question', 'type' => 'textarea', 'attributes' => ['rows' => 3]]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
