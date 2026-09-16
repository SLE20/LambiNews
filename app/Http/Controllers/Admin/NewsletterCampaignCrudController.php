<?php

namespace App\Http\Controllers\Admin;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\RedirectResponse;

class NewsletterCampaignCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(NewsletterCampaign::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/newsletter-campaign');
        CRUD::setEntityNameStrings('infolettre', 'infolettres');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'subject', 'label' => 'Objet']);

        CRUD::addColumn([
            'name'          => 'status',
            'label'         => 'État',
            'type'          => 'model_function',
            'function_name' => 'getStatusLabel',
        ]);

        CRUD::addColumn([
            'name'          => 'progress',
            'label'         => 'Progression',
            'type'          => 'model_function',
            'function_name' => 'getProgress',
        ]);

        CRUD::addColumn(['name' => 'sponsor_name', 'label' => 'Sponsor']);
        CRUD::addColumn(['name' => 'created_at', 'label' => 'Créée le', 'type' => 'datetime']);

        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();

        CRUD::addColumn(['name' => 'preheader', 'label' => 'Texte d’aperçu']);
        CRUD::addColumn(['name' => 'intro', 'label' => 'Introduction']);
        CRUD::addColumn(['name' => 'sponsor_url', 'label' => 'Lien sponsor']);
        CRUD::addColumn(['name' => 'sponsor_text', 'label' => 'Texte sponsor']);
        CRUD::addColumn(['name' => 'article_count', 'label' => 'Articles inclus']);
        CRUD::addColumn(['name' => 'queued_at', 'label' => 'Mise en file', 'type' => 'datetime']);
        CRUD::addColumn(['name' => 'sent_at', 'label' => 'Terminée le', 'type' => 'datetime']);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'subject'       => 'required|string|max:180',
            'preheader'     => 'nullable|string|max:180',
            'sponsor_url'   => 'nullable|url|max:500',
            'article_count' => 'nullable|integer|min:1|max:15',
        ]);

        CRUD::addField([
            'name'  => 'subject',
            'label' => 'Objet du courriel',
            'type'  => 'text',
        ]);

        CRUD::addField([
            'name'  => 'preheader',
            'label' => 'Texte d’aperçu',
            'type'  => 'text',
            'hint'  => 'La ligne grise affichée à côté de l’objet dans la boîte de réception.',
        ]);

        CRUD::addField([
            'name'       => 'intro',
            'label'      => 'Mot d’introduction',
            'type'       => 'textarea',
            'attributes' => ['rows' => 4],
        ]);

        CRUD::addField([
            'name'    => 'article_count',
            'label'   => 'Nombre d’articles récents à inclure',
            'type'    => 'number',
            'default' => 5,
        ]);

        CRUD::addField([
            'name'  => 'sponsor_name',
            'label' => 'Nom du sponsor',
            'type'  => 'text',
            'hint'  => 'Laissez vide s’il n’y a pas d’encart publicitaire.',
            'tab'   => 'Sponsor',
        ]);

        CRUD::addField([
            'name'  => 'sponsor_text',
            'label' => 'Texte du sponsor',
            'type'  => 'textarea',
            'tab'   => 'Sponsor',
        ]);

        CRUD::addField([
            'name'  => 'sponsor_url',
            'label' => 'Lien du sponsor',
            'type'  => 'url',
            'tab'   => 'Sponsor',
        ]);

        CRUD::addField([
            'name'      => 'sponsor_image',
            'label'     => 'Visuel du sponsor',
            'type'      => 'upload',
            'withFiles' => ['disk' => 'public', 'path' => 'newsletter'],
            'tab'       => 'Sponsor',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    /**
     * Remet les envois en échec à l'état « en attente ».
     *
     * Utile après une coupure SMTP : la campagne repart sans réexpédier
     * les courriels déjà partis.
     */
    public function retry(int $id): RedirectResponse
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        $campaign = NewsletterCampaign::findOrFail($id);

        $reset = NewsletterSend::query()
            ->where('newsletter_campaign_id', $campaign->id)
            ->where('status', NewsletterSend::STATUS_FAILED)
            ->update(['status' => NewsletterSend::STATUS_PENDING, 'error' => null]);

        $campaign->forceFill([
            'status'       => NewsletterCampaign::STATUS_QUEUED,
            'failed_count' => max(0, $campaign->failed_count - $reset),
            'sent_at'      => null,
        ])->save();

        \Alert::success($reset.' envoi(s) remis en file.')->flash();

        return redirect()->to(backpack_url('newsletter-campaign'));
    }

    /**
     * Met la campagne en file : une ligne par abonné actif.
     *
     * L'envoi lui-même est fait par le planificateur, vague par vague.
     */
    public function queue(int $id): RedirectResponse
    {
        abort_unless(backpack_user()?->isAdmin(), 403);

        $campaign = NewsletterCampaign::findOrFail($id);

        if ($campaign->status !== NewsletterCampaign::STATUS_DRAFT) {
            \Alert::error('Cette infolettre est déjà partie.')->flash();

            return redirect()->to(backpack_url('newsletter-campaign'));
        }

        $subscribers = Subscriber::query()->where('is_active', true)->get(['id']);

        if ($subscribers->isEmpty()) {
            \Alert::error('Aucun abonné actif.')->flash();

            return redirect()->to(backpack_url('newsletter-campaign'));
        }

        $now = now();

        NewsletterSend::query()->insertOrIgnore(
            $subscribers->map(fn ($s) => [
                'newsletter_campaign_id' => $campaign->id,
                'subscriber_id'          => $s->id,
                'status'                 => NewsletterSend::STATUS_PENDING,
                'created_at'             => $now,
                'updated_at'             => $now,
            ])->all()
        );

        $campaign->forceFill([
            'status'           => NewsletterCampaign::STATUS_QUEUED,
            'recipients_count' => $subscribers->count(),
            'queued_at'        => $now,
        ])->save();

        \Alert::success('Infolettre mise en file : '.$subscribers->count()
            .' destinataires. L’envoi démarre dans la minute.')->flash();

        return redirect()->to(backpack_url('newsletter-campaign'));
    }
}
