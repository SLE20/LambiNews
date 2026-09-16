<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
        CRUD::addColumn(['name' => 'value', 'label' => 'Valeur actuelle', 'limit' => 90]);

        CRUD::orderBy('group')->orderBy('position');
    }

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(['value' => 'nullable|string|max:2000']);

        $entry = $this->crud->getCurrentEntry();
        $type  = $entry->type ?? 'text';

        CRUD::addField([
            'name'       => 'value',
            'label'      => $entry->label ?? 'Valeur',
            'type'       => $type === 'textarea' ? 'textarea' : ($type === 'url' ? 'url' : ($type === 'email' ? 'email' : 'text')),
            'hint'       => $entry->hint ?? null,
            'attributes' => $type === 'textarea' ? ['rows' => 4] : [],
        ]);
    }
}
