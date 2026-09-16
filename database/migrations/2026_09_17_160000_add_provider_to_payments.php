<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MonCash devient disponible partout où l'on encaisse : dons,
     * annonces et votes payants. Chaque enregistrement retient donc le
     * moyen employé et l'adresse de règlement renvoyée par WalCash.
     */
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('provider', 20)->default('paypal')->after('status');
            $table->string('checkout_url', 500)->nullable()->after('provider');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('provider', 20)->default('paypal')->after('status');
            $table->string('checkout_url', 500)->nullable()->after('provider');
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->string('provider', 20)->default('paypal')->after('payment_status');
            $table->string('checkout_url', 500)->nullable()->after('provider');
        });
    }

    public function down(): void
    {
        foreach (['donations', 'announcements', 'poll_votes'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['provider', 'checkout_url']);
            });
        }
    }
};
