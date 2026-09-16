<?php

namespace App\Http\Controllers\Admin;

use App\Models\Poll;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PollCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(Poll::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/poll');
        CRUD::setEntityNameStrings('sondage', 'sondages');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'question', 'label' => 'Question']);

        CRUD::addColumn([
            'name'          => 'status',
            'label'         => 'État',
            'type'          => 'model_function',
            'function_name' => 'getStatusLabel',
        ]);

        CRUD::addColumn([
            'name'          => 'votes',
            'label'         => 'Participation',
            'type'          => 'model_function',
            'function_name' => 'getVotesSummary',
        ]);

        CRUD::addColumn(['name' => 'created_at', 'label' => 'Créé le', 'type' => 'datetime']);

        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'question'  => 'required|string|max:200',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        CRUD::addField([
            'name'  => 'question',
            'label' => 'Question posée aux lecteurs',
            'type'  => 'text',
            'tab'   => 'Sondage',
        ]);

        CRUD::addField([
            'name'  => 'description',
            'label' => 'Précision (facultatif)',
            'type'  => 'textarea',
            'tab'   => 'Sondage',
        ]);

        CRUD::addField([
            'name'    => 'layout',
            'label'   => 'Présentation',
            'type'    => 'select_from_array',
            'options' => [
                'standard' => 'Bloc simple (colonne d’article, liste)',
                'showcase' => 'Affiche pleine page (bandeau + cartes + graphiques)',
            ],
            'default' => 'standard',
            'tab'     => 'Affiche',
        ]);

        CRUD::addField([
            'name'  => 'headline',
            'label' => 'Titre du bandeau',
            'type'  => 'text',
            'hint'  => 'Ex. « Sondaj Prézidansyèl 2026 ». Le dernier mot '
                .'s’affiche en rouge. Vide = la question est utilisée.',
            'tab'   => 'Affiche',
        ]);

        CRUD::addField([
            'name'    => 'eyebrow',
            'label'   => 'Surtitre',
            'type'    => 'text',
            'hint'    => 'Petite ligne au-dessus du titre. Ex. « Votre avis compte ».',
            'tab'     => 'Affiche',
        ]);

        CRUD::addField([
            'name'  => 'subtitle',
            'label' => 'Phrase d’accroche',
            'type'  => 'text',
            'hint'  => 'Affichée sous la question, dans le bandeau.',
            'tab'   => 'Affiche',
        ]);

        CRUD::addField([
            'name'      => 'hero_image',
            'label'     => 'Image de fond du bandeau',
            'type'      => 'upload',
            'withFiles' => ['disk' => 'public', 'path' => 'polls'],
            'hint'      => 'Format paysage, au moins 1600 px de large.',
            'tab'       => 'Affiche',
        ]);

        CRUD::addField([
            'tab'     => 'Sondage',
            'name'    => 'starts_at',
            'label'   => 'Ouverture',
            'type'    => 'date',
            'hint'    => 'Vide = ouvert tout de suite.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'tab'     => 'Sondage',
            'name'    => 'ends_at',
            'label'   => 'Clôture',
            'type'    => 'date',
            'hint'    => 'Vide = sans date de fin.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'tab'     => 'Sondage',
            'name'    => 'is_active',
            'label'   => 'Actif',
            'type'    => 'checkbox',
            'default' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'tab'     => 'Sondage',
            'name'    => 'hide_results_before_vote',
            'label'   => 'Cacher les résultats avant le vote',
            'type'    => 'checkbox',
            'hint'    => 'Évite d’influencer le lecteur avant qu’il choisisse.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'tab'   => 'Sondage',
            'name'  => 'options_help',
            'type'  => 'custom_html',
            'value' => '<div class="alert alert-info mb-0">Après avoir '
                .'enregistré le sondage, ajoutez ses choix dans '
                .'<strong>Choix de sondage</strong>.</div>',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
