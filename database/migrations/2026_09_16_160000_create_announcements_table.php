<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('reference', 32)->unique();
            $table->string('slug')->unique();

            // Avis de décès, remerciements, félicitations, annonce commerciale…
            $table->string('type', 30)->index();

            $table->string('title');
            $table->text('body');
            $table->string('photo')->nullable();

            // Coordonnées du demandeur, jamais affichées publiquement.
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('requester_phone')->nullable();

            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('USD');

            /*
             * pending_payment : formulaire rempli, PayPal pas encore validé
             * paid            : payé, en attente de relecture
             * published       : visible sur le site
             * rejected        : refusé (le motif est dans moderation_note)
             * expired         : la période d’affichage est terminée
             */
            $table->string('status', 20)->default('pending_payment');
            $table->text('moderation_note')->nullable();

            $table->string('paypal_order_id')->nullable()->index();
            $table->string('paypal_capture_id')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->string('ip_hash', 64)->nullable();

            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
