<?php

namespace App\Http\Controllers\Admin;

use App\Models\PartyAnswer;
use App\Models\PartyQuestion;
use App\Models\PoliticalParty;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Validation\Rule;

class PartyAnswerCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(PartyAnswer::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/party-answer');
        CRUD::setEntityNameStrings('réponse', 'réponses des partis');
        CRUD::setSubheading('Les réponses sont publiées telles que reçues, sans correction de fond.');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'political_party_id', 'label' => 'Structure', 'type' => 'select', 'entity' => 'party', 'attribute' => 'name', 'model' => PoliticalParty::class]);
        CRUD::addColumn(['name' => 'party_question_id', 'label' => 'Question', 'type' => 'select', 'entity' => 'question', 'attribute' => 'theme', 'model' => PartyQuestion::class]);
        CRUD::addColumn(['name' => 'answer', 'label' => 'Réponse', 'limit' => 80]);
        CRUD::addColumn(['name' => 'received_on', 'label' => 'Reçue le', 'type' => 'date']);
        CRUD::orderBy('updated_at', 'desc');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'political_party_id' => 'required|exists:political_parties,id',
            'party_question_id'  => [
                'required', 'exists:party_questions,id',
                Rule::unique('party_answers')
                    ->where('political_party_id', request('political_party_id'))
                    ->ignore(CRUD::getCurrentEntryId()),
            ],
            'answer'      => 'required|string|max:5000',
            'source_url'  => 'nullable|url|max:500',
            'received_on' => 'nullable|date',
        ], [
            'party_question_id.unique' => 'Cette structure a déjà une réponse à cette question : modifiez-la.',
        ]);

        CRUD::addField([
            'name'    => 'political_party_id',
            'label'   => 'Structure politique',
            'type'    => 'select_from_array',
            'options' => PoliticalParty::orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->displayName()])->all(),
        ]);
        CRUD::addField([
            'name'    => 'party_question_id',
            'label'   => 'Question',
            'type'    => 'select_from_array',
            'options' => PartyQuestion::orderBy('position')->get()->mapWithKeys(fn ($q) => [$q->id => $q->label])->all(),
        ]);
        CRUD::addField(['name' => 'answer', 'label' => 'Réponse (telle que reçue)', 'type' => 'textarea', 'attributes' => ['rows' => 7]]);
        CRUD::addField(['name' => 'received_on', 'label' => 'Reçue le', 'type' => 'date', 'wrapper' => ['class' => 'form-group col-md-6']]);
        CRUD::addField(['name' => 'source_url', 'label' => 'Lien (document, publication)', 'type' => 'url', 'wrapper' => ['class' => 'form-group col-md-6']]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
