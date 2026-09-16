<?php

namespace App\Http\Controllers\Admin;

use App\Models\Poll;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PollCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(Poll::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/poll');
        CRUD::setEntityNameStrings('sondage', 'sondages');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'question', 'label' => 'Question']);

        CRUD::addColumn([
            'name'          => 'status',
            'label'         => 'État',
            'type'          => 'model_function',
            'function_name' => 'getStatusLabel',
        ]);

        CRUD::addColumn([
            'name'          => 'votes',
            'label'         => 'Participation',
            'type'          => 'model_function',
            'function_name' => 'getVotesSummary',
        ]);

        CRUD::addColumn([
            'name'          => 'rules',
            'label'         => 'Règles',
            'type'          => 'model_function',
            'function_name' => 'getRulesLabel',
        ]);

        CRUD::addColumn(['name' => 'created_at', 'label' => 'Créé le', 'type' => 'datetime']);

        CRUD::addColumn([
            'name'  => 'reset_link',
            'label' => 'Réinitialiser',
            'type'  => 'closure',
            'function' => fn ($entry) => '<a href="'
                .backpack_url('poll/'.$entry->id.'/reset')
                .'" onclick="return confirm(\'Effacer toutes les voix de ce sondage ?\')">'
                .'Remettre à zéro</a>',
            'escaped' => false,
        ]);

        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'question'         => 'required|string|max:200',
            'ends_at'          => 'nullable|date|after_or_equal:starts_at',
            'max_votes_per_ip' => 'nullable|integer|min:1|max:50',
            'vote_identity'    => 'nullable|in:device,ip',
            // Un sondage payant sans prix ne pourrait jamais encaisser.
            'vote_price'       => 'nullable|numeric|min:0.5|max:500|required_if:is_paid,1',
        ]);

        CRUD::addField([
            'name'  => 'question',
            'label' => 'Question posée aux lecteurs',
            'type'  => 'text',
            'tab'   => 'Sondage',
        ]);

        CRUD::addField([
            'name'  => 'description',
            'label' => 'Précision (facultatif)',
            'type'  => 'textarea',
            'tab'   => 'Sondage',
        ]);

        CRUD::addField([
            'name'    => 'layout',
            'label'   => 'Présentation',
            'type'    => 'select_from_array',
            'options' => [
                'standard' => 'Bloc simple (colonne d’article, liste)',
                'showcase' => 'Affiche pleine page (bandeau + cartes + graphiques)',
            ],
            'default' => 'standard',
            'tab'     => 'Affiche',
        ]);

        CRUD::addField([
            'name'  => 'headline',
            'label' => 'Titre du bandeau',
            'type'  => 'text',
            'hint'  => 'Ex. « Sondaj Prézidansyèl 2026 ». Le dernier mot '
                .'s’affiche en rouge. Vide = la question est utilisée.',
            'tab'   => 'Affiche',
        ]);

        CRUD::addField([
            'name'    => 'eyebrow',
            'label'   => 'Surtitre',
            'type'    => 'text',
            'hint'    => 'Petite ligne au-dessus du titre. Ex. « Votre avis compte ».',
            'tab'     => 'Affiche',
        ]);

        CRUD::addField([
            'name'  => 'subtitle',
            'label' => 'Phrase d’accroche',
            'type'  => 'text',
            'hint'  => 'Affichée sous la question, dans le bandeau.',
            'tab'   => 'Affiche',
        ]);

        CRUD::addField([
            'name'      => 'hero_image',
            'label'     => 'Image de fond du bandeau',
            'type'      => 'upload',
            'withFiles' => ['disk' => 'public', 'path' => 'polls'],
            'hint'      => 'Format paysage, au moins 1600 px de large.',
            'tab'       => 'Affiche',
        ]);

        CRUD::addField([
            'tab'     => 'Sondage',
            'name'    => 'starts_at',
            'label'   => 'Ouverture',
            'type'    => 'date',
            'hint'    => 'Vide = ouvert tout de suite.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'tab'     => 'Sondage',
            'name'    => 'ends_at',
            'label'   => 'Clôture',
            'type'    => 'date',
            'hint'    => 'Vide = sans date de fin.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'tab'     => 'Sondage',
            'name'    => 'is_active',
            'label'   => 'Actif',
            'type'    => 'checkbox',
            'default' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'tab'     => 'Sondage',
            'name'    => 'hide_results_before_vote',
            'label'   => 'Cacher les résultats avant le vote',
            'type'    => 'checkbox',
            'hint'    => 'Évite d’influencer le lecteur avant qu’il choisisse.',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'vote_identity',
            'label'   => 'Reconnaître un votant par',
            'type'    => 'select_from_array',
            'options' => [
                'device' => 'Appareil (téléphone et ordinateur votent séparément)',
                'ip'     => 'Connexion / IP (une seule voix par foyer ou par bureau)',
            ],
            'default' => 'device',
            'hint'    => 'En Haïti, les opérateurs mobiles placent des milliers '
                .'d’abonnés derrière une même IP : « Connexion » les fait taire '
                .'tous sauf un. « Appareil » est le réglage recommandé.',
            'tab'     => 'Règles de vote',
        ]);

        CRUD::addField([
            'name'    => 'max_votes_per_ip',
            'label'   => 'Nombre de voix par votant',
            'type'    => 'number',
            'default' => 1,
            'hint'    => '1 = une seule voix, impossible de voter pour deux '
                .'candidats. Au-delà, le même votant peut voter plusieurs fois.',
            'tab'     => 'Règles de vote',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'is_paid',
            'label'   => 'Sondage payant',
            'type'    => 'checkbox',
            'hint'    => 'Le lecteur règle chaque voix avant qu’elle compte.',
            'tab'     => 'Règles de vote',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'vote_price',
            'label'   => 'Prix d’une voix',
            'type'    => 'number',
            'attributes' => ['step' => '0.01', 'min' => '0.5'],
            'hint'    => 'Obligatoire si le sondage est payant.',
            'tab'     => 'Règles de vote',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'currency',
            'label'   => 'Devise',
            'type'    => 'select_from_array',
            'options' => ['USD' => 'USD'],
            'default' => 'USD',
            'hint'    => 'PayPal ne gère pas la gourde.',
            'tab'     => 'Règles de vote',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'tab'   => 'Sondage',
            'name'  => 'options_help',
            'type'  => 'custom_html',
            'value' => '<div class="alert alert-info mb-0">Après avoir '
                .'enregistré le sondage, ajoutez ses choix dans '
                .'<strong>Choix de sondage</strong>.</div>',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    /**
     * Efface toutes les voix d'un sondage et remet les compteurs à zéro.
     *
     * Indispensable après une phase d'essai : sans cela, les personnes
     * qui ont testé depuis la rédaction ont consommé leur quota et ne
     * peuvent plus voter une fois le sondage réellement ouvert.
     */
    public function reset(int $id): RedirectResponse
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        $poll = Poll::findOrFail($id);

        $deleted = DB::table('poll_votes')->where('poll_id', $poll->id)->delete();

        DB::table('poll_options')->where('poll_id', $poll->id)->update(['votes_count' => 0]);
        DB::table('polls')->where('id', $poll->id)->update(['votes_count' => 0]);

        return redirect()
            ->to(backpack_url('poll'))
            ->with('success', $deleted.' voix effacée(s). Le sondage repart de zéro.');
    }
}
