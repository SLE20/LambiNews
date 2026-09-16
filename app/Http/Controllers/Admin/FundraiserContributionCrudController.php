<?php

namespace App\Http\Controllers\Admin;

use App\Models\Fundraiser;
use App\Models\FundraiserContribution;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Consultation des contributions. En lecture seule : une contribution
 * reflète une transaction chez PayPal ou MonCash.
 */
class FundraiserContributionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        CRUD::setModel(FundraiserContribution::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/fundraiser-contribution');
        CRUD::setEntityNameStrings('contribution', 'contributions');

        CRUD::denyAccess(['create', 'update', 'delete']);
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'created_at', 'label' => 'Date', 'type' => 'datetime']);
        CRUD::addColumn(['name' => 'reference', 'label' => 'Référence']);

        CRUD::addColumn([
            'name'      => 'fundraiser_id',
            'label'     => 'Campagne',
            'type'      => 'select',
            'entity'    => 'fundraiser',
            'attribute' => 'title',
            'model'     => Fundraiser::class,
        ]);

        CRUD::addColumn([
            'name'          => 'amount',
            'label'         => 'Montant',
            'type'          => 'model_function',
            'function_name' => 'getFormattedAmount',
        ]);

        CRUD::addColumn([
            'name'          => 'provider',
            'label'         => 'Moyen',
            'type'          => 'model_function',
            'function_name' => 'getProviderLabel',
        ]);

        CRUD::addColumn([
            'name'          => 'status',
            'label'         => 'État',
            'type'          => 'model_function',
            'function_name' => 'getStatusLabel',
        ]);

        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();
        CRUD::addColumn(['name' => 'donor_name', 'label' => 'Donateur']);
        CRUD::addColumn(['name' => 'donor_email', 'label' => 'Courriel']);
        CRUD::addColumn(['name' => 'donor_phone', 'label' => 'Téléphone']);
        CRUD::addColumn(['name' => 'message', 'label' => 'Message']);
        CRUD::addColumn(['name' => 'is_anonymous', 'label' => 'Anonyme', 'type' => 'boolean']);
        CRUD::addColumn(['name' => 'provider_order_id', 'label' => 'Identifiant paiement']);
        CRUD::addColumn(['name' => 'provider_capture_id', 'label' => 'Capture']);
        CRUD::addColumn(['name' => 'paid_at', 'label' => 'Payée le', 'type' => 'datetime']);
    }
}
