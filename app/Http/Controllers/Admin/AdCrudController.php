<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ad;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class AdCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        CRUD::setModel(Ad::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/ad');
        CRUD::setEntityNameStrings('publicité', 'publicités');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'name', 'label' => 'Nom']);

        CRUD::addColumn(['name' => 'client_name', 'label' => 'Client']);

        CRUD::addColumn([
            'name'          => 'position',
            'label'         => 'Emplacement',
            'type'          => 'model_function',
            'function_name' => 'getPositionLabel',
        ]);

        CRUD::addColumn([
            'name'          => 'schedule',
            'label'         => 'État',
            'type'          => 'model_function',
            'function_name' => 'getScheduleLabel',
        ]);

        CRUD::addColumn([
            'name'          => 'performance',
            'label'         => 'Performance',
            'type'          => 'model_function',
            'function_name' => 'getPerformance',
        ]);

        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();

        CRUD::addColumn([
            'name'          => 'type',
            'label'         => 'Type',
            'type'          => 'model_function',
            'function_name' => 'getTypeLabel',
        ]);

        CRUD::addColumn(['name' => 'client_email', 'label' => 'Courriel du client']);
        CRUD::addColumn(['name' => 'target_url', 'label' => 'Lien', 'type' => 'url']);
        CRUD::addColumn(['name' => 'starts_at', 'label' => 'Début', 'type' => 'date']);
        CRUD::addColumn(['name' => 'ends_at', 'label' => 'Fin', 'type' => 'date']);
        CRUD::addColumn(['name' => 'weight', 'label' => 'Poids de rotation']);
        CRUD::addColumn(['name' => 'notes', 'label' => 'Notes']);

        CRUD::addColumn([
            'name'          => 'report_url',
            'label'         => 'Lien de rapport pour le client',
            'type'          => 'model_function',
            'function_name' => 'getReportUrlAttribute',
            'limit'         => 255,
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'name'         => 'required|string|max:150',
            'position'     => 'required|string|max:40',
            'type'         => 'required|string|max:20',
            'client_name'  => 'nullable|string|max:150',
            'client_email' => 'nullable|email|max:190',
            'target_url'   => 'nullable|url|max:500',
            'alt_text'     => 'nullable|string|max:190',
            'weight'       => 'nullable|integer|min:1|max:100',
            'starts_at'    => 'nullable|date',
            'ends_at'      => 'nullable|date|after_or_equal:starts_at',
        ]);

        CRUD::addField([
            'name'  => 'name',
            'label' => 'Nom interne',
            'type'  => 'text',
            'hint'  => 'Sert uniquement dans l’administration.',
        ]);

        CRUD::addField([
            'name'    => 'position',
            'label'   => 'Emplacement',
            'type'    => 'select_from_array',
            'options' => Ad::positions(),
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'type',
            'label'   => 'Type de publicité',
            'type'    => 'select_from_array',
            'options' => Ad::types(),
            'default' => Ad::TYPE_IMAGE,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'      => 'image',
            'label'     => 'Visuel (pour le type « Image »)',
            'type'      => 'upload',
            'withFiles' => [
                'disk' => 'public',
                'path' => 'ads',
            ],
            'hint' => 'Respectez le format indiqué dans l’emplacement choisi.',
        ]);

        CRUD::addField([
            'name'  => 'target_url',
            'label' => 'Lien de destination',
            'type'  => 'url',
            'hint'  => 'Où le visiteur arrive quand il clique.',
        ]);

        CRUD::addField([
            'name'  => 'alt_text',
            'label' => 'Texte alternatif',
            'type'  => 'text',
            'hint'  => 'Décrit l’image aux lecteurs d’écran et aux moteurs.',
        ]);

        CRUD::addField([
            'name'  => 'html_code',
            'label' => 'Code HTML (types « Code » et « Régie »)',
            'type'  => 'textarea',
            'attributes' => ['rows' => 6],
            'hint'  => 'Collez ici le script fourni par l’annonceur ou par AdSense.',
        ]);

        CRUD::addField([
            'name'    => 'client_name',
            'label'   => 'Nom du client',
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'client_email',
            'label'   => 'Courriel du client',
            'type'    => 'email',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'starts_at',
            'label'   => 'Début de diffusion',
            'type'    => 'date',
            'hint'    => 'Vide = démarre tout de suite.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'ends_at',
            'label'   => 'Fin de diffusion',
            'type'    => 'date',
            'hint'    => 'Vide = sans date de fin.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'weight',
            'label'   => 'Poids de rotation',
            'type'    => 'number',
            'default' => 1,
            'hint'    => 'Si plusieurs publicités visent le même emplacement, '
                .'celle de poids 3 s’affiche trois fois plus souvent que celle de poids 1.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'is_active',
            'label'   => 'Active',
            'type'    => 'checkbox',
            'default' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'  => 'notes',
            'label' => 'Notes internes',
            'type'  => 'textarea',
            'hint'  => 'Prix facturé, contact, conditions…',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
