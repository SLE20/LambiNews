<?php

namespace App\Http\Controllers\Admin;

use App\Models\Poll;
use App\Models\PollOption;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PollOptionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(PollOption::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/poll-option');
        CRUD::setEntityNameStrings('choix de sondage', 'choix de sondage');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name'      => 'poll_id',
            'label'     => 'Sondage',
            'type'      => 'select',
            'entity'    => 'poll',
            'attribute' => 'question',
            'model'     => Poll::class,
        ]);

        CRUD::addColumn(['name' => 'label', 'label' => 'Choix']);
        CRUD::addColumn(['name' => 'position', 'label' => 'Ordre']);
        CRUD::addColumn(['name' => 'votes_count', 'label' => 'Voix']);

        CRUD::orderBy('poll_id', 'desc');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'poll_id' => 'required|integer|exists:polls,id',
            'label'   => 'required|string|max:150',
        ]);

        CRUD::addField([
            'name'    => 'poll_id',
            'label'   => 'Sondage',
            'type'    => 'select_from_array',
            'options' => Poll::query()
                ->orderByDesc('created_at')
                ->pluck('question', 'id')
                ->all(),
        ]);

        CRUD::addField(['name' => 'label', 'label' => 'Choix', 'type' => 'text']);

        CRUD::addField([
            'name'  => 'subtitle',
            'label' => 'Précision (parti, fonction…)',
            'type'  => 'text',
        ]);

        CRUD::addField([
            'name'      => 'image',
            'label'     => 'Photo (facultative)',
            'type'      => 'upload',
            'withFiles' => ['disk' => 'public', 'path' => 'polls'],
        ]);

        CRUD::addField([
            'name'    => 'position',
            'label'   => 'Ordre d’affichage',
            'type'    => 'number',
            'default' => 0,
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
