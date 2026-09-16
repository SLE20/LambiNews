<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            /*
             * Nombre de voix autorisées par adresse IP. 1 = une seule voix,
             * impossible de voter pour deux candidats.
             */
            $table->unsignedSmallInteger('max_votes_per_ip')->default(1)->after('hide_results_before_vote');

            // Sondage payant : le lecteur règle chaque voix avant qu'elle compte.
            $table->boolean('is_paid')->default(false)->after('max_votes_per_ip');
            $table->decimal('vote_price', 8, 2)->nullable()->after('is_paid');
            $table->string('currency', 3)->default('USD')->after('vote_price');
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            // pending_payment | paid | free
            $table->string('payment_status', 20)->default('free')->after('is_void');
            $table->decimal('amount', 8, 2)->nullable()->after('payment_status');
            $table->string('paypal_order_id')->nullable()->after('amount')->index();
            $table->string('paypal_capture_id')->nullable()->after('paypal_order_id');
            $table->timestamp('paid_at')->nullable()->after('paypal_capture_id');
        });

        /*
         * L'unicité passe de l'empreinte du navigateur à un simple
         * comptage par IP, fait en PHP : avec le vote multiple, plusieurs
         * voix légitimes partagent la même empreinte.
         */
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique(['poll_id', 'voter_hash']);
            $table->index(['poll_id', 'voter_hash']);
        });
    }

    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropIndex(['poll_id', 'voter_hash']);
            $table->unique(['poll_id', 'voter_hash']);
            $table->dropColumn([
                'payment_status', 'amount', 'paypal_order_id',
                'paypal_capture_id', 'paid_at',
            ]);
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn(['max_votes_per_ip', 'is_paid', 'vote_price', 'currency']);
        });
    }
};
