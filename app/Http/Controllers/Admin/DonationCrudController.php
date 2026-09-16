<?php

namespace App\Http\Controllers\Admin;

use App\Models\Donation;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Consultation des dons.
 *
 * Volontairement en lecture seule : un don est le reflet d’une transaction
 * PayPal, le modifier à la main désynchroniserait les comptes.
 */
class DonationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        CRUD::setModel(Donation::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/donation');
        CRUD::setEntityNameStrings('don', 'dons');

        CRUD::denyAccess(['create', 'update', 'delete']);
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name'  => 'created_at',
            'label' => 'Date',
            'type'  => 'datetime',
        ]);

        CRUD::addColumn([
            'name'  => 'reference',
            'label' => 'Référence',
        ]);

        CRUD::addColumn([
            'name'  => 'amount',
            'label' => 'Montant',
            'type'  => 'model_function',
            'function_name' => 'getFormattedAmount',
        ]);

        CRUD::addColumn([
            'name'  => 'donor_name',
            'label' => 'Donateur',
            'type'  => 'model_function',
            'function_name' => 'getDisplayName',
        ]);

        CRUD::addColumn([
            'name'  => 'donor_email',
            'label' => 'Courriel',
        ]);

        CRUD::addColumn([
            'name'  => 'status',
            'label' => 'Statut',
            'type'  => 'model_function',
            'function_name' => 'getStatusLabel',
        ]);

        CRUD::orderBy('created_at', 'desc');

        /*
         * Pas de CRUD::addFilter() ici : les filtres appartiennent à
         * Backpack PRO, et le projet n’utilise que la version gratuite.
         * La colonne « Statut » suffit à distinguer un don encaissé d’une
         * commande abandonnée.
         */
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();

        CRUD::addColumn(['name' => 'message', 'label' => 'Message']);
        CRUD::addColumn(['name' => 'currency', 'label' => 'Devise']);
        CRUD::addColumn(['name' => 'is_anonymous', 'label' => 'Anonyme', 'type' => 'boolean']);
        CRUD::addColumn(['name' => 'paypal_order_id', 'label' => 'Commande PayPal']);
        CRUD::addColumn(['name' => 'paypal_capture_id', 'label' => 'Capture PayPal']);
        CRUD::addColumn(['name' => 'payer_email', 'label' => 'Compte PayPal payeur']);
        CRUD::addColumn(['name' => 'paid_at', 'label' => 'Payé le', 'type' => 'datetime']);
    }
}
