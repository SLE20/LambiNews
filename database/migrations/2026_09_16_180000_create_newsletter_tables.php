<?php

use App\Models\Subscriber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Jeton de désabonnement. Un lien de désinscription en un clic est
         * obligatoire : sans lui, les plaintes pour spam font blacklister
         * le domaine et plus aucun courriel n'arrive.
         */
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('token', 40)->nullable()->unique()->after('email');
        });

        Subscriber::query()->whereNull('token')->get()->each(function (Subscriber $s): void {
            $s->forceFill(['token' => Str::lower(Str::random(32))])->saveQuietly();
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->text('intro')->nullable();

            // Encart sponsor : c'est ce qui se vend dans une infolettre.
            $table->string('sponsor_name')->nullable();
            $table->string('sponsor_url')->nullable();
            $table->string('sponsor_image')->nullable();
            $table->text('sponsor_text')->nullable();

            // Nombre d'articles récents à inclure automatiquement.
            $table->unsignedSmallInteger('article_count')->default(5);

            /*
             * draft   : en préparation
             * queued  : destinataires inscrits, envoi en cours par le cron
             * sent    : terminé
             */
            $table->string('status', 20)->default('draft');

            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('newsletter_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();

            // pending | sent | failed
            $table->string('status', 20)->default('pending');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Empêche d'envoyer deux fois la même campagne au même abonné.
            $table->unique(
                ['newsletter_campaign_id', 'subscriber_id'],
                'newsletter_sends_campaign_subscriber_unique'
            );
            $table->index(['newsletter_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_sends');
        Schema::dropIfExists('newsletter_campaigns');

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
