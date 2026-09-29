<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class VideoCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(Video::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/video');
        CRUD::setEntityNameStrings('vidéo', 'vidéos');
        CRUD::setSubheading('Collez l’adresse YouTube : le site en tire l’identifiant et la vignette. '
            .'Pour une vidéo hébergée ici, indiquez le chemin du fichier.');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name'     => 'thumb',
            'label'    => ' ',
            'type'     => 'closure',
            'escaped'  => false,
            'function' => fn ($v) => $v->thumbUrl()
                ? '<img src="'.e($v->thumbUrl()).'" style="width:96px;aspect-ratio:16/9;object-fit:cover;border-radius:6px">'
                : '—',
        ]);
        CRUD::addColumn(['name' => 'title', 'label' => 'Titre', 'limit' => 60]);
        CRUD::addColumn([
            'name'      => 'category_id',
            'label'     => 'Rubrique',
            'type'      => 'select',
            'entity'    => 'category',
            'attribute' => 'name',
            'model'     => Category::class,
        ]);
        CRUD::addColumn([
            'name'          => 'source',
            'label'         => 'Origine',
            'type'          => 'model_function',
            'function_name' => 'getSourceLabel',
        ]);
        CRUD::addColumn([
            'name'     => 'duration',
            'label'    => 'Durée',
            'type'     => 'closure',
            'function' => fn ($v) => $v->durationLabel() ?: '—',
        ]);
        CRUD::addColumn(['name' => 'views_count', 'label' => 'Vues']);
        CRUD::addColumn(['name' => 'published_at', 'label' => 'Publiée le', 'type' => 'datetime']);
        CRUD::addColumn([
            'name'          => 'publication',
            'label'         => 'État',
            'type'          => 'model_function',
            'function_name' => 'getPublicationLabel',
        ]);

        CRUD::orderBy('published_at', 'desc');
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();
        CRUD::addColumn(['name' => 'description', 'label' => 'Description']);
        CRUD::addColumn(['name' => 'youtube_id', 'label' => 'Identifiant YouTube']);
        CRUD::addColumn(['name' => 'video_url', 'label' => 'Fichier']);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'title'            => 'required|string|max:180',
            'source'           => 'required|in:youtube,file',
            'youtube_id'       => 'required_if:source,youtube|nullable|string|max:200',
            'video_url'        => 'required_if:source,file|nullable|string|max:500',
            'duration_seconds' => 'nullable|integer|min:0|max:86400',
            'slug'             => 'nullable|alpha_dash|max:200|unique:videos,slug,'.(CRUD::getCurrentEntryId() ?: 'NULL'),
        ], [
            'youtube_id.required_if' => 'Collez l’adresse de la vidéo YouTube.',
            'video_url.required_if'  => 'Indiquez le fichier ou son adresse.',
        ]);

        CRUD::addField(['name' => 'title', 'label' => 'Titre', 'type' => 'text']);

        CRUD::addField([
            'name'    => 'source',
            'label'   => 'Origine de la vidéo',
            'type'    => 'select_from_array',
            'options' => [
                Video::SOURCE_YOUTUBE => 'YouTube (recommandé : hébergement gratuit)',
                Video::SOURCE_FILE    => 'Fichier servi par le site',
            ],
            'default' => Video::SOURCE_YOUTUBE,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'youtube_id',
            'label'   => 'Adresse YouTube',
            'type'    => 'text',
            'hint'    => 'Collez le lien complet (youtube.com/watch?v=…, youtu.be/…, /shorts/…). '
                .'Le site en extrait l’identifiant.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'video_url',
            'label'   => 'Fichier vidéo (MP4)',
            'type'    => 'text',
            'hint'    => 'Chemin dans le stockage (ex. videos/reportage.mp4) ou adresse complète. '
                .'Utile seulement si l’origine est « Fichier ».',
        ]);

        CRUD::addField([
            'name'      => 'thumbnail',
            'label'     => 'Vignette (16:9)',
            'type'      => 'upload',
            'withFiles' => ['disk' => 'public', 'path' => 'videos'],
            'hint'      => 'Facultatif pour YouTube : sa vignette est reprise automatiquement.',
        ]);

        CRUD::addField([
            'name'       => 'duration_seconds',
            'label'      => 'Durée (en secondes)',
            'type'       => 'number',
            'hint'       => 'Affichée sur la vignette. Ex. 754 pour 12:34.',
            'wrapper'    => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name'    => 'category_id',
            'label'   => 'Rubrique',
            'type'    => 'select_from_array',
            'options' => ['' => '—'] + Category::orderBy('name')->pluck('name', 'id')->all(),
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name'    => 'author_id',
            'label'   => 'Auteur',
            'type'    => 'select_from_array',
            'options' => ['' => '—'] + User::orderBy('name')->pluck('name', 'id')->all(),
            'default' => backpack_user()?->id,
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name'       => 'description',
            'label'      => 'Description',
            'type'       => 'textarea',
            'attributes' => ['rows' => 6],
        ]);

        CRUD::addField(['name' => 'published_at', 'label' => 'Date de publication', 'type' => 'datetime', 'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'is_published', 'label' => 'Publiée', 'type' => 'checkbox', 'default' => true, 'wrapper' => ['class' => 'form-group col-md-4 pt-4']]);
        CRUD::addField(['name' => 'is_featured', 'label' => 'À la une des médias', 'type' => 'checkbox', 'wrapper' => ['class' => 'form-group col-md-4 pt-4']]);
        CRUD::addField(['name' => 'slug', 'label' => 'Adresse (slug)', 'type' => 'text', 'hint' => 'Laisser vide pour la générer.']);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
