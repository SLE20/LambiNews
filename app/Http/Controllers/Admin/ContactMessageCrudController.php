<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ContactMessageRequest;
use App\Models\ContactMessage;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ContactMessageCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        abort_unless(
            backpack_user()?->isEditor(),
            403
        );

        CRUD::setModel(ContactMessage::class);

        CRUD::setRoute(
            config('backpack.base.route_prefix').'/contact-message'
        );

        CRUD::setEntityNameStrings(
            'message',
            'messages'
        );
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Expéditeur',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'email',
            'label' => 'Courriel',
            'type' => 'email',
        ]);

        CRUD::addColumn([
            'name' => 'subject',
            'label' => 'Sujet',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'message',
            'label' => 'Message',
            'type' => 'text',
            'limit' => 100,
        ]);

        CRUD::addColumn([
            'name' => 'status',
            'label' => 'Statut',
            'type' => 'select_from_array',
            'options' => $this->statusOptions(),
        ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'label' => 'Reçu le',
            'type' => 'datetime',
        ]);

        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupShowOperation(): void
    {
        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Nom',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'email',
            'label' => 'Adresse courriel',
            'type' => 'email',
        ]);

        CRUD::addColumn([
            'name' => 'phone',
            'label' => 'Téléphone',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'subject',
            'label' => 'Sujet',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'message',
            'label' => 'Message',
            'type' => 'textarea',
        ]);

        CRUD::addColumn([
            'name' => 'status',
            'label' => 'Statut',
            'type' => 'select_from_array',
            'options' => $this->statusOptions(),
        ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'label' => 'Reçu le',
            'type' => 'datetime',
        ]);

        CRUD::addColumn([
            'name' => 'read_at',
            'label' => 'Lu le',
            'type' => 'datetime',
        ]);

        CRUD::addColumn([
            'name' => 'answered_at',
            'label' => 'Répondu le',
            'type' => 'datetime',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(ContactMessageRequest::class);

        CRUD::addField([
            'name' => 'name',
            'label' => 'Nom',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);

        CRUD::addField([
            'name' => 'email',
            'label' => 'Adresse courriel',
            'type' => 'email',
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);

        CRUD::addField([
            'name' => 'phone',
            'label' => 'Téléphone',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);

        CRUD::addField([
            'name' => 'subject',
            'label' => 'Sujet',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);

        CRUD::addField([
            'name' => 'message',
            'label' => 'Message',
            'type' => 'textarea',
            'attributes' => [
                'readonly' => 'readonly',
                'rows' => 10,
            ],
        ]);

        CRUD::addField([
            'name' => 'status',
            'label' => 'Statut',
            'type' => 'select_from_array',
            'options' => $this->statusOptions(),
        ]);
    }

    private function statusOptions(): array
    {
        return [
            'unread' => 'Non lu',
            'read' => 'Lu',
            'answered' => 'Répondu',
            'archived' => 'Archivé',
        ];
    }
}