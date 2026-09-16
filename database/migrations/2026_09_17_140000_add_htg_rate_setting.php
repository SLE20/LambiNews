<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * MonCash n'accepte que la gourde. Une campagne affichée en
         * dollars doit donc être convertie au moment de l'encaissement,
         * avec un taux que la rédaction tient à jour elle-même.
         */
        DB::table('site_settings')->insert([
            'key'        => 'htg_per_usd',
            'value'      => '132',
            'label'      => 'Taux de change (HTG pour 1 USD)',
            'hint'       => 'Sert à convertir un montant en dollars quand le '
                .'lecteur paie par MonCash, qui n’accepte que la gourde. '
                .'À tenir à jour.',
            'group'      => 'paiement',
            'type'       => 'text',
            'position'   => 80,
            'is_secret'  => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', 'htg_per_usd')->delete();
    }
};
