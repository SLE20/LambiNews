<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            /*
             * Réglage sensible : la valeur est chiffrée en base et n'est
             * jamais réaffichée en clair dans l'administration.
             */
            $table->boolean('is_secret')->default(false)->after('type');
        });

        $now = now();

        $rows = [
            // [key, value, label, hint, group, type, position, secret]
            ['paypal_mode', 'sandbox', 'Mode PayPal', 'sandbox pour les essais, live pour encaisser réellement.', 'paiement', 'select', 10, false],
            ['paypal_client_id', '', 'Identifiant client PayPal', 'developer.paypal.com → Apps & Credentials → Client ID.', 'paiement', 'text', 20, false],
            ['paypal_secret', '', 'Clé secrète PayPal', 'La même page, bouton « Show » sous Secret. Stockée chiffrée.', 'paiement', 'text', 30, true],
        ];

        DB::table('site_settings')->insert(array_map(
            fn (array $r) => [
                'key'        => $r[0],
                'value'      => $r[1],
                'label'      => $r[2],
                'hint'       => $r[3],
                'group'      => $r[4],
                'type'       => $r[5],
                'position'   => $r[6],
                'is_secret'  => $r[7],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $rows
        ));
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->whereIn('key', ['paypal_mode', 'paypal_client_id', 'paypal_secret'])
            ->delete();

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('is_secret');
        });
    }
};
