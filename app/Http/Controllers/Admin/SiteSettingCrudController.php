<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Services\PayPalClient;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\RedirectResponse;

/**
 * Réglages du site. On modifie une valeur, on n'ajoute ni ne supprime de
 * clé : chaque clé est lue quelque part dans le code.
 */
class SiteSettingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        CRUD::setModel(SiteSetting::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/site-setting');
        CRUD::setEntityNameStrings('réglage', 'réglages du site');

        CRUD::denyAccess(['create', 'delete', 'show']);
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name'          => 'group',
            'label'         => 'Section',
            'type'          => 'model_function',
            'function_name' => 'getGroupLabel',
        ]);

        CRUD::addColumn(['name' => 'label', 'label' => 'Réglage']);

        // Les secrets ne sont jamais réaffichés en clair.
        CRUD::addColumn([
            'name'          => 'value',
            'label'         => 'Valeur actuelle',
            'type'          => 'model_function',
            'function_name' => 'maskedValue',
        ]);

        CRUD::addColumn([
            'name'  => 'action',
            'label' => ' ',
            'type'  => 'closure',
            'function' => fn ($entry) => $entry->key === 'paypal_secret'
                ? '<a class="btn btn-sm btn-outline-primary" href="'
                    .backpack_url('site-setting/test-paypal').'">Tester PayPal</a>'
                : '',
            'escaped' => false,
        ]);

        CRUD::orderBy('group')->orderBy('position');
    }

    /**
     * Interroge PayPal avec les clés enregistrées et rapporte le résultat.
     * Évite de découvrir une faute de frappe le jour d'un vrai paiement.
     */
    public function testPaypal(): RedirectResponse
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        $result = PayPalClient::fromConfig()->check();

        /*
         * Backpack rend ses messages via Prologue\Alerts ; un
         * session()->with() ne s'affiche nulle part dans l'administration.
         */
        $result['ok']
            ? \Alert::success('PayPal : '.$result['message'])->flash()
            : \Alert::error('PayPal : '.$result['message'])->flash();

        return redirect()->to(backpack_url('site-setting'));
    }

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(['value' => 'nullable|string|max:2000']);

        $entry = $this->crud->getCurrentEntry();
        $type  = $entry->type ?? 'text';

        // Le mode PayPal est une liste fermée, pas un champ libre.
        if (($entry->key ?? null) === 'paypal_mode') {
            CRUD::addField([
                'name'    => 'value',
                'label'   => $entry->label,
                'type'    => 'select_from_array',
                'options' => [
                    'sandbox' => 'Sandbox — essais, avec de l’argent fictif',
                    'live'    => 'Live — encaissement réel',
                ],
                'hint'    => $entry->hint,
            ]);

            return;
        }

        CRUD::addField([
            'name'       => 'value',
            'label'      => $entry->label ?? 'Valeur',
            'type'       => $type === 'textarea' ? 'textarea' : ($type === 'url' ? 'url' : ($type === 'email' ? 'email' : 'text')),
            'hint'       => ($entry->hint ?? '')
                .($entry->is_secret ? ' Laissez vide pour conserver la clé actuelle.' : ''),
            'attributes' => $type === 'textarea' ? ['rows' => 4] : [],
            // On ne préremplit pas un secret : il repartirait chiffré deux fois.
            'value'      => $entry->is_secret ? '' : $entry->value,
        ]);
    }
}
