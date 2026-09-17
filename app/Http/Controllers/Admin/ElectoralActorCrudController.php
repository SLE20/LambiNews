<?php

namespace App\Http\Controllers\Admin;

use App\Models\ElectoralActor;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ElectoralActorCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(ElectoralActor::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/electoral-actor');
        CRUD::setEntityNameStrings('acteur', 'acteurs du processus');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'position', 'label' => '#']);
        CRUD::addColumn(['name' => 'icon', 'label' => ' ']);
        CRUD::addColumn(['name' => 'name', 'label' => 'Acteur']);
        CRUD::addColumn(['name' => 'summary', 'label' => 'Résumé', 'limit' => 80]);
        CRUD::addColumn(['name' => 'is_published', 'label' => 'Publié', 'type' => 'boolean']);
        CRUD::orderBy('position');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'name'       => 'required|string|max:255',
            'summary'    => 'required|string|max:300',
            'role'       => 'required|string',
            'source_url' => 'nullable|url|max:500',
        ]);

        $lines = 'Un élément par ligne.';

        CRUD::addField(['name' => 'name', 'label' => 'Nom', 'type' => 'text', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'slug', 'label' => 'Adresse (slug)', 'type' => 'text', 'hint' => 'Laisser vide pour la générer.', 'wrapper' => ['class' => 'form-group col-md-3']]);
        CRUD::addField(['name' => 'icon', 'label' => 'Emoji', 'type' => 'text', 'wrapper' => ['class' => 'form-group col-md-1']]);
        CRUD::addField(['name' => 'position', 'label' => 'Ordre', 'type' => 'number', 'wrapper' => ['class' => 'form-group col-md-2']]);
        CRUD::addField(['name' => 'summary', 'label' => 'Résumé (carte)', 'type' => 'text']);
        CRUD::addField(['name' => 'role', 'label' => 'Rôle dans le processus', 'type' => 'textarea', 'attributes' => ['rows' => 4]]);
        CRUD::addField(['name' => 'responsibilities', 'label' => 'Responsabilités', 'type' => 'textarea', 'hint' => $lines, 'attributes' => ['rows' => 5]]);
        CRUD::addField(['name' => 'moments', 'label' => 'Moments d’intervention', 'type' => 'textarea', 'hint' => $lines, 'attributes' => ['rows' => 3]]);
        CRUD::addField(['name' => 'watch_points', 'label' => 'Points de vigilance', 'type' => 'textarea', 'hint' => $lines, 'attributes' => ['rows' => 3]]);
        CRUD::addField(['name' => 'source_label', 'label' => 'Source', 'type' => 'text', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'source_url', 'label' => 'Lien source', 'type' => 'url', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'is_published', 'label' => 'Publié', 'type' => 'checkbox', 'default' => true]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
