<?php

namespace App\Http\Controllers\Admin;

use App\Models\PoliticalParty;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PoliticalPartyCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(PoliticalParty::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/political-party');
        CRUD::setEntityNameStrings('structure politique', 'structures politiques');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'campaign_number', 'label' => 'N°']);
        CRUD::addColumn(['name' => 'acronym', 'label' => 'Sigle']);
        CRUD::addColumn(['name' => 'name', 'label' => 'Nom', 'limit' => 70]);
        CRUD::addColumn([
            'name'     => 'answers_count',
            'label'    => 'Réponses',
            'type'     => 'closure',
            'function' => fn ($p) => $p->answers()->count(),
        ]);
        CRUD::addColumn(['name' => 'is_published', 'label' => 'Publié', 'type' => 'boolean']);
        CRUD::orderBy('campaign_number');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'name'            => 'required|string|max:255',
            'acronym'         => 'nullable|string|max:60',
            'campaign_number' => 'nullable|integer|min:1|max:999|unique:political_parties,campaign_number,'.(CRUD::getCurrentEntryId() ?: 'NULL'),
            'website'         => 'nullable|url|max:300',
        ]);

        CRUD::addField(['name' => 'name', 'label' => 'Nom', 'type' => 'text', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'acronym', 'label' => 'Sigle', 'type' => 'text', 'wrapper' => ['class' => 'form-group col-md-3']]);
        CRUD::addField(['name' => 'campaign_number', 'label' => 'Numéro de campagne (CEP)', 'type' => 'number', 'wrapper' => ['class' => 'form-group col-md-3']]);
        CRUD::addField(['name' => 'leader', 'label' => 'Dirigeant·e', 'type' => 'text', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'website', 'label' => 'Site web', 'type' => 'url', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'logo', 'label' => 'Logo', 'type' => 'upload', 'withFiles' => ['disk' => 'public', 'path' => 'parties']]);
        CRUD::addField(['name' => 'description', 'label' => 'Présentation factuelle', 'type' => 'textarea', 'attributes' => ['rows' => 4]]);
        CRUD::addField(['name' => 'slug', 'label' => 'Adresse (slug)', 'type' => 'text', 'hint' => 'Laisser vide pour la générer.']);
        CRUD::addField(['name' => 'is_published', 'label' => 'Publié', 'type' => 'checkbox', 'default' => true]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
