<?php

namespace App\Console\Commands;

use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envoie une tranche d'infolettre à chaque passage du planificateur.
 *
 * L'hébergement mutualisé plafonne le nombre de courriels par heure ;
 * tout envoyer d'un coup ferait rejeter le reste et abîmerait la
 * réputation du domaine. On envoie donc par petites vagues, minute par
 * minute, et une campagne interrompue reprend là où elle s'est arrêtée.
 */
class SendNewsletterBatch extends Command
{
    protected $signature = 'newsletter:send {--limit=25 : Courriels par passage}';

    protected $description = 'Envoie la prochaine tranche des infolettres en attente';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $campaign = NewsletterCampaign::query()
            ->where('status', NewsletterCampaign::STATUS_QUEUED)
            ->orderBy('queued_at')
            ->first();

        if (! $campaign) {
            $this->info('Aucune campagne en attente.');

            return self::SUCCESS;
        }

        $pending = NewsletterSend::query()
            ->with('subscriber')
            ->where('newsletter_campaign_id', $campaign->id)
            ->where('status', NewsletterSend::STATUS_PENDING)
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            $campaign->forceFill([
                'status'  => NewsletterCampaign::STATUS_SENT,
                'sent_at' => now(),
            ])->save();

            $this->info("Campagne « {$campaign->subject} » terminée.");

            return self::SUCCESS;
        }

        foreach ($pending as $send) {
            $subscriber = $send->subscriber;

            // L'abonné a pu se désinscrire depuis la mise en file.
            if (! $subscriber || ! $subscriber->is_active) {
                $send->forceFill([
                    'status' => NewsletterSend::STATUS_FAILED,
                    'error'  => 'Abonné inactif au moment de l’envoi',
                ])->save();

                DB::table('newsletter_campaigns')->where('id', $campaign->id)
                    ->increment('failed_count');

                continue;
            }

            try {
                Mail::to($subscriber->email)->send(
                    new NewsletterMail($campaign, $subscriber)
                );

                $send->forceFill([
                    'status'  => NewsletterSend::STATUS_SENT,
                    'sent_at' => now(),
                ])->save();

                DB::table('newsletter_campaigns')->where('id', $campaign->id)
                    ->increment('sent_count');
            } catch (Throwable $e) {
                $send->forceFill([
                    'status' => NewsletterSend::STATUS_FAILED,
                    'error'  => mb_substr($e->getMessage(), 0, 500),
                ])->save();

                DB::table('newsletter_campaigns')->where('id', $campaign->id)
                    ->increment('failed_count');

                /*
                 * Le cron redirige la sortie vers /dev/null : sans trace
                 * dans le journal, un envoi qui échoue resterait invisible.
                 */
                Log::error('Infolettre — échec pour '.$subscriber->email.' : '.$e->getMessage(), [
                    'campaign' => $campaign->id,
                    'exception' => $e::class,
                ]);

                $this->error('Échec pour '.$subscriber->email.' : '.$e->getMessage());
            }
        }

        $this->info($pending->count().' courriel(s) traité(s).');

        return self::SUCCESS;
    }
}
