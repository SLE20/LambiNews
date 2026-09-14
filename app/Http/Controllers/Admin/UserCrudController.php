<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        abort_unless(
            backpack_user()?->isAdmin(),
            403
        );

        CRUD::setModel(User::class);

        CRUD::setRoute(
            config('backpack.base.route_prefix').'/user'
        );

        CRUD::setEntityNameStrings(
            'utilisateur',
            'utilisateurs'
        );
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name' => 'photo',
            'label' => 'Photo',
            'type' => 'image',
            'disk' => 'public',
            'height' => '45px',
            'width' => '45px',
        ]);

        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Nom',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'job_title',
            'label' => 'Fonction',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'email',
            'label' => 'Courriel',
            'type' => 'email',
        ]);

        CRUD::addColumn([
            'name' => 'role',
            'label' => 'Rôle',
            'type' => 'select_from_array',
            'options' => $this->roleOptions(),
        ]);

        CRUD::addColumn([
            'name' => 'is_active',
            'label' => 'Actif',
            'type' => 'boolean',
            'options' => [
                0 => 'Non',
                1 => 'Oui',
            ],
        ]);

        CRUD::orderBy('name');
    }

    protected function setupShowOperation(): void
    {
        CRUD::addColumn([
            'name' => 'photo',
            'label' => 'Photo',
            'type' => 'image',
            'disk' => 'public',
            'height' => '150px',
        ]);

        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Nom',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'slug',
            'label' => 'Adresse URL',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'email',
            'label' => 'Courriel',
            'type' => 'email',
        ]);

        CRUD::addColumn([
            'name' => 'role',
            'label' => 'Rôle',
            'type' => 'select_from_array',
            'options' => $this->roleOptions(),
        ]);

        CRUD::addColumn([
            'name' => 'job_title',
            'label' => 'Fonction',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'bio',
            'label' => 'Biographie',
            'type' => 'textarea',
        ]);

        CRUD::addColumn([
            'name' => 'facebook_url',
            'label' => 'Facebook',
            'type' => 'url',
        ]);

        CRUD::addColumn([
            'name' => 'instagram_url',
            'label' => 'Instagram',
            'type' => 'url',
        ]);

        CRUD::addColumn([
            'name' => 'tiktok_url',
            'label' => 'TikTok',
            'type' => 'url',
        ]);

        CRUD::addColumn([
            'name' => 'is_active',
            'label' => 'Compte actif',
            'type' => 'boolean',
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(UserRequest::class);

        CRUD::addField([
            'name' => 'name',
            'label' => 'Nom',
            'type' => 'text',
            'tab' => 'Compte',
        ]);

        CRUD::addField([
            'name' => 'slug',
            'label' => 'Adresse du profil',
            'type' => 'text',
            'hint' => 'Laissez vide pour la générer automatiquement.',
            'tab' => 'Compte',
        ]);

        CRUD::addField([
            'name' => 'email',
            'label' => 'Adresse courriel',
            'type' => 'email',
            'tab' => 'Compte',
        ]);

        CRUD::addField([
            'name' => 'password',
            'label' => 'Mot de passe',
            'type' => 'password',
            'tab' => 'Compte',
        ]);

        CRUD::addField([
            'name' => 'role',
            'label' => 'Rôle',
            'type' => 'select_from_array',
            'options' => $this->roleOptions(),
            'default' => 'author',
            'tab' => 'Compte',
        ]);

        CRUD::addField([
            'name' => 'is_active',
            'label' => 'Compte actif',
            'type' => 'checkbox',
            'default' => true,
            'tab' => 'Compte',
        ]);

        CRUD::addField([
            'name' => 'job_title',
            'label' => 'Fonction',
            'type' => 'text',
            'hint' => 'Exemple : Journaliste politique',
            'tab' => 'Profil public',
        ]);

        CRUD::addField([
            'name' => 'photo',
            'label' => 'Photo',
            'type' => 'upload',
            'withFiles' => [
                'disk' => 'public',
                'path' => 'authors',
            ],
            'tab' => 'Profil public',
        ]);

        CRUD::addField([
            'name' => 'bio',
            'label' => 'Biographie',
            'type' => 'textarea',
            'attributes' => [
                'rows' => 7,
                'maxlength' => 2000,
            ],
            'tab' => 'Profil public',
        ]);

        CRUD::addField([
            'name' => 'facebook_url',
            'label' => 'Lien Facebook',
            'type' => 'url',
            'tab' => 'Réseaux sociaux',
        ]);

        CRUD::addField([
            'name' => 'instagram_url',
            'label' => 'Lien Instagram',
            'type' => 'url',
            'tab' => 'Réseaux sociaux',
        ]);

        CRUD::addField([
            'name' => 'tiktok_url',
            'label' => 'Lien TikTok',
            'type' => 'url',
            'tab' => 'Réseaux sociaux',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();

        CRUD::modifyField('password', [
            'hint' => 'Laissez vide pour conserver le mot de passe actuel.',
        ]);
    }

    private function roleOptions(): array
    {
        return [
            'admin' => 'Administrateur',
            'editor' => 'Éditeur',
            'author' => 'Auteur',
        ];
    }
}