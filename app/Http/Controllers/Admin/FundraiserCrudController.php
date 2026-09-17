<?php

namespace App\Http\Controllers\Admin;

use App\Models\Fundraiser;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class FundraiserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(Fundraiser::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/fundraiser');
        CRUD::setEntityNameStrings('campagne', 'campagnes de financement');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'title', 'label' => 'Campagne']);
        CRUD::addColumn(['name' => 'beneficiary', 'label' => 'Bénéficiaire']);

        CRUD::addColumn([
            'name'          => 'progress',
            'label'         => 'Collecte',
            'type'          => 'model_function',
            'function_name' => 'getProgressLabel',
        ]);

        CRUD::addColumn([
            'name'          => 'status',
            'label'         => 'État',
            'type'          => 'model_function',
            'function_name' => 'getStatusLabel',
        ]);

        CRUD::addColumn(['name' => 'ends_at', 'label' => 'Fin', 'type' => 'date']);

        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();
        CRUD::addColumn(['name' => 'summary', 'label' => 'Résumé']);
        CRUD::addColumn(['name' => 'story', 'label' => 'Présentation']);
        CRUD::addColumn(['name' => 'contributions_count', 'label' => 'Contributions']);
        CRUD::addColumn(['name' => 'currency', 'label' => 'Devise']);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'title'       => 'required|string|max:160',
            'goal_amount' => 'required|numeric|min:1|max:10000000',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
        ]);

        CRUD::addField(['name' => 'title', 'label' => 'Titre de la campagne', 'type' => 'text']);

        CRUD::addField([
            'name'  => 'summary',
            'label' => 'Résumé',
            'type'  => 'text',
            'hint'  => 'Une phrase, affichée sur la carte de la liste.',
        ]);

        CRUD::addField([
            'name'       => 'story',
            'label'      => 'Présentation détaillée',
            'type'       => 'textarea',
            'attributes' => ['rows' => 10],
            'hint'       => 'Expliquez à quoi l’argent va servir. C’est ce qui décide les gens.',
        ]);

        CRUD::addField([
            'name'      => 'cover_image',
            'label'     => 'Image de couverture',
            'type'      => 'upload',
            'withFiles' => ['disk' => 'public', 'path' => 'fundraisers'],
        ]);

        CRUD::addField([
            'name'      => 'photo',
            'label'     => 'Photo (portrait)',
            'type'      => 'upload',
            'withFiles' => ['disk' => 'public', 'path' => 'fundraisers'],
            'hint'      => 'Le visage de la campagne : bénéficiaire, équipe, '
                          .'ou l’objet financé. Affiché en rond sur la carte.',
        ]);

        CRUD::addField([
            'name'  => 'beneficiary',
            'label' => 'Bénéficiaire',
            'type'  => 'text',
            'hint'  => 'Qui reçoit l’argent. À dire clairement au public.',
        ]);

        CRUD::addField([
            'name'       => 'goal_amount',
            'label'      => 'Objectif',
            'type'       => 'number',
            'attributes' => ['step' => '0.01'],
            'wrapper'    => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'currency',
            'label'   => 'Devise',
            'type'    => 'select_from_array',
            'options' => ['USD' => 'USD (PayPal et MonCash)', 'HTG' => 'HTG (MonCash seulement)'],
            'default' => 'USD',
            'hint'    => 'PayPal ne gère pas la gourde.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'starts_at',
            'label'   => 'Début',
            'type'    => 'date',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'ends_at',
            'label'   => 'Fin',
            'type'    => 'date',
            'hint'    => 'Vide = sans date de fin.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'status',
            'label'   => 'État',
            'type'    => 'select_from_array',
            'options' => [
                Fundraiser::STATUS_DRAFT  => 'Brouillon (invisible)',
                Fundraiser::STATUS_ACTIVE => 'En cours (accepte les dons)',
                Fundraiser::STATUS_CLOSED => 'Clôturée (visible, sans don)',
            ],
            'default' => Fundraiser::STATUS_DRAFT,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'is_featured',
            'label'   => 'Mettre en avant',
            'type'    => 'checkbox',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
