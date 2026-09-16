<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();

            // Référence interne, communiquée au donateur.
            $table->string('reference', 32)->unique();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);

            /*
             * pending   : commande créée chez PayPal, paiement non confirmé
             * completed : paiement capturé
             * failed    : refus ou erreur à la capture
             * cancelled : le donateur a fermé la fenêtre PayPal
             */
            $table->string('status', 20)->default('pending');

            $table->string('paypal_order_id')->nullable()->index();
            $table->string('paypal_capture_id')->nullable();
            $table->string('payer_email')->nullable();

            // Réponse brute de PayPal, conservée pour les litiges.
            $table->json('payload')->nullable();

            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
