<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Tag;

class ArticleCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(Article::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/article');
        CRUD::setEntityNameStrings('article', 'articles');
        $user = backpack_user();

if ($user->role === 'author') {
    CRUD::addClause('where', 'author_id', $user->id);
    CRUD::denyAccess('delete');

    CRUD::setAccessCondition('update', function ($entry) use ($user) {
    return $entry !== null
        && (int) $entry->author_id === (int) $user->id
        && in_array($entry->status, ['draft', 'review'], true);
});
}
    }

   protected function setupListOperation(): void
{
    /*
     * Charge les relations pour éviter des requêtes supplémentaires.
     */
    CRUD::addClause('with', [
        'category',
        'author',
    ]);

    CRUD::addColumn([
        'name' => 'title',
        'label' => 'Titre',
        'type' => 'text',
        'limit' => 55,
        'orderable' => true,
        'searchLogic' => function (
            $query,
            $column,
            $searchTerm
        ): void {
            $query->orWhere(
                'title',
                'like',
                '%'.$searchTerm.'%'
            );
        },
    ]);

    /*
     * La notation category.name affiche uniquement
     * le nom de la catégorie, pas l’objet JSON complet.
     */
    CRUD::addColumn([
        'name' => 'category.name',
        'label' => 'Rubrique',
        'type' => 'text',
        'orderable' => false,
    ]);

    /*
     * Même correction pour l’auteur.
     */
    CRUD::addColumn([
        'name' => 'author.name',
        'label' => 'Auteur',
        'type' => 'text',
        'orderable' => false,
    ]);

    CRUD::addColumn([
        'name' => 'status',
        'label' => 'Statut',
        'type' => 'select_from_array',
        'options' => [
            'draft' => 'Brouillon',
            'review' => 'En révision',
            'scheduled' => 'Programmé',
            'published' => 'Publié',
            'archived' => 'Archivé',
        ],
    ]);

    CRUD::addColumn([
        'name' => 'is_featured',
        'label' => 'À la une',
        'type' => 'boolean',
        'options' => [
            0 => 'Non',
            1 => 'Oui',
        ],
    ]);

    CRUD::addColumn([
        'name' => 'is_breaking',
        'label' => 'Dernière minute',
        'type' => 'boolean',
        'options' => [
            0 => 'Non',
            1 => 'Oui',
        ],
    ]);

    CRUD::addColumn([
        'name' => 'published_at',
        'label' => 'Publication',
        'type' => 'datetime',
        'format' => 'DD MMM YYYY, HH:mm',
    ]);

    CRUD::addColumn([
        'name' => 'views_count',
        'label' => 'Vues',
        'type' => 'number',
        'thousands_sep' => ' ',
    ]);

    /*
     * Conserve le bouton Aperçu si la vue du bouton existe déjà.
     */
    
}

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(ArticleRequest::class);

        CRUD::addField([
            'name' => 'title',
            'label' => 'Titre',
            'type' => 'text',
        ]);

        CRUD::addField([
            'name' => 'slug',
            'label' => 'Adresse URL',
            'type' => 'text',
            'hint' => 'Laissez vide pour la générer automatiquement.',
        ]);

        CRUD::addField([
            'name' => 'category_id',
            'label' => 'Rubrique',
            'type' => 'select',
            'entity' => 'category',
            'model' => Category::class,
            'attribute' => 'name',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'author_id',
            'label' => 'Auteur',
            'type' => 'select',
            'entity' => 'author',
            'model' => User::class,
            'attribute' => 'name',
            'default' => backpack_user()->id,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
    'name' => 'tags',
    'label' => 'Mots-clés',
    'type' => 'select_multiple',
    'entity' => 'tags',
    'model' => \App\Models\Tag::class,
    'attribute' => 'name',
    'pivot' => true,
    'options' => function ($query) {
        return $query
            ->orderBy('name')
            ->get();
    },
    'hint' => 'Maintenez la touche Ctrl pour sélectionner plusieurs mots-clés.',
]);

        CRUD::addField([
            'name' => 'excerpt',
            'label' => 'Résumé',
            'type' => 'textarea',
            'attributes' => [
                'rows' => 3,
                'maxlength' => 1000,
            ],
        ]);

        CRUD::addField([
            'name' => 'content',
            'label' => 'Contenu',
            'type' => 'summernote',
        ]);

        CRUD::addField([
    'name' => 'featured_image',
    'label' => 'Image principale',
    'type' => 'upload',
    'withFiles' => [
        'disk' => 'public',
        'path' => 'articles',
    ],
]);

        CRUD::addField([
            'name'  => 'is_sponsored',
            'label' => 'Contenu sponsorisé (publireportage)',
            'type'  => 'checkbox',
            'hint'  => 'Affiche une mention « Contenu sponsorisé » sur '
                .'l’article et l’exclut du flux RSS et du sitemap '
                .'Google Actualités.',
            'tab'   => 'Sponsoring',
        ]);

        CRUD::addField([
            'name'  => 'sponsor_name',
            'label' => 'Nom de l’annonceur',
            'type'  => 'text',
            'tab'   => 'Sponsoring',
        ]);

        CRUD::addField([
            'name'  => 'sponsor_url',
            'label' => 'Lien de l’annonceur',
            'type'  => 'url',
            'hint'  => 'Le lien porte rel="sponsored", comme Google l’exige.',
            'tab'   => 'Sponsoring',
        ]);

        CRUD::addField([
            'name'      => 'sponsor_logo',
            'label'     => 'Logo de l’annonceur',
            'type'      => 'upload',
            'withFiles' => [
                'disk' => 'public',
                'path' => 'sponsors',
            ],
            'tab'   => 'Sponsoring',
        ]);

        CRUD::addField([
            'name' => 'image_caption',
            'label' => 'Légende de l’image',
            'type' => 'text',
        ]);

        CRUD::addField([
            'name' => 'status',
            'label' => 'Statut',
            'type' => 'select_from_array',
            'options' => $this->statusOptions(),
            'default' => 'draft',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'published_at',
            'label' => 'Date de publication',
            'type' => 'datetime',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'is_featured',
            'label' => 'Afficher à la une',
            'type' => 'checkbox',
        ]);
        CRUD::addField([
    'name' => 'is_breaking',
    'label' => 'Afficher dans “Dernière minute”',
    'type' => 'checkbox',
    'default' => false,
]);

        CRUD::addField([
            'name' => 'allow_comments',
            'label' => 'Autoriser les commentaires',
            'type' => 'checkbox',
            'default' => true,
        ]);

        CRUD::addField([
            'name' => 'seo_title',
            'label' => 'Titre SEO',
            'type' => 'text',
            'tab' => 'Référencement',
        ]);

        CRUD::addField([
            'name' => 'seo_description',
            'label' => 'Description SEO',
            'type' => 'textarea',
            'tab' => 'Référencement',
            'attributes' => [
                'rows' => 3,
                'maxlength' => 500,
            ],
        ]);

        if (backpack_user()->role === 'author') {
    CRUD::modifyField('author_id', [
        'type' => 'hidden',
        'value' => backpack_user()->id,
    ]);
}
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    private function statusOptions(): array
{
    if (backpack_user()?->role === 'author') {
        return [
            'draft' => 'Brouillon',
            'review' => 'Soumettre pour révision',
        ];
    }

    return [
        'draft' => 'Brouillon',
        'review' => 'En révision',
        'scheduled' => 'Programmé',
        'published' => 'Publié',
        'archived' => 'Archivé',
    ];
}
}