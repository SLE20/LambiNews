<?php

namespace App\Http\Controllers\Admin;

use App\Models\Announcement;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Relecture des annonces payées.
 *
 * On ne crée pas d’annonce ici : elles arrivent par le formulaire public,
 * une fois payées. Le rôle de cet écran est d’accepter ou de refuser.
 */
class AnnouncementCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(Announcement::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/announcement');
        CRUD::setEntityNameStrings('annonce', 'annonces');

        CRUD::denyAccess(['create']);
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'created_at', 'label' => 'Reçue le', 'type' => 'datetime']);
        CRUD::addColumn(['name' => 'reference', 'label' => 'Référence']);

        CRUD::addColumn([
            'name'          => 'type',
            'label'         => 'Catégorie',
            'type'          => 'model_function',
            'function_name' => 'getTypeLabel',
        ]);

        CRUD::addColumn(['name' => 'title', 'label' => 'Titre']);
        CRUD::addColumn(['name' => 'requester_name', 'label' => 'Demandeur']);

        CRUD::addColumn([
            'name'          => 'amount',
            'label'         => 'Payé',
            'type'          => 'model_function',
            'function_name' => 'getFormattedAmount',
        ]);

        CRUD::addColumn([
            'name'          => 'status',
            'label'         => 'État',
            'type'          => 'model_function',
            'function_name' => 'getStatusLabel',
        ]);

        // Les annonces payées en attente de relecture d'abord.
        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();

        CRUD::addColumn(['name' => 'body', 'label' => 'Texte']);
        CRUD::addColumn(['name' => 'location', 'label' => 'Lieu']);
        CRUD::addColumn(['name' => 'public_contact', 'label' => 'Contact public']);
        CRUD::addColumn(['name' => 'requester_email', 'label' => 'Courriel']);
        CRUD::addColumn(['name' => 'requester_phone', 'label' => 'Téléphone']);
        CRUD::addColumn(['name' => 'paid_at', 'label' => 'Payée le', 'type' => 'datetime']);
        CRUD::addColumn(['name' => 'published_at', 'label' => 'Publiée le', 'type' => 'datetime']);
        CRUD::addColumn(['name' => 'expires_at', 'label' => 'Expire le', 'type' => 'date']);
        CRUD::addColumn(['name' => 'paypal_capture_id', 'label' => 'Capture PayPal']);
        CRUD::addColumn(['name' => 'moderation_note', 'label' => 'Note de relecture']);
    }

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation([
            'title'  => 'required|string|max:150',
            'body'   => 'required|string|max:4000',
            'status' => 'required|string|max:20',
        ]);

        CRUD::addField([
            'name'  => 'title',
            'label' => 'Titre',
            'type'  => 'text',
        ]);

        CRUD::addField([
            'name'       => 'body',
            'label'      => 'Texte de l’annonce',
            'type'       => 'textarea',
            'attributes' => ['rows' => 10],
            'hint'       => 'Corrigez l’orthographe si nécessaire avant publication.',
        ]);

        CRUD::addField([
            'name'    => 'status',
            'label'   => 'État',
            'type'    => 'select_from_array',
            'options' => [
                Announcement::STATUS_PAID      => 'Payée — à relire',
                Announcement::STATUS_PUBLISHED => 'Publier',
                Announcement::STATUS_REJECTED  => 'Refuser',
                Announcement::STATUS_EXPIRED   => 'Expirée',
            ],
            'hint' => 'Passer à « Publier » met l’annonce en ligne pour '
                .Announcement::DISPLAY_DAYS.' jours.',
        ]);

        CRUD::addField([
            'name'  => 'moderation_note',
            'label' => 'Note interne / motif de refus',
            'type'  => 'textarea',
        ]);

        CRUD::addField([
            'name'  => 'location',
            'label' => 'Lieu',
            'type'  => 'text',
        ]);

        CRUD::addField([
            'name'  => 'public_contact',
            'label' => 'Contact publié',
            'type'  => 'text',
        ]);

        CRUD::addField([
            'name'      => 'photo',
            'label'     => 'Photo',
            'type'      => 'upload',
            'withFiles' => [
                'disk' => 'public',
                'path' => 'announcements',
            ],
        ]);
    }

    /**
     * Au passage en « publiée », fixe les dates d’affichage.
     */
    public function update()
    {
        $response = $this->traitUpdate();

        $announcement = Announcement::find($this->crud->getCurrentEntryId());

        if (
            $announcement instanceof Announcement
            && $announcement->status === Announcement::STATUS_PUBLISHED
            && blank($announcement->published_at)
        ) {
            $announcement->forceFill([
                'published_at' => now(),
                'expires_at'   => now()->addDays(Announcement::DISPLAY_DAYS)->toDateString(),
            ])->save();
        }

        return $response;
    }
}
