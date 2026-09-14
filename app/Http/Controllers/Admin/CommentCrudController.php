<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class CommentCrudController extends CrudController
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

        CRUD::setModel(Comment::class);

        CRUD::setRoute(
            config('backpack.base.route_prefix').'/comment'
        );

        CRUD::setEntityNameStrings(
            'commentaire',
            'commentaires'
        );
    }

    protected function setupListOperation(): void
{
    /*
     * Charge l’article associé à chaque commentaire.
     */
    CRUD::addClause('with', [
        'article',
    ]);

    CRUD::addColumn([
        'name' => 'article.title',
        'label' => 'Article',
        'type' => 'text',
        'limit' => 55,
        'orderable' => false,
        'searchLogic' => function (
            $query,
            $column,
            $searchTerm
        ): void {
            $query->orWhereHas(
                'article',
                function ($articleQuery) use ($searchTerm): void {
                    $articleQuery->where(
                        'title',
                        'like',
                        '%'.$searchTerm.'%'
                    );
                }
            );
        },
    ]);

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
        'name' => 'content',
        'label' => 'Commentaire',
        'type' => 'text',
        'limit' => 80,
    ]);

    CRUD::addColumn([
        'name' => 'status',
        'label' => 'Statut',
        'type' => 'select_from_array',
        'options' => [
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'spam' => 'Indésirable',
        ],
    ]);

    CRUD::addColumn([
        'name' => 'created_at',
        'label' => 'Reçu le',
        'type' => 'datetime',
        'format' => 'DD MMM YYYY, HH:mm',
    ]);
}

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(CommentRequest::class);

        CRUD::addField([
            'name' => 'article',
            'label' => 'Article',
            'type' => 'custom_html',
            'value' => $this->crud->getCurrentEntry()
                ? e($this->crud->getCurrentEntry()->article->title)
                : '',
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => 'Nom',
            'type' => 'text',
        ]);

        CRUD::addField([
            'name' => 'email',
            'label' => 'Adresse courriel',
            'type' => 'email',
        ]);

        CRUD::addField([
            'name' => 'body',
            'label' => 'Commentaire',
            'type' => 'textarea',
            'attributes' => [
                'rows' => 8,
                'maxlength' => 2000,
            ],
        ]);

        CRUD::addField([
            'name' => 'status',
            'label' => 'Décision',
            'type' => 'select_from_array',
            'options' => $this->statusOptions(),
        ]);
    }

    private function statusOptions(): array
    {
        return [
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'spam' => 'Indésirable',
        ];
    }
}