<?php

namespace App\Http\Controllers\Admin;

use App\Models\ElectionEvent;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ElectionEventCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(ElectionEvent::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/election-event');
        CRUD::setEntityNameStrings('échéance', 'calendrier électoral');
        CRUD::setSubheading('Une échéance n’apparaît sur le site qu’une fois cochée « Publiée ». '
            .'Chaque changement de date ou d’état est archivé et montré aux lecteurs.');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'starts_on', 'label' => 'Début', 'type' => 'date']);
        CRUD::addColumn(['name' => 'ends_on', 'label' => 'Fin', 'type' => 'date']);
        CRUD::addColumn(['name' => 'title', 'label' => 'Échéance', 'limit' => 70]);
        CRUD::addColumn(['name' => 'status', 'label' => 'État', 'type' => 'select_from_array', 'options' => ElectionEvent::STATUSES]);
        CRUD::addColumn(['name' => 'verified_on', 'label' => 'Vérifié le', 'type' => 'date']);
        CRUD::addColumn(['name' => 'publication', 'label' => 'Publication', 'type' => 'model_function', 'function_name' => 'getPublicationLabel']);
        CRUD::orderBy('starts_on');
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();
        CRUD::addColumn(['name' => 'description', 'label' => 'Précisions']);
        CRUD::addColumn(['name' => 'source_label', 'label' => 'Source']);
        CRUD::addColumn(['name' => 'source_url', 'label' => 'Lien source', 'type' => 'url']);
        CRUD::addColumn([
            'name'     => 'history',
            'label'    => 'Historique',
            'type'     => 'closure',
            'escaped'  => false,
            'function' => fn ($e) => $e->revisions->map(fn ($r) => e($r->changed_at->format('d/m/Y H:i').' — '.$r->summary()))->implode('<br>') ?: '—',
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'title'       => 'required|string|max:255',
            'starts_on'   => 'required|date',
            'ends_on'     => 'nullable|date|after_or_equal:starts_on',
            'status'      => 'required|in:'.implode(',', array_keys(ElectionEvent::STATUSES)),
            'source_url'  => 'nullable|url|max:500',
            'verified_on' => 'nullable|date|required_if:is_published,1',
        ], [
            'verified_on.required_if' => 'Indiquez la date de vérification avant de publier.',
        ]);

        CRUD::addField(['name' => 'title', 'label' => 'Échéance', 'type' => 'text']);
        CRUD::addField(['name' => 'starts_on', 'label' => 'Date (ou début)', 'type' => 'date', 'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'ends_on', 'label' => 'Fin (si période)', 'type' => 'date', 'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'status', 'label' => 'État', 'type' => 'select_from_array', 'options' => ElectionEvent::STATUSES, 'default' => 'upcoming', 'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'description', 'label' => 'Précisions', 'type' => 'textarea', 'attributes' => ['rows' => 3]]);
        CRUD::addField(['name' => 'source_label', 'label' => 'Source', 'type' => 'text', 'hint' => 'Ex. « CEP — communiqué du 2 septembre 2026 ».', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'source_url', 'label' => 'Lien vers la source', 'type' => 'url', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'verified_on', 'label' => 'Vérifié le', 'type' => 'date', 'hint' => 'Date à laquelle la rédaction a contrôlé l’échéance sur la source.', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'is_published', 'label' => 'Publiée sur le site', 'type' => 'checkbox', 'wrapper' => ['class' => 'form-group col-md-6 pt-4']]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
